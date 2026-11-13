<?php
require_once 'db.php';

$message = '';

// ========================================================
// 1. POST HANDLERS (ASSIGN TEACHER & ASSIGN SUBJECTS)
// ========================================================

// --- ASSIGN / UPDATE CLASS TEACHER HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_teacher'])) {
    $class_id   = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
    $teacher_id = !empty($_POST['class_teacher_id']) ? (int)$_POST['class_teacher_id'] : null;
    
    if ($class_id) {
        try {
            $stmt = $pdo->prepare("UPDATE Classes SET class_teacher_id = ? WHERE class_id = ?");
            $stmt->execute([$teacher_id, $class_id]);
            $message = "Class teacher updated successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Please select a valid class.";
    }
}

// --- ASSIGN / UPDATE SUBJECTS TO CLASS HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_subjects'])) {
    $class_id          = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
    $selected_subjects = $_POST['subject_ids'] ?? [];

    if ($class_id) {
        try {
            $pdo->beginTransaction();

            $del_stmt = $pdo->prepare("DELETE FROM Class_Subject WHERE class_id = ?");
            $del_stmt->execute([$class_id]);

            if (!empty($selected_subjects)) {
                $ins_stmt = $pdo->prepare("INSERT INTO Class_Subject (class_id, subject_id) VALUES (?, ?)");
                foreach ($selected_subjects as $sub_id) {
                    $ins_stmt->execute([$class_id, (int)$sub_id]);
                }
            }

            $pdo->commit();
            $message = "Class curriculum updated successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error updating subjects: " . $e->getMessage();
        }
    }
}

// ========================================================
// 2. FETCH DATA & DETERMINE SELECTED CLASS
// ========================================================
$selected_class = isset($_GET['view']) && !empty($_GET['view']) ? (int)$_GET['view'] : null;

// Fetch all classes with teachers, student count, and assigned subject names
$classes = $pdo->query("
    SELECT 
        c.*, 
        t.teacher_name, 
        COUNT(DISTINCT p.pupil_id) AS total_students,
        COUNT(DISTINCT cs.subject_id) AS total_subjects,
        GROUP_CONCAT(DISTINCT s.subject_name ORDER BY s.subject_name SEPARATOR ', ') AS assigned_subjects
    FROM Classes c
    LEFT JOIN Teachers t ON c.class_teacher_id = t.teacher_id
    LEFT JOIN Pupils p ON c.class_id = p.class_id
    LEFT JOIN Class_Subject cs ON c.class_id = cs.class_id
    LEFT JOIN Subjects s ON cs.subject_id = s.subject_id
    GROUP BY c.class_id
    ORDER BY c.class_id ASC
")->fetchAll();

$teachers     = $pdo->query("SELECT * FROM Teachers ORDER BY teacher_name ASC")->fetchAll();
$all_subjects = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name ASC")->fetchAll();

$roster = [];
$selected_class_details = null;
$assigned_subject_ids   = [];

if ($selected_class) {
    $c_stmt = $pdo->prepare("
        SELECT c.*, t.teacher_name 
        FROM Classes c 
        LEFT JOIN Teachers t ON c.class_teacher_id = t.teacher_id 
        WHERE c.class_id = ?
    ");
    $c_stmt->execute([$selected_class]);
    $selected_class_details = $c_stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT p.*, par.parent_name, par.phone 
        FROM Pupils p 
        LEFT JOIN Pupil_Parent pp ON p.pupil_id = pp.pupil_id 
        LEFT JOIN Parents par ON pp.parent_id = par.parent_id 
        WHERE p.class_id = ?
        ORDER BY p.first_name ASC
    ");
    $stmt->execute([$selected_class]);
    $roster = $stmt->fetchAll();

    $sub_stmt = $pdo->prepare("SELECT subject_id FROM Class_Subject WHERE class_id = ?");
    $sub_stmt->execute([$selected_class]);
    $assigned_subject_ids = $sub_stmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once 'header.php';
?>

<style>
    .class-pill-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }
    .class-pill {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        text-decoration: none;
        color: var(--text);
        transition: all 0.2s ease;
    }
    .class-pill:hover, .class-pill.active {
        border-color: var(--primary);
        background: #eff6ff;
        color: var(--primary);
    }
    .class-pill strong { display: block; font-size: 0.95rem; }
    .class-pill span { font-size: 0.75rem; color: #64748b; }
    
    .subject-selector-box {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        max-height: 220px;
        overflow-y: auto;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 6px;
    }
    .subject-check-item {
        background: #fff;
        padding: 8px 10px;
        border-radius: 4px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .stats-bar {
        display: flex;
        gap: 15px;
        background: #f1f5f9;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    .stat-item {
        font-size: 0.85rem;
    }
    .stat-item strong { color: var(--primary); }
</style>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- 1. QUICK CLASS SELECTOR BAR -->
<div class="card" style="margin-bottom: 20px;">
    <h2>Select Class Level</h2>
    <div class="class-pill-grid">
        <?php foreach ($classes as $c): ?>
            <a href="classes.php?view=<?= $c['class_id'] ?>" class="class-pill <?= $selected_class == $c['class_id'] ? 'active' : '' ?>">
                <strong><?= htmlspecialchars($c['class_name']) ?></strong>
                <span><?= $c['total_students'] ?> Pupils | <?= $c['total_subjects'] ?> Subj</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($selected_class_details): ?>
    <!-- STATS OVERVIEW FOR SELECTED CLASS -->
    <div class="stats-bar">
        <div class="stat-item">Class: <strong><?= htmlspecialchars($selected_class_details['class_name']) ?></strong></div>
        <div class="stat-item">Class Teacher: <strong><?= htmlspecialchars($selected_class_details['teacher_name'] ?? 'Unassigned') ?></strong></div>
        <div class="stat-item">Enrolled Pupils: <strong><?= count($roster) ?></strong></div>
        <div class="stat-item">Assigned Subjects: <strong><?= count($assigned_subject_ids) ?></strong></div>
    </div>
<?php endif; ?>

<div class="grid">
    <div>
        <!-- 2. ASSIGN SUBJECTS TO CLASS -->
        <div class="card">
            <h2>Assign Subjects to <?= $selected_class_details ? htmlspecialchars($selected_class_details['class_name']) : 'Selected Class' ?></h2>
            
            <?php if (!$selected_class): ?>
                <p style="color: #64748b; font-size: 0.9rem;">Please select a class from the top level selector to configure subjects.</p>
            <?php else: ?>
                <form action="classes.php?view=<?= $selected_class ?>" method="POST">
                    <input type="hidden" name="assign_subjects" value="1">
                    <input type="hidden" name="class_id" value="<?= $selected_class ?>">

                    <div class="form-group">
                        <label>Select Subjects for this Class Curriculum:</label>
                        <div class="subject-selector-box">
                            <?php foreach ($all_subjects as $s): ?>
                                <label class="subject-check-item">
                                    <input type="checkbox" name="subject_ids[]" value="<?= $s['subject_id'] ?>" 
                                        <?= in_array($s['subject_id'], $assigned_subject_ids) ? 'checked' : '' ?>>
                                    <span>
                                        <strong><?= htmlspecialchars($s['subject_name']) ?></strong> 
                                        <?= !empty($s['subject_code']) ? '(' . htmlspecialchars($s['subject_code']) . ')' : '' ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn">Save Class Curriculum</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- 3. ASSIGN CLASS TEACHER -->
        <div class="card">
            <h2>Assign Class Teacher</h2>
            <form action="classes.php<?= $selected_class ? '?view='.$selected_class : '' ?>" method="POST">
                <input type="hidden" name="assign_teacher" value="1">
                
                <div class="form-group">
                    <label>Class</label>
                    <select name="class_id" required>
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>" <?= $selected_class == $c['class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['class_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assigned Teacher</label>
                    <select name="class_teacher_id">
                        <option value="">No Teacher (Unassigned)</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['teacher_id'] ?>" <?= (isset($selected_class_details['teacher_name']) && $selected_class_details['teacher_name'] === $t['teacher_name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['teacher_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">Update Class Teacher</button>
            </form>
        </div>
    </div>

    <!-- 4. ENROLLED ROSTER TABLE -->
    <div class="card">
        <h2>
            <?= $selected_class_details ? htmlspecialchars($selected_class_details['class_name']) . ' Pupil Roster' : 'Pupil Roster' ?>
        </h2>

        <?php if ($selected_class): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Adm No</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Parent Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roster)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #64748b;">No pupils registered in <?= htmlspecialchars($selected_class_details['class_name']) ?> yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($roster as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['admission_no']) ?></td>
                                    <td><strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($r['gender']) ?></td>
                                    <td><?= htmlspecialchars($r['parent_name'] ?? 'N/A') ?> (<?= htmlspecialchars($r['phone'] ?? '-') ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #64748b;">Select a class level above to inspect enrolled pupils and curriculum.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>