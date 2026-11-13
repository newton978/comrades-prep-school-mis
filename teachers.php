<?php
require_once 'db.php';

$message = '';
$edit_teacher = null;

// Ensure uploads directory exists
$upload_dir = 'uploads/teachers/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ========================================================
// 1. CREATE & UPDATE HANDLERS
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action            = $_POST['action'] ?? '';
    $staff_code        = trim($_POST['staff_code']);
    $teacher_name      = trim($_POST['teacher_name']);
    $phone             = trim($_POST['phone']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $qualification     = trim($_POST['qualification']);
    $role              = trim($_POST['role']);
    $assigned_class_id = !empty($_POST['assigned_class_id']) ? (int)$_POST['assigned_class_id'] : null;
    $national_id       = trim($_POST['national_id']);
    $dob               = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $address           = trim($_POST['address']);
    $salary            = (float)$_POST['salary'];
    $termly_salary     = (float)$_POST['termly_salary'];
    $amount_paid       = (float)$_POST['amount_paid'];

    // Handle Photo Upload
    $photo_path = $_POST['existing_photo'] ?? null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['photo']['tmp_name'];
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES['photo']['name']);
        $target_file = $upload_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['photo']['type'], $allowed_types)) {
            if (move_uploaded_file($file_tmp, $target_file)) {
                $photo_path = $target_file;
            }
        }
    }

    if ($action === 'create') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Teachers (staff_code, teacher_name, photo, phone, emergency_contact, qualification, role, assigned_class_id, national_id, dob, address, salary, termly_salary, amount_paid) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$staff_code, $teacher_name, $photo_path, $phone, $emergency_contact, $qualification, $role, $assigned_class_id, $national_id, $dob, $address, $salary, $termly_salary, $amount_paid]);
            $message = "Teacher record created successfully with photo!";
        } catch (PDOException $e) {
            $message = "Error creating teacher: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $teacher_id = (int)$_POST['teacher_id'];
        try {
            $stmt = $pdo->prepare("
                UPDATE Teachers 
                SET staff_code = ?, teacher_name = ?, photo = ?, phone = ?, emergency_contact = ?, qualification = ?, role = ?, assigned_class_id = ?, national_id = ?, dob = ?, address = ?, salary = ?, termly_salary = ?, amount_paid = ? 
                WHERE teacher_id = ?
            ");
            $stmt->execute([$staff_code, $teacher_name, $photo_path, $phone, $emergency_contact, $qualification, $role, $assigned_class_id, $national_id, $dob, $address, $salary, $termly_salary, $amount_paid, $teacher_id]);
            $message = "Teacher record updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating teacher: " . $e->getMessage();
        }
    }
}

// ========================================================
// 2. DELETE HANDLER
// ========================================================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Teachers WHERE teacher_id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        header("Location: teachers.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $message = "Error deleting teacher: " . $e->getMessage();
    }
}

// ========================================================
// 3. EDIT FETCH HANDLER
// ========================================================
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Teachers WHERE teacher_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_teacher = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ========================================================
// 4. FETCH DATA (CLASSES & TEACHERS DIRECTORY)
// ========================================================
$classes = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);

$teachers = $pdo->query("
    SELECT t.*, c.class_name, (t.termly_salary - t.amount_paid) AS salary_balance
    FROM Teachers t 
    LEFT JOIN Classes c ON t.assigned_class_id = c.class_id
    ORDER BY t.teacher_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<?php if ($message || isset($_GET['msg'])): ?>
    <div class="alert">
        <?= htmlspecialchars($message ?: ($_GET['msg'] === 'deleted' ? 'Teacher deleted successfully!' : '')) ?>
    </div>
<?php endif; ?>

<div class="grid">
    <!-- FORM SECTION: CREATE & EDIT -->
    <div class="card">
        <h2><?= $edit_teacher ? 'Edit Staff Profile' : 'Add New Staff Member' ?></h2>
        <form action="teachers.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_teacher ? 'update' : 'create' ?>">
            <?php if ($edit_teacher): ?>
                <input type="hidden" name="teacher_id" value="<?= $edit_teacher['teacher_id'] ?>">
                <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($edit_teacher['photo'] ?? '') ?>">
            <?php endif; ?>

            <div style="font-weight: bold; color: var(--primary); margin-bottom: 8px;">Personal & Identification Info</div>

            <div class="form-group">
                <label>Staff Code / ID</label>
                <input type="text" name="staff_code" value="<?= htmlspecialchars($edit_teacher['staff_code'] ?? '') ?>" placeholder="e.g. TR-2026-01" required>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="teacher_name" value="<?= htmlspecialchars($edit_teacher['teacher_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Passport Photo</label>
                <input type="file" name="photo" accept="image/*">
                <?php if ($edit_teacher && !empty($edit_teacher['photo'])): ?>
                    <img src="<?= htmlspecialchars($edit_teacher['photo']) ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-top:5px;">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>National ID No. (NIN)</label>
                <input type="text" name="national_id" value="<?= htmlspecialchars($edit_teacher['national_id'] ?? '') ?>" placeholder="e.g. CM92019481920A">
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($edit_teacher['dob'] ?? '') ?>">
            </div>

            <div style="margin-top: 15px; font-weight: bold; color: var(--primary);">Contact & Residence</div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($edit_teacher['phone'] ?? '') ?>" placeholder="e.g. 0700000000">
            </div>

            <div class="form-group">
                <label>Emergency Contact Person & Tel</label>
                <input type="text" name="emergency_contact" value="<?= htmlspecialchars($edit_teacher['emergency_contact'] ?? '') ?>" placeholder="e.g. Spouse / Brother (0770000000)">
            </div>

            <div class="form-group">
                <label>Residence / Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($edit_teacher['address'] ?? '') ?>" placeholder="e.g. Kitebi, Kampala">
            </div>

            <div style="margin-top: 15px; font-weight: bold; color: var(--primary);">Professional Role & Class Allocation</div>
            
            <div class="form-group">
                <label>Staff Role / Designation</label>
                <select name="role" required>
                    <option value="Class Teacher" <?= ($edit_teacher['role'] ?? '') === 'Class Teacher' ? 'selected' : '' ?>>Class Teacher</option>
                    <option value="Subject Teacher" <?= ($edit_teacher['role'] ?? '') === 'Subject Teacher' ? 'selected' : '' ?>>Subject Teacher</option>
                    <option value="Head of Department" <?= ($edit_teacher['role'] ?? '') === 'Head of Department' ? 'selected' : '' ?>>Head of Department (HOD)</option>
                    <option value="Director of Studies" <?= ($edit_teacher['role'] ?? '') === 'Director of Studies' ? 'selected' : '' ?>>Director of Studies (DOS)</option>
                    <option value="Senior Teacher" <?= ($edit_teacher['role'] ?? '') === 'Senior Teacher' ? 'selected' : '' ?>>Senior Teacher</option>
                    <option value="Assistant Teacher" <?= ($edit_teacher['role'] ?? '') === 'Assistant Teacher' ? 'selected' : '' ?>>Assistant Teacher</option>
                </select>
            </div>

            <div class="form-group">
                <label>Assigned Class (If Class Teacher)</label>
                <select name="assigned_class_id">
                    <option value="">-- Unassigned / N/A --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($edit_teacher['assigned_class_id'] ?? '') == $c['class_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Qualification / Specialization</label>
                <input type="text" name="qualification" value="<?= htmlspecialchars($edit_teacher['qualification'] ?? '') ?>" placeholder="e.g. B.Ed Primary, Grade III Cert">
            </div>

            <div style="margin-top: 15px; font-weight: bold; color: var(--primary);">Payroll & Salary Breakdown (UGX)</div>
            
            <div class="form-group">
                <label>Monthly Base Salary (UGX)</label>
                <input type="number" step="1000" id="t_salary" name="salary" value="<?= htmlspecialchars($edit_teacher['salary'] ?? '0.00') ?>" required>
            </div>

            <div class="form-group">
                <label>Billed Termly Salary (UGX)</label>
                <input type="number" step="1000" id="t_termly" name="termly_salary" value="<?= htmlspecialchars($edit_teacher['termly_salary'] ?? '0.00') ?>" placeholder="Base Salary × 3 Months" required>
            </div>

            <div class="form-group">
                <label>Term Salary Amount Paid So Far (UGX)</label>
                <input type="number" step="1000" id="t_paid" name="amount_paid" value="<?= htmlspecialchars($edit_teacher['amount_paid'] ?? '0.00') ?>" required>
            </div>

            <div style="background: #f1f5f9; padding: 10px; border-radius: 4px; margin-top: 6px; font-size: 0.85rem;">
                <strong>Termly Salary Balance Due:</strong> UGX <span id="calc_salary_bal">0.00</span>
            </div>

            <button type="submit" class="btn" style="margin-top: 15px;"><?= $edit_teacher ? 'Update Teacher Profile' : 'Save Teacher Profile' ?></button>
            <?php if ($edit_teacher): ?>
                <p style="text-align: center; margin-top: 8px;"><a href="teachers.php" style="color: var(--text);">Cancel Editing</a></p>
            <?php endif; ?>
        </form>
    </div>

    <!-- TEACHERS DIRECTORY TABLE & ID CARD ENGINE -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:12px;">
            <h2 style="margin:0;">Teachers Directory & Payroll</h2>
            <button type="button" class="btn" style="background:#166534; padding:6px 12px; font-size:0.8rem;" 
                    onclick='batchPrintStaffIDs(<?= json_encode($teachers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                🖨️ Batch Print Staff IDs (<?= count($teachers) ?>)
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Code & Name</th>
                        <th>Role & Class</th>
                        <th>Contact</th>
                        <th>Salary / Term</th>
                        <th>Term Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr><td colspan="7" style="text-align: center;">No teachers registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($t['photo'])): ?>
                                        <img src="<?= htmlspecialchars($t['photo']) ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:40px; height:40px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.8rem; color:#475569;">
                                            <?= strtoupper(substr($t['teacher_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['teacher_name']) ?></strong>
                                    <br><small style="color:#64748b;"><?= htmlspecialchars($t['staff_code'] ?? ('TR-' . $t['teacher_id'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background:#eff6ff; color:#1e3a8a; font-size:0.75rem;"><?= htmlspecialchars($t['role']) ?></span>
                                    <br><small><?= htmlspecialchars($t['class_name'] ?? 'No Assigned Class') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($t['phone'] ?? 'No Phone') ?>
                                    <?php if (!empty($t['emergency_contact'])): ?>
                                        <br><small style="color: #64748b;">Emg: <?= htmlspecialchars($t['emergency_contact']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    Termly: UGX <?= number_format($t['termly_salary']) ?>
                                    <br><small style="color: #166534;">Paid: UGX <?= number_format($t['amount_paid']) ?></small>
                                </td>
                                <td>
                                    <span class="badge <?= $t['salary_balance'] <= 0 ? 'badge-success' : 'badge-danger' ?>">
                                        UGX <?= number_format($t['salary_balance']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button type="button" class="btn" style="padding: 3px 6px; font-size: 0.75rem; background:#166534; margin-bottom:2px;" 
                                            onclick='printStaffCard(<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        Print ID
                                    </button>
                                    <a href="teachers.php?edit=<?= $t['teacher_id'] ?>" class="edit">Edit</a>
                                    <a href="teachers.php?delete=<?= $t['teacher_id'] ?>" class="delete" onclick="return confirm('Delete teacher record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SALARY CALCULATOR & PRINT STAFF ID SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const salInput   = document.getElementById('t_salary');
    const termInput  = document.getElementById('t_termly');
    const paidInput  = document.getElementById('t_paid');
    const balOut     = document.getElementById('calc_salary_bal');

    // Auto calculate termly salary if base monthly salary changes
    salInput.addEventListener('input', function() {
        const base = parseFloat(salInput.value) || 0;
        if (!termInput.value || termInput.value == 0) {
            termInput.value = base * 3;
        }
        updateSalaryBalance();
    });

    function updateSalaryBalance() {
        const termly = parseFloat(termInput.value) || 0;
        const paid   = parseFloat(paidInput.value) || 0;
        const bal    = termly - paid;
        
        balOut.textContent = bal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        balOut.style.color = bal > 0 ? '#dc2626' : '#166534';
    }

    termInput.addEventListener('input', updateSalaryBalance);
    paidInput.addEventListener('input', updateSalaryBalance);
    updateSalaryBalance();
});

// STAFF ID CARD PRINT ENGINE
function printStaffCard(staff) {
    batchPrintStaffIDs([staff]);
}

function batchPrintStaffIDs(staffList) {
    if (!staffList || staffList.length === 0) {
        alert('No staff members available to print ID cards.');
        return;
    }

    const printWin = window.open('', '_blank', 'width=900,height=750');
    if (!printWin) {
        alert('Please allow popups to print staff ID cards.');
        return;
    }

    let cardsHtml = '';

    staffList.forEach(staff => {
        const photoSrc = staff.photo ? staff.photo : '';
        const avatarHtml = photoSrc 
            ? `<img src="${photoSrc}" class="avatar">`
            : `<div class="avatar-placeholder">${staff.teacher_name[0]}</div>`;

        cardsHtml += `
            <div class="id-card">
                <div class="card-header">
                    <h2>COMRADES PREPARATORY</h2>
                    <p>STAFF IDENTITY CARD</p>
                </div>
                <div class="card-body">
                    ${avatarHtml}
                    <div class="name">${staff.teacher_name}</div>
                    <div class="role-badge">${staff.role || 'Staff Member'}</div>

                    <div class="info-row"><span>Staff Code:</span> <strong>${staff.staff_code || 'TR-' + staff.teacher_id}</strong></div>
                    <div class="info-row"><span>Class:</span> <strong>${staff.class_name || 'N/A'}</strong></div>
                    <div class="info-row"><span>Phone:</span> <strong>${staff.phone || 'N/A'}</strong></div>
                    <div class="info-row"><span>NIN:</span> <strong>${staff.national_id || 'N/A'}</strong></div>
                </div>
                <div class="card-footer">
                    Authorized Staff Identification Card - Comrades Prep School
                </div>
            </div>
        `;
    });

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Staff Identity Cards</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 15px; background: #fff; margin:0; }
                .grid-container { display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start; }
                .id-card { width: 260px; height: 390px; background: #fff; border: 2px solid #166534; border-radius: 10px; overflow: hidden; text-align: center; position: relative; box-sizing: border-box; page-break-inside: avoid; }
                .card-header { background: #166534; color: #fff; padding: 8px 6px; }
                .card-header h2 { margin: 0; font-size: 0.85rem; letter-spacing: 0.5px; }
                .card-header p { margin: 2px 0 0 0; font-size: 0.55rem; opacity: 0.9; }
                .card-body { padding: 10px 8px; }
                .avatar { width: 70px; height: 70px; border-radius: 50%; border: 2px solid #166534; object-fit: cover; margin-bottom: 4px; }
                .avatar-placeholder { width: 70px; height: 70px; border-radius: 50%; border: 2px solid #166534; background: #cbd5e1; margin: 0 auto 4px auto; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: bold; color: #166534; }
                .name { font-size: 0.95rem; font-weight: bold; color: #1e293b; margin: 2px 0 2px 0; }
                .role-badge { background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 0.7rem; display: inline-block; margin-bottom: 6px; border: 1px solid #bbf7d0; }
                .info-row { display: flex; justify-content: space-between; font-size: 0.7rem; border-bottom: 1px solid #f1f5f9; padding: 3px 0; }
                .info-row span { color: #64748b; }
                .card-footer { position: absolute; bottom: 0; width: 100%; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 4px 0; font-size: 0.55rem; color: #64748b; }
            </style>
        </head>
        <body>
            <div class="grid-container">${cardsHtml}</div>
            <script>window.onload = function() { window.print(); };<\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>
</body>
</html>