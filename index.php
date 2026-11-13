<?php
require_once 'db.php';

$message = '';
$edit_pupil = null;

// Ensure uploads directory exists
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ========================================================
// 1. POST HANDLER (CREATE & UPDATE PUPIL WITH PHOTO)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action            = $_POST['action'] ?? '';
    $admission_no      = trim($_POST['admission_no']);
    $first_name        = trim($_POST['first_name']);
    $last_name         = trim($_POST['last_name']);
    $gender            = $_POST['gender'];
    $dob               = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $class_id          = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
    $custom_fee        = !empty($_POST['tuition_fee']) ? (float)$_POST['tuition_fee'] : null;
    $parent_name       = trim($_POST['parent_name']);
    $parent_phone      = trim($_POST['parent_phone']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $medical_notes     = trim($_POST['medical_notes']);
    $religion          = trim($_POST['religion']);
    $address           = trim($_POST['address']);

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
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO Pupils (admission_no, first_name, last_name, gender, dob, class_id, custom_fee, photo, emergency_contact, medical_notes, religion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$admission_no, $first_name, $last_name, $gender, $dob, $class_id, $custom_fee, $photo_path, $emergency_contact, $medical_notes, $religion]);
            $pupil_id = $pdo->lastInsertId();

            if (!empty($parent_name)) {
                $p_stmt = $pdo->prepare("INSERT INTO Parents (parent_name, phone, address) VALUES (?, ?, ?)");
                $p_stmt->execute([$parent_name, $parent_phone, $address]);
                $parent_id = $pdo->lastInsertId();

                $link_stmt = $pdo->prepare("INSERT INTO Pupil_Parent (pupil_id, parent_id) VALUES (?, ?)");
                $link_stmt->execute([$pupil_id, $parent_id]);
            }

            $pdo->commit();
            $message = "Pupil and parent registered successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error saving record: " . $e->getMessage();
        }
    } elseif ($action === 'update') {
        $pupil_id = (int)$_POST['pupil_id'];
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE Pupils 
                SET admission_no=?, first_name=?, last_name=?, gender=?, dob=?, class_id=?, custom_fee=?, photo=?, emergency_contact=?, medical_notes=?, religion=? 
                WHERE pupil_id=?
            ");
            $stmt->execute([$admission_no, $first_name, $last_name, $gender, $dob, $class_id, $custom_fee, $photo_path, $emergency_contact, $medical_notes, $religion, $pupil_id]);

            if (!empty($parent_name)) {
                $check_p = $pdo->prepare("SELECT parent_id FROM Pupil_Parent WHERE pupil_id = ?");
                $check_p->execute([$pupil_id]);
                $existing_parent_id = $check_p->fetchColumn();

                if ($existing_parent_id) {
                    $p_stmt = $pdo->prepare("UPDATE Parents SET parent_name=?, phone=?, address=? WHERE parent_id=?");
                    $p_stmt->execute([$parent_name, $parent_phone, $address, $existing_parent_id]);
                } else {
                    $p_stmt = $pdo->prepare("INSERT INTO Parents (parent_name, phone, address) VALUES (?, ?, ?)");
                    $p_stmt->execute([$parent_name, $parent_phone, $address]);
                    $parent_id = $pdo->lastInsertId();

                    $link_stmt = $pdo->prepare("INSERT INTO Pupil_Parent (pupil_id, parent_id) VALUES (?, ?)");
                    $link_stmt->execute([$pupil_id, $parent_id]);
                }
            }

            $pdo->commit();
            $message = "Pupil record updated successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error updating record: " . $e->getMessage();
        }
    }
}

// ========================================================
// 2. DELETE & EDIT HANDLERS
// ========================================================
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Pupils WHERE pupil_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: index.php?msg=deleted");
    exit();
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT p.*, par.parent_name, par.phone AS parent_phone, par.address 
        FROM Pupils p
        LEFT JOIN Pupil_Parent pp ON p.pupil_id = pp.pupil_id
        LEFT JOIN Parents par ON pp.parent_id = par.parent_id
        WHERE p.pupil_id = ?
    ");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_pupil = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ========================================================
// 3. FETCH DATA & FILTER DIRECTORY BY CLASS
// ========================================================
$classes = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);

$filter_class_id = isset($_GET['filter_class']) && !empty($_GET['filter_class']) ? (int)$_GET['filter_class'] : null;
$where_class_sql = $filter_class_id ? " WHERE p.class_id = " . $filter_class_id : "";

$pupils = $pdo->query("
    SELECT p.*, c.class_name, par.parent_name, par.phone AS parent_phone 
    FROM Pupils p 
    LEFT JOIN Classes c ON p.class_id = c.class_id 
    LEFT JOIN Pupil_Parent pp ON p.pupil_id = pp.pupil_id
    LEFT JOIN Parents par ON pp.parent_id = par.parent_id
    $where_class_sql
    ORDER BY p.pupil_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<?php if ($message || isset($_GET['msg'])): ?>
    <div class="alert"><?= htmlspecialchars($message ?: ($_GET['msg'] === 'deleted' ? 'Record deleted successfully!' : '')) ?></div>
<?php endif; ?>

<div class="grid">
    <!-- REGISTRATION / EDIT FORM -->
    <div class="card">
        <h2><?= $edit_pupil ? 'Edit Pupil Bio-Data' : 'Register New Pupil' ?></h2>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_pupil ? 'update' : 'create' ?>">
            <?php if ($edit_pupil): ?>
                <input type="hidden" name="pupil_id" value="<?= $edit_pupil['pupil_id'] ?>">
                <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($edit_pupil['photo'] ?? '') ?>">
            <?php endif; ?>

            <div style="font-weight:bold; color:var(--primary); margin-bottom:8px;">Basic Pupil Information</div>
            <div class="form-group"><label>Admission No</label><input type="text" name="admission_no" value="<?= htmlspecialchars($edit_pupil['admission_no'] ?? '') ?>" required></div>
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?= htmlspecialchars($edit_pupil['first_name'] ?? '') ?>" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?= htmlspecialchars($edit_pupil['last_name'] ?? '') ?>" required></div>
            
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="M" <?= ($edit_pupil['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= ($edit_pupil['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date of Birth (DOB)</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($edit_pupil['dob'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Class Level</label>
                <select name="class_id">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($edit_pupil['class_id'] ?? '') == $c['class_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Passport Photo</label>
                <input type="file" name="photo" accept="image/*">
                <?php if ($edit_pupil && !empty($edit_pupil['photo'])): ?>
                    <img src="<?= htmlspecialchars($edit_pupil['photo']) ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-top:5px;">
                <?php endif; ?>
            </div>

            <div style="margin-top:15px; font-weight:bold; color:var(--primary);">Parent & Guardian Info</div>
            <div class="form-group"><label>Parent/Guardian Name</label><input type="text" name="parent_name" value="<?= htmlspecialchars($edit_pupil['parent_name'] ?? '') ?>"></div>
            <div class="form-group"><label>Parent Phone</label><input type="text" name="parent_phone" value="<?= htmlspecialchars($edit_pupil['parent_phone'] ?? '') ?>"></div>
            <div class="form-group"><label>Emergency Contact</label><input type="text" name="emergency_contact" value="<?= htmlspecialchars($edit_pupil['emergency_contact'] ?? '') ?>" placeholder="Alternative phone number"></div>
            <div class="form-group"><label>Address / Residence</label><input type="text" name="address" value="<?= htmlspecialchars($edit_pupil['address'] ?? '') ?>"></div>

            <div style="margin-top:15px; font-weight:bold; color:var(--primary);">Medical & Background Details</div>
            <div class="form-group"><label>Religion</label><input type="text" name="religion" value="<?= htmlspecialchars($edit_pupil['religion'] ?? '') ?>" placeholder="e.g. Christian, Muslim"></div>
            <div class="form-group"><label>Medical Conditions / Allergies</label><textarea name="medical_notes" rows="2" placeholder="e.g. Asthmatic, Peanut allergy"><?= htmlspecialchars($edit_pupil['medical_notes'] ?? '') ?></textarea></div>

            <button type="submit" class="btn"><?= $edit_pupil ? 'Update Pupil Record' : 'Save Pupil Record' ?></button>
            <?php if ($edit_pupil): ?>
                <p style="text-align:center; margin-top:8px;"><a href="index.php" style="color:var(--text);">Cancel Editing</a></p>
            <?php endif; ?>
        </form>
    </div>

    <!-- DIRECTORY & BATCH STUDENT ID GENERATOR -->
    <div class="card">
        <h2>Registered Pupils Directory</h2>

        <!-- CLASS FILTER & BULK PRINT CONTROLS -->
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:15px; flex-wrap:wrap; background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #e2e8f0;">
            <form action="index.php" method="GET" style="display:flex; gap:10px; align-items:center; flex:1; margin:0;">
                <label style="margin:0; font-weight:bold; font-size:0.85rem;">Filter Class:</label>
                <select name="filter_class" onchange="this.form.submit()" style="padding:6px; margin:0;">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['class_id'] ?>" <?= $filter_class_id == $c['class_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <button type="button" class="btn" style="background:#166534; padding:7px 12px; font-size:0.85rem;" 
                    onclick='batchPrintIDs(<?= json_encode($pupils, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                 Batch Print Class IDs (<?= count($pupils) ?>)
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Adm No</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>DOB</th>
                        <th>Parent Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pupils)): ?>
                        <tr><td colspan="7" style="text-align:center;">No registered pupils found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pupils as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['photo'])): ?>
                                        <img src="<?= htmlspecialchars($p['photo']) ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:40px; height:40px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.8rem; color:#475569;">
                                            <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['admission_no']) ?></td>
                                <td><strong><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></strong></td>
                                <td><?= htmlspecialchars($p['class_name'] ?? 'Unassigned') ?></td>
                                <td><small><?= $p['dob'] ? date('d M Y', strtotime($p['dob'])) : '-' ?></small></td>
                                <td><?= htmlspecialchars($p['parent_name'] ?? 'N/A') ?><br><small><?= htmlspecialchars($p['parent_phone'] ?? '') ?></small></td>
                                <td class="actions">
                                    <button type="button" class="btn" style="padding: 3px 6px; font-size: 0.75rem; background:#166534; margin-bottom:2px;" 
                                            onclick='printIDCard(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        Print ID
                                    </button>
                                    <a href="index.php?edit=<?= $p['pupil_id'] ?>" class="edit">Edit</a>
                                    <a href="index.php?delete=<?= $p['pupil_id'] ?>" class="delete" onclick="return confirm('Delete pupil record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT BATCH & INDIVIDUAL ID CARD PRINT ENGINE -->
<script>
// SINGLE STUDENT ID PRINT
function printIDCard(pupil) {
    batchPrintIDs([pupil]);
}

// BATCH CLASS ID CARDS PRINT ENGINE
function batchPrintIDs(pupilsList) {
    if (!pupilsList || pupilsList.length === 0) {
        alert('No pupils selected or available to print ID cards.');
        return;
    }

    const printWin = window.open('', '_blank', 'width=900,height=750');
    if (!printWin) {
        alert('Please allow popups to print student ID cards.');
        return;
    }

    let cardsHtml = '';

    pupilsList.forEach(pupil => {
        const photoSrc = pupil.photo ? pupil.photo : '';
        const avatarHtml = photoSrc 
            ? `<img src="${photoSrc}" class="avatar">`
            : `<div class="avatar-placeholder">${pupil.first_name[0]}${pupil.last_name[0]}</div>`;

        cardsHtml += `
            <div class="id-card">
                <div class="card-header">
                    <h2>COMRADES PREPARATORY</h2>
                    <p>STUDENT IDENTITY CARD</p>
                </div>
                <div class="card-body">
                    ${avatarHtml}
                    <div class="name">${pupil.first_name} ${pupil.last_name}</div>
                    <div class="class-badge">${pupil.class_name || 'Primary Student'}</div>

                    <div class="info-row"><span>Admission No:</span> <strong>${pupil.admission_no}</strong></div>
                    <div class="info-row"><span>Gender:</span> <strong>${pupil.gender === 'M' ? 'Male' : 'Female'}</strong></div>
                    <div class="info-row"><span>DOB:</span> <strong>${pupil.dob || 'N/A'}</strong></div>
                    <div class="info-row"><span>Emergency Tel:</span> <strong>${pupil.emergency_contact || pupil.parent_phone || 'N/A'}</strong></div>
                </div>
                <div class="card-footer">
                    If found, please return to Comrades Prep School - Kitebi
                </div>
            </div>
        `;
    });

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Batch Student ID Cards (${pupilsList.length} Students)</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 15px; background: #fff; margin:0; }
                .grid-container { display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start; }
                .id-card { width: 260px; height: 390px; background: #fff; border: 2px solid #1e3a8a; border-radius: 10px; overflow: hidden; text-align: center; position: relative; box-sizing: border-box; page-break-inside: avoid; }
                .card-header { background: #1e3a8a; color: #fff; padding: 8px 6px; }
                .card-header h2 { margin: 0; font-size: 0.85rem; letter-spacing: 0.5px; }
                .card-header p { margin: 2px 0 0 0; font-size: 0.55rem; opacity: 0.9; }
                .card-body { padding: 10px 8px; }
                .avatar { width: 70px; height: 70px; border-radius: 50%; border: 2px solid #1e3a8a; object-fit: cover; margin-bottom: 4px; }
                .avatar-placeholder { width: 70px; height: 70px; border-radius: 50%; border: 2px solid #1e3a8a; background: #cbd5e1; margin: 0 auto 4px auto; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: bold; color: #1e3a8a; }
                .name { font-size: 0.95rem; font-weight: bold; color: #1e293b; margin: 2px 0 2px 0; }
                .class-badge { background: #eff6ff; color: #1e3a8a; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 0.7rem; display: inline-block; margin-bottom: 6px; }
                .info-row { display: flex; justify-content: space-between; font-size: 0.7rem; border-bottom: 1px solid #f1f5f9; padding: 3px 0; }
                .info-row span { color: #64748b; }
                .card-footer { position: absolute; bottom: 0; width: 100%; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 4px 0; font-size: 0.55rem; color: #64748b; }
                @media print {
                    body { padding: 0; background: #fff; }
                    .grid-container { gap: 10px; }
                }
            </style>
        </head>
        <body>
            <div class="grid-container">
                ${cardsHtml}
            </div>
            <script>
                window.onload = function() { window.print(); };
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>
</body>
</html>