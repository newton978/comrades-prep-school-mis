<?php
require_once 'db.php';

$message = '';
$message_type = 'success';

// ========================================================
// 1. POST HANDLER: SAVE EXAM MARKS & CLASS TEACHER COMMENTS
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_student_marks_comments'])) {
    $exam_id     = (int)$_POST['exam_id'];
    $subject_id  = (int)$_POST['subject_id'];
    $marks_arr   = $_POST['marks'] ?? [];
    $comments_arr= $_POST['comments'] ?? [];
    $conduct_arr = $_POST['conduct'] ?? [];

    if ($exam_id && $subject_id) {
        try {
            $pdo->beginTransaction();

            // A. Save/Update Subject Marks & Grades
            $ins_stmt = $pdo->prepare("
                INSERT INTO Results (pupil_id, subject_id, exam_id, marks, grade) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE marks=VALUES(marks), grade=VALUES(grade)
            ");

            foreach ($marks_arr as $pupil_id => $mark_val) {
                if ($mark_val === '' || $mark_val === null) continue;
                
                $m = (float)$mark_val;
                
                // Ugandan Primary Grading Scale
                $grade = 'F9';
                if ($m >= 80) $grade = 'D1';
                elseif ($m >= 75) $grade = 'D2';
                elseif ($m >= 65) $grade = 'C3';
                elseif ($m >= 55) $grade = 'C4';
                elseif ($m >= 45) $grade = 'P7';

                $ins_stmt->execute([(int)$pupil_id, $subject_id, $exam_id, $m, $grade]);
            }

            // B. Save/Update Class Teacher Comments & Conduct Remarks
            $com_stmt = $pdo->prepare("
                INSERT INTO Pupil_Exam_Comments (pupil_id, exam_id, teacher_comment, conduct_remark) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE teacher_comment=VALUES(teacher_comment), conduct_remark=VALUES(conduct_remark)
            ");

            foreach ($comments_arr as $pupil_id => $comment_text) {
                $p_id = (int)$pupil_id;
                $comment = trim($comment_text);
                $conduct = isset($conduct_arr[$p_id]) ? trim($conduct_arr[$p_id]) : 'Good';

                if (!empty($comment) || !empty($conduct)) {
                    $com_stmt->execute([$p_id, $exam_id, $comment, $conduct]);
                }
            }

            $pdo->commit();
            $message = "Exam marks and class teacher comments saved successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error saving marks and comments: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// ========================================================
// 2. FETCH DATA FOR ACTIVE CLASS
// ========================================================
$classes  = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);
$exams    = $pdo->query("SELECT * FROM Examinations ORDER BY academic_year DESC, exam_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$subjects_all = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$selected_class_id = isset($_GET['class_id']) && !empty($_GET['class_id']) ? (int)$_GET['class_id'] : ($classes[0]['class_id'] ?? null);
$selected_exam_id  = isset($_GET['exam_id']) && !empty($_GET['exam_id']) ? (int)$_GET['exam_id'] : ($exams[0]['exam_id'] ?? null);
$selected_sub_id   = isset($_GET['subject_id']) && !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;

// Fetch subjects assigned to selected class
$subjects = [];
if ($selected_class_id) {
    $sub_stmt = $pdo->prepare("
        SELECT s.* 
        FROM Class_Subject cs 
        JOIN Subjects s ON cs.subject_id = s.subject_id 
        WHERE cs.class_id = ? 
        ORDER BY s.subject_name ASC
    ");
    $sub_stmt->execute([$selected_class_id]);
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$selected_sub_id && !empty($subjects)) {
        $selected_sub_id = $subjects[0]['subject_id'];
    }
}

// Fetch active pupils in class
$pupils = [];
if ($selected_class_id) {
    $p_stmt = $pdo->prepare("
        SELECT p.*, c.class_name 
        FROM Pupils p
        LEFT JOIN Classes c ON p.class_id = c.class_id
        WHERE p.class_id = ? AND (p.status = 'Active' OR p.status IS NULL)
        ORDER BY p.first_name ASC
    ");
    $p_stmt->execute([$selected_class_id]);
    $pupils = $p_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch existing marks for selected exam & subject
$existing_marks = [];
if ($selected_class_id && $selected_exam_id && $selected_sub_id) {
    $m_stmt = $pdo->prepare("
        SELECT pupil_id, marks, grade 
        FROM Results 
        WHERE exam_id = ? AND subject_id = ?
    ");
    $m_stmt->execute([$selected_exam_id, $selected_sub_id]);
    $existing_marks_raw = $m_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing_marks_raw as $em) {
        $existing_marks[$em['pupil_id']] = $em;
    }
}

// Fetch all subject scores for preview engine: [pupil_id][exam_id][subject_id] => {marks, grade}
$all_results_raw = $pdo->query("SELECT pupil_id, exam_id, subject_id, marks, grade FROM Results")->fetchAll(PDO::FETCH_ASSOC);
$all_results = [];
foreach ($all_results_raw as $ar) {
    $all_results[$ar['pupil_id']][$ar['exam_id']][$ar['subject_id']] = [
        'marks' => (float)$ar['marks'],
        'grade' => $ar['grade']
    ];
}

// Fetch existing teacher comments for selected exam
$existing_comments = [];
if ($selected_class_id && $selected_exam_id) {
    $c_stmt = $pdo->prepare("
        SELECT pupil_id, teacher_comment, conduct_remark 
        FROM Pupil_Exam_Comments 
        WHERE exam_id = ?
    ");
    $c_stmt->execute([$selected_exam_id]);
    $existing_comments_raw = $c_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing_comments_raw as $ec) {
        $existing_comments[$ec['pupil_id']] = $ec;
    }
}

// Get selected exam details
$current_exam_details = null;
foreach ($exams as $ex) {
    if ($ex['exam_id'] == $selected_exam_id) {
        $current_exam_details = $ex;
        break;
    }
}

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>" style="background: <?= $message_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $message_type === 'success' ? '#bbf7d0' : '#fecaca' ?>; color: <?= $message_type === 'success' ? '#166534' : '#991b1b' ?>; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <?= $message ?>
    </div>
<?php endif; ?>

<!-- CLASS & EXAM SELECTOR BAR -->
<div class="card" style="margin-bottom: 20px; background: #1e3a8a; color: #fff;">
    <form action="class_teacher_dashboard.php" method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <h2 style="margin: 0; color: #fff; font-size: 1.25rem;">🏫 Class Teacher Grading & Report Desk</h2>
        
        <div style="display: flex; gap: 10px; align-items: center; margin-left: auto; flex-wrap: wrap;">
            <label style="margin: 0; font-weight: bold;">Class:</label>
            <select name="class_id" onchange="this.form.submit()" style="padding: 6px 10px; font-weight: bold; border-radius: 4px;">
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['class_id'] ?>" <?= $selected_class_id == $c['class_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="margin: 0; font-weight: bold;">Exam Set:</label>
            <select name="exam_id" onchange="this.form.submit()" style="padding: 6px 10px; font-weight: bold; border-radius: 4px;">
                <?php foreach ($exams as $ex): ?>
                    <option value="<?= $ex['exam_id'] ?>" <?= $selected_exam_id == $ex['exam_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ex['exam_name'] . ' (' . $ex['term'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="margin: 0; font-weight: bold;">Subject:</label>
            <select name="subject_id" onchange="this.form.submit()" style="padding: 6px 10px; font-weight: bold; border-radius: 4px;">
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= $sub['subject_id'] ?>" <?= $selected_sub_id == $sub['subject_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sub['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<!-- MARKS ENTRY & CLASS TEACHER COMMENTS TABLE WITH REPORT PREVIEW -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 12px;">
        <h2 style="margin:0;">Exam Score Entry & Teacher Remarks</h2>
        <!-- LIVE SEARCH BAR -->
        <input type="text" id="class_student_search" placeholder="🔍 Search student name or adm no..." style="padding: 6px 12px; width: 280px; border-radius: 6px; border: 1px solid #cbd5e1;">
    </div>

    <form action="class_teacher_dashboard.php?class_id=<?= $selected_class_id ?>&exam_id=<?= $selected_exam_id ?>&subject_id=<?= $selected_sub_id ?>" method="POST">
        <input type="hidden" name="save_student_marks_comments" value="1">
        <input type="hidden" name="exam_id" value="<?= $selected_exam_id ?>">
        <input type="hidden" name="subject_id" value="<?= $selected_sub_id ?>">

        <div style="overflow-x: auto;">
            <table id="student_marks_table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Adm No</th>
                        <th>Student Name</th>
                        <th>Score (%)</th>
                        <th>Grade</th>
                        <th>Class Teacher Comment</th>
                        <th>Conduct</th>
                        <th>Report Preview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pupils)): ?>
                        <tr><td colspan="8" style="text-align: center;">No students assigned to this class.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pupils as $p): ?>
                            <?php 
                                $p_id = (int)$p['pupil_id'];
                                $existing_score   = isset($existing_marks[$p_id]) ? $existing_marks[$p_id]['marks'] : '';
                                $existing_grade   = isset($existing_marks[$p_id]) ? $existing_marks[$p_id]['grade'] : '-';
                                $existing_comment = isset($existing_comments[$p_id]) ? $existing_comments[$p_id]['teacher_comment'] : '';
                                $existing_conduct = isset($existing_comments[$p_id]) ? $existing_comments[$p_id]['conduct_remark'] : 'Good';
                                $pupil_scores_all = $all_results[$p_id] ?? [];
                            ?>
                            <tr class="student-row" id="row_pupil_<?= $p_id ?>">
                                <td>
                                    <?php if (!empty($p['photo'])): ?>
                                        <img src="<?= htmlspecialchars($p['photo']) ?>" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:35px; height:35px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.75rem; color:#475569;">
                                            <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><small class="adm-no"><?= htmlspecialchars($p['admission_no']) ?></small></td>
                                <td class="student-name">
                                    <strong><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></strong>
                                </td>
                                <td>
                                    <input type="number" step="0.1" max="100" min="0" 
                                           name="marks[<?= $p_id ?>]" 
                                           value="<?= htmlspecialchars($existing_score) ?>" 
                                           placeholder="0-100" 
                                           style="width: 75px; padding: 5px; font-weight: bold;">
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9; color:#1e293b;"><?= htmlspecialchars($existing_grade) ?></span>
                                </td>
                                <td>
                                    <input type="text" name="comments[<?= $p_id ?>]" 
                                           id="comment_field_<?= $p_id ?>"
                                           value="<?= htmlspecialchars($existing_comment) ?>" 
                                           placeholder="e.g. Attentive pupil, needs practice in Math" 
                                           style="width: 100%; min-width: 200px; padding: 5px;">
                                </td>
                                <td>
                                    <select name="conduct[<?= $p_id ?>]" id="conduct_field_<?= $p_id ?>" style="padding: 5px;">
                                        <option value="Excellent" <?= $existing_conduct === 'Excellent' ? 'selected' : '' ?>>Excellent</option>
                                        <option value="Very Good" <?= $existing_conduct === 'Very Good' ? 'selected' : '' ?>>Very Good</option>
                                        <option value="Good" <?= $existing_conduct === 'Good' ? 'selected' : '' ?>>Good</option>
                                        <option value="Fair" <?= $existing_conduct === 'Fair' ? 'selected' : '' ?>>Fair</option>
                                        <option value="Needs Improvement" <?= $existing_conduct === 'Needs Improvement' ? 'selected' : '' ?>>Needs Improvement</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem; background:#166534;" 
                                            onclick='previewTeacherReport(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($current_exam_details, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($pupil_scores_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($subjects_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= $p_id ?>)'>
                                        👁️ Preview Report
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn" style="margin-top: 15px; background: #166534; font-size: 1rem; padding: 10px;">
            💾 Save Exam Marks & Class Teacher Remarks
        </button>
    </form>
</div>

<!-- LIVE REPORT PREVIEW JAVASCRIPT ENGINE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('class_student_search');
    const rows        = document.querySelectorAll('.student-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();

            rows.forEach(row => {
                const name  = row.querySelector('.student-name').textContent.toLowerCase();
                const adm   = row.querySelector('.adm-no').textContent.toLowerCase();

                if (name.includes(query) || adm.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

// POPUP LIVE PREVIEW ENGINE FOR CLASS TEACHER
function previewTeacherReport(pupil, exam, pupilScoresAll, allSubjects, pupilId) {
    if (!exam) {
        alert("Please select an Examination Set first.");
        return;
    }

    // Read real-time edited comment and conduct inputs from DOM
    const liveCommentField = document.getElementById('comment_field_' + pupilId);
    const liveConductField = document.getElementById('conduct_field_' + pupilId);

    const liveComment = liveCommentField ? liveCommentField.value : 'An attentive student showing steady academic progress.';
    const liveConduct = liveConductField ? liveConductField.value : 'Good';

    const examId = exam.exam_id;
    const pupilExamScores = pupilScoresAll[examId] || {};

    let rowsHtml = '';
    let grandTotal = 0;
    let subjectCount = 0;

    allSubjects.forEach(sub => {
        const sId = sub.subject_id;
        if (pupilExamScores[sId]) {
            const score = parseFloat(pupilExamScores[sId].marks);
            const grade = pupilExamScores[sId].grade;

            grandTotal += score;
            subjectCount++;

            rowsHtml += `
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">${sub.subject_name}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>${score.toFixed(1)}%</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>${grade}</strong></td>
                </tr>
            `;
        }
    });

    const avgScore = subjectCount > 0 ? (grandTotal / subjectCount) : 0;
    let division = 'Division 4';
    if (avgScore >= 75) division = 'Division 1';
    else if (avgScore >= 60) division = 'Division 2';
    else if (avgScore >= 45) division = 'Division 3';

    const previewWin = window.open('', '_blank', 'width=800,height=700');
    if (!previewWin) {
        alert('Please allow popups to view the student report card preview.');
        return;
    }

    previewWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Preview Report - ${pupil.first_name} ${pupil.last_name}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; color: #1e293b; background: #fff; }
                .preview-banner { background: #fef3c7; color: #92400e; padding: 8px 12px; border: 1px solid #fcd34d; border-radius: 4px; font-weight: bold; text-align: center; margin-bottom: 20px; font-size: 0.85rem; }
                .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #1e3a8a; font-size: 1.5rem; }
                .header p { margin: 3px 0 0 0; font-size: 0.85rem; color: #64748b; }
                .info-table, .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem; }
                .info-table td { padding: 6px; }
                .report-table th, .report-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
                .report-table th { background: #f1f5f9; color: #1e3a8a; }
                .summary-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 12px; border-radius: 6px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem; }
                .remarks-box { background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; border-radius: 6px; margin-bottom: 25px; font-size: 0.9rem; }
                .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 0.85rem; }
            </style>
        </head>
        <body>
            <div class="preview-banner">🔍 CLASS TEACHER LIVE REPORT CARD PREVIEW</div>

            <div class="header">
                <h1>COMRADES PREPARATORY SCHOOL</h1>
                <p>Kitebi Campus - Terminal Student Progress Report Card</p>
            </div>

            <table class="info-table">
                <tr><td><strong>Pupil Name:</strong> ${pupil.first_name} ${pupil.last_name}</td><td><strong>Admission No:</strong> ${pupil.admission_no}</td></tr>
                <tr><td><strong>Class Level:</strong> ${pupil.class_name || 'N/A'}</td><td><strong>Exam Term:</strong> ${exam.exam_name} (${exam.term} ${exam.academic_year})</td></tr>
            </table>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Score (%)</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml.length > 0 ? rowsHtml : '<tr><td colspan="3" style="text-align:center;">No subject marks recorded for this exam set yet.</td></tr>'}
                </tbody>
            </table>

            <div class="summary-box">
                <div>Total Score: <strong>${grandTotal.toFixed(1)}</strong></div>
                <div>Average Score: <strong>${avgScore.toFixed(1)}%</strong></div>
                <div>Calculated Division: <strong>${division}</strong></div>
                <div>Subjects Logged: <strong>${subjectCount}</strong></div>
            </div>

            <div class="remarks-box">
                <div style="margin-bottom: 6px;"><strong>General Conduct:</strong> ${liveConduct}</div>
                <div><strong>Class Teacher Remarks:</strong> <em>"${liveComment || 'None provided'}"</em></div>
            </div>

            <div class="footer">
                <div>Class Teacher Signature: __________________</div>
                <div>Headteacher Signature: __________________</div>
            </div>
        </body>
        </html>
    `);
    previewWin.document.close();
}
</script>
</body>
</html>