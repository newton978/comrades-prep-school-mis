<?php
// ========================================================
// 1. DATABASE CONNECTION
// ========================================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'comrades_prep_school_db';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$message = '';
$edit_pupil = null;

// ========================================================
// 2. CRUD BACKEND HANDLERS
// ========================================================

// --- CREATE & UPDATE HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $admission_no = trim($_POST['admission_no']);
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $gender       = $_POST['gender'];
    $class_id     = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
    $tuition_fee  = (float)$_POST['tuition_fee'];
    $amount_paid  = (float)$_POST['amount_paid'];

    // Parent details
    $parent_name  = trim($_POST['parent_name']);
    $parent_phone = trim($_POST['parent_phone']);
    
    // Exam details
    $subject_id   = !empty($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
    $marks        = ($_POST['marks'] !== '') ? (float)$_POST['marks'] : null;

    // Server-side grade calculation
    $grade = null;
    if ($marks !== null) {
        if ($marks >= 80) $grade = 'D1';
        elseif ($marks >= 75) $grade = 'D2';
        elseif ($marks >= 65) $grade = 'C3';
        elseif ($marks >= 55) $grade = 'C4';
        elseif ($marks >= 45) $grade = 'P7';
        else $grade = 'F9';
    }

    if ($action === 'create') {
        try {
            $pdo->beginTransaction();

            // 1. Insert Pupil
            $stmt = $pdo->prepare("INSERT INTO Pupils (admission_no, first_name, last_name, gender, class_id, tuition_fee, amount_paid) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$admission_no, $first_name, $last_name, $gender, $class_id, $tuition_fee, $amount_paid]);
            $pupil_id = $pdo->lastInsertId();

            // 2. Insert Parent & Link
            if (!empty($parent_name)) {
                $p_stmt = $pdo->prepare("INSERT INTO Parents (parent_name, phone) VALUES (?, ?)");
                $p_stmt->execute([$parent_name, $parent_phone]);
                $parent_id = $pdo->lastInsertId();

                $link_stmt = $pdo->prepare("INSERT INTO Pupil_Parent (pupil_id, parent_id) VALUES (?, ?)");
                $link_stmt->execute([$pupil_id, $parent_id]);
            }

            // 3. Insert Grade Record if Subject and Score selected
            if ($subject_id && $marks !== null) {
                // Defaulting to Exam ID 1 for demonstration
                $r_stmt = $pdo->prepare("INSERT INTO Results (pupil_id, subject_id, exam_id, marks, grade) VALUES (?, ?, 1, ?, ?)");
                $r_stmt->execute([$pupil_id, $subject_id, $marks, $grade]);
            }

            $pdo->commit();
            $message = "Pupil and associated records saved successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error saving record: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $pupil_id = (int)$_POST['pupil_id'];
        try {
            $pdo->beginTransaction();

            // Update Pupil
            $stmt = $pdo->prepare("UPDATE Pupils SET admission_no=?, first_name=?, last_name=?, gender=?, class_id=?, tuition_fee=?, amount_paid=? WHERE pupil_id=?");
            $stmt->execute([$admission_no, $first_name, $last_name, $gender, $class_id, $tuition_fee, $amount_paid, $pupil_id]);

            // Update/Insert Grade
            if ($subject_id && $marks !== null) {
                $r_stmt = $pdo->prepare("INSERT INTO Results (pupil_id, subject_id, exam_id, marks, grade) 
                                        VALUES (?, ?, 1, ?, ?) 
                                        ON DUPLICATE KEY UPDATE marks=VALUES(marks), grade=VALUES(grade)");
                $r_stmt->execute([$pupil_id, $subject_id, $marks, $grade]);
            }

            $pdo->commit();
            $message = "Pupil record updated!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error updating: " . $e->getMessage();
        }
    }
}

// --- DELETE HANDLER ---
if (isset($_GET['delete'])) {
    $pupil_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Pupils WHERE pupil_id = ?");
    $stmt->execute([$pupil_id]);
    header("Location: index.php?msg=deleted");
    exit();
}

// --- EDIT FETCH HANDLER ---
if (isset($_GET['edit'])) {
    $pupil_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("
        SELECT p.*, par.parent_name, par.phone AS parent_phone
        FROM Pupils p
        LEFT JOIN Pupil_Parent pp ON p.pupil_id = pp.pupil_id
        LEFT JOIN Parents par ON pp.parent_id = par.parent_id
        WHERE p.pupil_id = ?
    ");
    $stmt->execute([$pupil_id]);
    $edit_pupil = $stmt->fetch();
}

// --- FETCH DISPLAY DATA ---
$classes  = $pdo->query("SELECT c.*, t.teacher_name FROM Classes c LEFT JOIN Teachers t ON c.class_teacher_id = t.teacher_id")->fetchAll();
$subjects = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name ASC")->fetchAll();
$pupils   = $pdo->query("
    SELECT 
        p.*, 
        c.class_name,
        par.parent_name,
        par.phone AS parent_phone,
        r.marks,
        r.grade,
        s.subject_name
    FROM Pupils p 
    LEFT JOIN Classes c ON p.class_id = c.class_id 
    LEFT JOIN Pupil_Parent pp ON p.pupil_id = pp.pupil_id
    LEFT JOIN Parents par ON pp.parent_id = par.parent_id
    LEFT JOIN Results r ON p.pupil_id = r.pupil_id
    LEFT JOIN Subjects s ON r.subject_id = s.subject_id
    ORDER BY p.pupil_id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comrades Preparatory School Portal</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
            --danger: #dc2626;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }

        .container { max-width: 1250px; margin: 0 auto; }

        header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert {
            padding: 12px;
            background-color: #dbeafe;
            color: #1e40af;
            border-left: 4px solid var(--primary);
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 20px;
        }

        @media (max-width: 950px) { .grid { grid-template-columns: 1fr; } }

        .card {
            background: var(--surface);
            border-radius: 8px;
            padding: 18px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card h2 {
            margin-top: 0;
            font-size: 1.15rem;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 8px;
        }

        .section-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: #64748b;
            text-transform: uppercase;
            margin: 12px 0 6px;
        }

        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.8rem; margin-bottom: 3px; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .calc-preview {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        .calc-preview div { display: flex; justify-content: space-between; margin-bottom: 4px; }

        button.btn {
            width: 100%;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        button.btn:hover { background-color: var(--primary-hover); }

        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background-color: #f1f5f9; font-weight: 600; }

        .badge { padding: 3px 6px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }

        .actions a { text-decoration: none; margin-right: 6px; font-weight: 600; }
        .actions a.edit { color: var(--primary); }
        .actions a.delete { color: var(--danger); }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1 style="margin:0;">Comrades Preparatory School</h1>
        <p style="margin:4px 0 0; opacity:0.9;">School Management & Grade Tracking System</p>
    </header>

    <?php if ($message || isset($_GET['msg'])): ?>
        <div class="alert">
            <?= htmlspecialchars($message ?: ($_GET['msg'] === 'deleted' ? 'Record deleted successfully!' : '')) ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <!-- REGISTRATION & EDIT FORM -->
        <div class="card">
            <h2><?= $edit_pupil ? 'Edit Pupil & Parent Record' : 'Register Pupil & Parent' ?></h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="<?= $edit_pupil ? 'update' : 'create' ?>">
                <?php if ($edit_pupil): ?>
                    <input type="hidden" name="pupil_id" value="<?= $edit_pupil['pupil_id'] ?>">
                <?php endif; ?>

                <div class="section-title">Pupil Information</div>
                <div class="form-group">
                    <label>Admission No</label>
                    <input type="text" name="admission_no" value="<?= htmlspecialchars($edit_pupil['admission_no'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($edit_pupil['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($edit_pupil['last_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="M" <?= ($edit_pupil['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Male</option>
                        <option value="F" <?= ($edit_pupil['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class (Assigned Teacher)</label>
                    <select name="class_id">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($edit_pupil['class_id'] ?? '') == $c['class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['class_name']) ?> (Tr. <?= htmlspecialchars($c['teacher_name'] ?? 'Unassigned') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="section-title">Parent / Guardian Details</div>
                <div class="form-group">
                    <label>Parent Name</label>
                    <input type="text" name="parent_name" value="<?= htmlspecialchars($edit_pupil['parent_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Parent Phone</label>
                    <input type="text" name="parent_phone" value="<?= htmlspecialchars($edit_pupil['parent_phone'] ?? '') ?>">
                </div>

                <div class="section-title">Fees & Academic Performance</div>
                <div class="form-group">
                    <label>Tuition Fee (UGX)</label>
                    <input type="number" step="0.01" id="tuition_fee" name="tuition_fee" value="<?= htmlspecialchars($edit_pupil['tuition_fee'] ?? '0.00') ?>" required>
                </div>
                <div class="form-group">
                    <label>Amount Paid</label>
                    <input type="number" step="0.01" id="amount_paid" name="amount_paid" value="<?= htmlspecialchars($edit_pupil['amount_paid'] ?? '0.00') ?>" required>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id">
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marks Obtained (%)</label>
                    <input type="number" step="0.1" max="100" min="0" id="marks" name="marks">
                </div>

                <!-- LIVE JAVASCRIPT PREVIEW -->
                <div class="calc-preview">
                    <div><strong>Balance Due:</strong> <span id="calc_balance">0.00</span></div>
                    <div><strong>Live Grade Preview:</strong> <span id="calc_grade">N/A</span></div>
                </div>

                <button type="submit" class="btn"><?= $edit_pupil ? 'Update Record' : 'Save Complete Record' ?></button>
            </form>
        </div>

        <!-- RECORDS DISPLAY TABLE -->
        <div class="card">
            <h2>Registered Pupils & Academic Records</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Adm No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Parent & Phone</th>
                            <th>Fee Balance</th>
                            <th>Subject Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pupils)): ?>
                            <tr><td colspan="7" style="text-align: center;">No records available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pupils as $p): 
                                $balance = $p['tuition_fee'] - $p['amount_paid'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['admission_no']) ?></td>
                                    <td><strong><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['class_name'] ?? 'Unassigned') ?></td>
                                    <td>
                                        <?= htmlspecialchars($p['parent_name'] ?? 'N/A') ?>
                                        <br><small><?= htmlspecialchars($p['parent_phone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $balance <= 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= number_format($balance, 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($p['marks'] !== null): ?>
                                            <?= htmlspecialchars($p['subject_name']) ?>: <?= number_format($p['marks'], 1) ?>% (<strong><?= $p['grade'] ?></strong>)
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="index.php?edit=<?= $p['pupil_id'] ?>" class="edit">Edit</a>
                                        <a href="index.php?delete=<?= $p['pupil_id'] ?>" class="delete" onclick="return confirm('Delete record?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CLIENT-SIDE COMPUTATIONS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tuitionInput = document.getElementById('tuition_fee');
    const paidInput    = document.getElementById('amount_paid');
    const marksInput   = document.getElementById('marks');
    
    const balanceOut   = document.getElementById('calc_balance');
    const gradeOut     = document.getElementById('calc_grade');

    function calculateValues() {
        // Balance Calculation
        const tuition = parseFloat(tuitionInput.value) || 0;
        const paid    = parseFloat(paidInput.value) || 0;
        const balance = tuition - paid;
        
        balanceOut.textContent = balance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        balanceOut.style.color = balance > 0 ? '#dc2626' : '#166534';

        // Live Ugandan Primary Grading Scale Calculation
        const scoreVal = marksInput.value.trim();
        if (scoreVal === '') {
            gradeOut.textContent = 'N/A';
            return;
        }

        const score = parseFloat(scoreVal);
        let grade = 'F9';
        if (score >= 80) grade = 'D1 (Distinction)';
        else if (score >= 75) grade = 'D2 (Distinction)';
        else if (score >= 65) grade = 'C3 (Credit)';
        else if (score >= 55) grade = 'C4 (Credit)';
        else if (score >= 45) grade = 'P7 (Pass)';

        gradeOut.textContent = grade;
    }

    tuitionInput.addEventListener('input', calculateValues);
    paidInput.addEventListener('input', calculateValues);
    marksInput.addEventListener('input', calculateValues);

    calculateValues();
});
</script>

</body>
</html>