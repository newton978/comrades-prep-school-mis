<?php
require_once 'db.php';

$message = '';

// ========================================================
// 1. POST HANDLERS (SAVE/UPDATE BATCH MARKS, ADD EXAM)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // SAVE OR UPDATE BATCH SCORES & REMOVE SUBJECT MARKS
    if (isset($_POST['save_batch_grades'])) {
        $pupil_id   = (int)$_POST['pupil_id'];
        $exam_id    = (int)$_POST['exam_id'];
        $marks_arr  = $_POST['marks'] ?? [];
        $remove_arr = $_POST['remove_subject'] ?? [];

        if ($pupil_id && $exam_id) {
            try {
                $pdo->beginTransaction();

                // Delete selected subjects
                if (!empty($remove_arr)) {
                    $del_stmt = $pdo->prepare("DELETE FROM Results WHERE pupil_id = ? AND exam_id = ? AND subject_id = ?");
                    foreach ($remove_arr as $rem_sub_id) {
                        $del_stmt->execute([$pupil_id, $exam_id, (int)$rem_sub_id]);
                    }
                }

                // Insert or Update marks
                $ins_stmt = $pdo->prepare("
                    INSERT INTO Results (pupil_id, subject_id, exam_id, marks, grade) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE marks=VALUES(marks), grade=VALUES(grade)
                ");

                foreach ($marks_arr as $sub_id => $mark_val) {
                    if (in_array($sub_id, $remove_arr)) continue;
                    if ($mark_val === '' || $mark_val === null) continue;
                    
                    $m = (float)$mark_val;
                    
                    // Ugandan Primary Grading Scale
                    $grade = 'F9';
                    if ($m >= 80) $grade = 'D1';
                    elseif ($m >= 75) $grade = 'D2';
                    elseif ($m >= 65) $grade = 'C3';
                    elseif ($m >= 55) $grade = 'C4';
                    elseif ($m >= 45) $grade = 'P7';

                    $ins_stmt->execute([$pupil_id, (int)$sub_id, $exam_id, $m, $grade]);
                }

                $pdo->commit();
                $message = "Pupil examination record updated successfully!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Error updating marks: " . $e->getMessage();
            }
        }
    } 

    // ADD NEW EXAMINATION SET
    elseif (isset($_POST['add_exam'])) {
        $exam_name     = trim($_POST['exam_name']);
        $exam_type     = trim($_POST['exam_type']);
        $term          = trim($_POST['term']);
        $academic_year = (int)$_POST['academic_year'];

        $full_name = $exam_name . ' (' . $exam_type . ')';

        if (!empty($exam_name) && !empty($term)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO Examinations (exam_name, term, academic_year) VALUES (?, ?, ?)");
                $stmt->execute([$full_name, $term, $academic_year]);
                $message = "Examination set created successfully!";
            } catch (PDOException $e) { $message = "Error: " . $e->getMessage(); }
        }
    }
}

// ========================================================
// 2. FETCH DATA & MAP CLASS SUBJECTS
// ========================================================
$filter_class_id = isset($_GET['filter_class']) && !empty($_GET['filter_class']) ? (int)$_GET['filter_class'] : null;
$filter_exam_type = isset($_GET['filter_exam_type']) && !empty($_GET['filter_exam_type']) ? trim($_GET['filter_exam_type']) : '';

$classes  = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);
$exams    = $pdo->query("SELECT * FROM Examinations ORDER BY academic_year DESC, exam_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$pupils   = $pdo->query("
    SELECT p.pupil_id, p.admission_no, p.first_name, p.last_name, p.class_id, c.class_name 
    FROM Pupils p 
    LEFT JOIN Classes c ON p.class_id = c.class_id 
    ORDER BY p.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Map class subjects for JavaScript dynamic loading
$class_subs_raw = $pdo->query("
    SELECT cs.class_id, s.subject_id, s.subject_name, s.subject_code 
    FROM Class_Subject cs 
    JOIN Subjects s ON cs.subject_id = s.subject_id 
    ORDER BY s.subject_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$class_subjects_map = [];
foreach ($class_subs_raw as $cs) {
    $class_subjects_map[$cs['class_id']][] = $cs;
}

// Map existing scores: [pupil_id][exam_id][subject_id] => {marks, grade}
$existing_results_raw = $pdo->query("SELECT pupil_id, exam_id, subject_id, marks, grade FROM Results")->fetchAll(PDO::FETCH_ASSOC);
$existing_results = [];
foreach ($existing_results_raw as $er) {
    $existing_results[$er['pupil_id']][$er['exam_id']][$er['subject_id']] = [
        'marks' => (float)$er['marks'],
        'grade' => $er['grade']
    ];
}

// Build query conditions
$where_conditions = [];
if ($filter_class_id) {
    $where_conditions[] = "p.class_id = " . (int)$filter_class_id;
}
if ($filter_exam_type) {
    $where_conditions[] = "e.exam_name LIKE " . $pdo->quote('%' . $filter_exam_type . '%');
}
$where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

// Ranked aggregate results
$sql = "
    SELECT 
        ranked.*,
        (
            SELECT COUNT(DISTINCT r_sub.pupil_id) 
            FROM Results r_sub 
            JOIN Pupils p_sub ON r_sub.pupil_id = p_sub.pupil_id 
            WHERE r_sub.exam_id = ranked.exam_id 
              AND p_sub.class_id = ranked.class_id
        ) AS total_pupils_in_class
    FROM (
        SELECT 
            p.pupil_id, p.admission_no, p.first_name, p.last_name, p.class_id, c.class_name,
            e.exam_id, e.exam_name, e.term, e.academic_year,
            SUM(r.marks) AS total_marks,
            AVG(r.marks) AS avg_marks,
            COUNT(r.result_id) AS total_subjects_sat,
            DENSE_RANK() OVER (
                PARTITION BY p.class_id, e.exam_id 
                ORDER BY SUM(r.marks) DESC
            ) AS class_position
        FROM Results r 
        JOIN Pupils p ON r.pupil_id = p.pupil_id 
        LEFT JOIN Classes c ON p.class_id = c.class_id 
        JOIN Examinations e ON r.exam_id = e.exam_id 
        $where_clause
        GROUP BY r.pupil_id, r.exam_id, p.class_id
    ) AS ranked
    ORDER BY ranked.class_id ASC, ranked.exam_id DESC, ranked.class_position ASC
";

$aggregated_results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function getOrdinalSuffix($number) {
    if (!in_array(($number % 100), [11, 12, 13])) {
        switch ($number % 10) {
            case 1: return $number . 'st';
            case 2: return $number . 'nd';
            case 3: return $number . 'rd';
        }
    }
    return $number . 'th';
}

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid">
    <div>
        <!-- 1. BATCH SUBJECT MARKS ENTRY FORM -->
        <div class="card">
            <h2>Batch Subject Mark Entry</h2>
            <form action="subjects_exams.php" method="POST">
                <input type="hidden" name="save_batch_grades" value="1">
                
                <div class="form-group">
                    <label>Select Pupil</label>
                    <select name="pupil_id" id="pupil_select" required>
                        <option value="">-- Choose Pupil --</option>
                        <?php foreach ($pupils as $p): ?>
                            <option value="<?= $p['pupil_id'] ?>" data-classid="<?= $p['class_id'] ?>">
                                <?= htmlspecialchars($p['admission_no'] . ' - ' . $p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['class_name'] ?? 'No Class') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Examination Set</label>
                    <select name="exam_id" id="exam_select" required>
                        <option value="">-- Select Exam Set --</option>
                        <?php foreach ($exams as $ex): ?>
                            <option value="<?= $ex['exam_id'] ?>">
                                <?= htmlspecialchars($ex['exam_name'] . ' - ' . $ex['term'] . ' (' . $ex['academic_year'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="subject_inputs_container" style="margin-top: 15px; display: none;">
                    <h3 style="font-size:0.95rem; color:var(--primary); margin-bottom:10px;">Fill Marks for Assigned Subjects</h3>
                    <div id="subject_fields_list" style="display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <button type="submit" id="save_btn" class="btn" style="margin-top:15px; display:none;">Save / Update Marks</button>
            </form>
        </div>

        <!-- 2. CREATE EXAM SET -->
        <div class="card">
            <h2>Add Examination Set</h2>
            <form action="subjects_exams.php" method="POST">
                <input type="hidden" name="add_exam" value="1">
                <div class="form-group">
                    <label>Exam Type</label>
                    <select name="exam_type" required>
                        <option value="Mid-Term">Mid-Term Exam</option>
                        <option value="End of Term">End of Term Exam</option>
                    </select>
                </div>
                <div class="form-group"><label>Exam Name</label><input type="text" name="exam_name" placeholder="e.g. Mid-Term 1 or End of Term 1" required></div>
                <div class="form-group">
                    <label>Term</label>
                    <select name="term" required>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>
                <div class="form-group"><label>Academic Year</label><input type="number" name="academic_year" value="<?= date('Y') ?>" required></div>
                <button type="submit" class="btn">Create Exam Set</button>
            </form>
        </div>
    </div>

    <!-- 3. RESULTS TABLE WITH FILTERS -->
    <div class="card">
        <h2>Logged Examination Summary</h2>

        <form action="subjects_exams.php" method="GET" style="margin-bottom: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div class="form-group" style="margin:0;">
                <label>Filter by Class Level</label>
                <select name="filter_class" onchange="this.form.submit()">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class_id'] ?>" <?= $filter_class_id == $c['class_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin:0;">
                <label>Filter Exam Type</label>
                <select name="filter_exam_type" onchange="this.form.submit()">
                    <option value="">-- All Exam Types --</option>
                    <option value="Mid-Term" <?= $filter_exam_type === 'Mid-Term' ? 'selected' : '' ?>>Mid-Term Exams Only</option>
                    <option value="End of Term" <?= $filter_exam_type === 'End of Term' ? 'selected' : '' ?>>End of Term Exams Only</option>
                </select>
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Adm No</th>
                        <th>Pupil Name</th>
                        <th>Class</th>
                        <th>Exam Set</th>
                        <th>Total</th>
                        <th>Avg</th>
                        <th>Division</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aggregated_results)): ?>
                        <tr><td colspan="9" style="text-align: center;">No examination scores logged yet for selected filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($aggregated_results as $res): ?>
                            <?php 
                                $avg = (float)$res['avg_marks'];
                                $div = 'Division 4';
                                if ($avg >= 75) $div = 'Division 1';
                                elseif ($avg >= 60) $div = 'Division 2';
                                elseif ($avg >= 45) $div = 'Division 3';

                                $position_str = getOrdinalSuffix($res['class_position']) . ' / ' . $res['total_pupils_in_class'];
                                $p_id = (int)$res['pupil_id'];
                                $e_id = (int)$res['exam_id'];
                                $pupil_all_exams = $existing_results[$p_id] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $res['class_position'] <= 3 ? 'badge-success' : '' ?>">
                                        <?= $position_str ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($res['admission_no']) ?></td>
                                <td><strong><?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?></strong></td>
                                <td><?= htmlspecialchars($res['class_name'] ?? '-') ?></td>
                                <td><small><?= htmlspecialchars($res['exam_name']) ?></small></td>
                                <td><strong><?= number_format($res['total_marks'], 1) ?></strong></td>
                                <td><strong><?= number_format($avg, 1) ?>%</strong></td>
                                <td><strong><?= $div ?></strong></td>
                                <td>
                                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem; margin-bottom: 4px;" 
                                            onclick="triggerEditScores(<?= $p_id ?>, <?= $e_id ?>)">
                                        Edit Scores
                                    </button>
                                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem; background:#166534;" 
                                            onclick='printCombinedReport(<?= json_encode($res, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($pupil_all_exams, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($exams, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($subjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, "<?= htmlspecialchars($position_str, ENT_QUOTES) ?>")'>
                                        Print Report
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const classSubjectsMap = <?= json_encode($class_subjects_map) ?>;
const existingResults  = <?= json_encode($existing_results) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const pupilSelect  = document.getElementById('pupil_select');
    const examSelect   = document.getElementById('exam_select');
    const container    = document.getElementById('subject_inputs_container');
    const fieldsList   = document.getElementById('subject_fields_list');
    const saveBtn      = document.getElementById('save_btn');

    function loadPupilSubjectFields() {
        const pupilOpt = pupilSelect.options[pupilSelect.selectedIndex];
        const pupilId  = pupilSelect.value;
        const examId   = examSelect.value;
        const classId  = pupilOpt ? pupilOpt.getAttribute('data-classid') : null;

        fieldsList.innerHTML = '';

        if (pupilId && examId && classId && classSubjectsMap[classId]) {
            container.style.display = 'block';
            saveBtn.style.display   = 'block';

            classSubjectsMap[classId].forEach(sub => {
                const sId = sub.subject_id;
                const existingMark = (existingResults[pupilId] && existingResults[pupilId][examId] && existingResults[pupilId][examId][sId]) 
                                      ? existingResults[pupilId][examId][sId].marks : '';

                const div = document.createElement('div');
                div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:8px 12px; border-radius:4px; border:1px solid #e2e8f0;';
                div.innerHTML = `
                    <label style="margin:0; font-weight:bold; flex:1;">${sub.subject_name} (${sub.subject_code || ''})</label>
                    <input type="number" step="0.1" max="100" min="0" name="marks[${sId}]" value="${existingMark}" placeholder="Marks (%)" style="width:110px; padding:6px; margin-right:10px;">
                    <label style="font-size:0.75rem; color:#dc2626; cursor:pointer; margin:0;">
                        <input type="checkbox" name="remove_subject[]" value="${sId}"> Remove
                    </label>
                `;
                fieldsList.appendChild(div);
            });
        } else {
            container.style.display = 'none';
            saveBtn.style.display   = 'none';
        }
    }

    pupilSelect.addEventListener('change', loadPupilSubjectFields);
    examSelect.addEventListener('change', loadPupilSubjectFields);

    window.triggerEditScores = function(pupilId, examId) {
        pupilSelect.value = pupilId;
        examSelect.value  = examId;
        loadPupilSubjectFields();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
});

// PRINT COMBINED / SINGLE REPORT CARD POPUP
function printCombinedReport(res, pupilExams, allExams, allSubjects, positionStr) {
    let midExamId = null;
    let endExamId = null;

    allExams.forEach(ex => {
        if (ex.term === res.term && parseInt(ex.academic_year) === parseInt(res.academic_year)) {
            if (ex.exam_name.toLowerCase().includes('mid-term')) midExamId = ex.exam_id;
            if (ex.exam_name.toLowerCase().includes('end of term')) endExamId = ex.exam_id;
        }
    });

    let hasBoth = midExamId && endExamId && pupilExams[midExamId] && pupilExams[endExamId];
    let rowsHtml = '';
    let grandTotal = 0;
    let countSubs = 0;

    allSubjects.forEach(sub => {
        let sId = sub.subject_id;
        let midMark = (midExamId && pupilExams[midExamId] && pupilExams[midExamId][sId]) ? parseFloat(pupilExams[midExamId][sId].marks) : null;
        let endMark = (endExamId && pupilExams[endExamId] && pupilExams[endExamId][sId]) ? parseFloat(pupilExams[endExamId][sId].marks) : null;
        
        let singleMark = (pupilExams[res.exam_id] && pupilExams[res.exam_id][sId]) ? parseFloat(pupilExams[res.exam_id][sId].marks) : null;

        if (hasBoth) {
            if (midMark !== null || endMark !== null) {
                countSubs++;
                let finalMark = (midMark !== null && endMark !== null) ? ((midMark + endMark) / 2) : (midMark !== null ? midMark : endMark);
                grandTotal += finalMark;

                let g = 'F9';
                if (finalMark >= 80) g = 'D1';
                else if (finalMark >= 75) g = 'D2';
                else if (finalMark >= 65) g = 'C3';
                else if (finalMark >= 55) g = 'C4';
                else if (finalMark >= 45) g = 'P7';

                rowsHtml += `
                    <tr>
                        <td>${sub.subject_name}</td>
                        <td>${midMark !== null ? midMark.toFixed(1) + '%' : '-'}</td>
                        <td>${endMark !== null ? endMark.toFixed(1) + '%' : '-'}</td>
                        <td><strong>${finalMark.toFixed(1)}%</strong></td>
                        <td><strong>${g}</strong></td>
                    </tr>
                `;
            }
        } else if (singleMark !== null) {
            countSubs++;
            grandTotal += singleMark;

            let g = 'F9';
            if (singleMark >= 80) g = 'D1';
            else if (singleMark >= 75) g = 'D2';
            else if (singleMark >= 65) g = 'C3';
            else if (singleMark >= 55) g = 'C4';
            else if (singleMark >= 45) g = 'P7';

            rowsHtml += `
                <tr>
                    <td>${sub.subject_name}</td>
                    <td><strong>${singleMark.toFixed(1)}%</strong></td>
                    <td><strong>${g}</strong></td>
                </tr>
            `;
        }
    });

    let avgScore = countSubs > 0 ? (grandTotal / countSubs) : 0;
    let div = 'Division 4';
    if (avgScore >= 75) div = 'Division 1';
    else if (avgScore >= 60) div = 'Division 2';
    else if (avgScore >= 45) div = 'Division 3';

    const printWin = window.open('', '_blank', 'width=850,height=650');
    if (!printWin) {
        alert('Please allow popups in your browser to print the report card.');
        return;
    }

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Report Card - ${res.first_name} ${res.last_name}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #1e293b; }
                .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #1e3a8a; }
                .info-table, .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table td { padding: 6px; font-size: 0.95rem; }
                .report-table th, .report-table td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
                .report-table th { background: #f1f5f9; }
                .summary-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; margin-bottom: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>COMRADES PREPARATORY SCHOOL</h1>
                <p>Kitebi Campus - Official Terminal Academic Report Card</p>
            </div>
            
            <table class="info-table">
                <tr><td><strong>Pupil Name:</strong> ${res.first_name} ${res.last_name}</td><td><strong>Admission No:</strong> ${res.admission_no}</td></tr>
                <tr><td><strong>Class:</strong> ${res.class_name || 'N/A'}</td><td><strong>Term / Year:</strong> ${res.term} ${res.academic_year}</td></tr>
            </table>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        ${hasBoth ? '<th>Mid-Term (%)</th><th>End of Term (%)</th><th>Combined Avg (%)</th>' : '<th>Score (%)</th>'}
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml.length > 0 ? rowsHtml : '<tr><td colspan="5">No marks recorded.</td></tr>'}
                </tbody>
            </table>

            <div class="summary-box">
                <div><strong>Total Aggregate Marks:</strong> ${grandTotal.toFixed(1)}</div>
                <div><strong>Class Position:</strong> <strong>${positionStr}</strong></div>
                <div><strong>Average Score:</strong> ${avgScore.toFixed(1)}%</div>
                <div><strong>Overall Division:</strong> <strong>${div}</strong></div>
            </div>

            <div class="footer">
                <div>Class Teacher Signature: __________________</div>
                <div>Headteacher Signature: __________________</div>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>
</body>
</html>