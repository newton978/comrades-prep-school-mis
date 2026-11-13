<?php
require_once 'db.php';

$message = '';
$message_type = 'success';
$imported_count = 0;
$error_count = 0;
$log_messages = [];

// ========================================================
// 1. EXCEL / CSV BATCH UPLOAD HANDLER
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "File upload failed. Please try again.";
        $message_type = "danger";
    } else {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, ['csv', 'txt'])) {
            $message = "Invalid file format! Please upload a valid CSV file (.csv) exported from Excel.";
            $message_type = "danger";
        } else {
            $handle = fopen($file['tmp_name'], 'r');

            if ($handle !== false) {
                // Fetch existing classes for fast matching: [class_name_lowercase => class_id]
                $classes_raw = $pdo->query("SELECT class_id, class_name FROM Classes")->fetchAll(PDO::FETCH_ASSOC);
                $class_map = [];
                foreach ($classes_raw as $c) {
                    $class_map[strtolower(trim($c['class_name']))] = (int)$c['class_id'];
                }

                // Prepared statements
                $check_stmt = $pdo->prepare("SELECT pupil_id FROM Pupils WHERE admission_no = ?");
                
                $insert_pupil = $pdo->prepare("
                    INSERT INTO Pupils (admission_no, first_name, last_name, gender, class_id, date_of_birth, parent_name, parent_phone, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $insert_class = $pdo->prepare("INSERT INTO Classes (class_name) VALUES (?)");

                $row_number = 0;
                $pdo->beginTransaction();

                try {
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        $row_number++;

                        // Skip header row if present
                        if ($row_number === 1 && (strtolower(trim($data[0])) === 'admission_no' || strtolower(trim($data[1])) === 'first_name')) {
                            continue;
                        }

                        // Required Columns mapping:
                        // 0: Admission No, 1: First Name, 2: Last Name, 3: Gender, 4: Class Name, 5: DOB, 6: Parent Name, 7: Parent Phone, 8: Status
                        $adm_no       = trim($data[0] ?? '');
                        $first_name   = trim($data[1] ?? '');
                        $last_name    = trim($data[2] ?? '');
                        $gender       = trim($data[3] ?? 'Male');
                        $class_name   = trim($data[4] ?? '');
                        $dob          = !empty(trim($data[5] ?? '')) ? trim($data[5]) : NULL;
                        $parent_name  = trim($data[6] ?? '');
                        $parent_phone = trim($data[7] ?? '');
                        $status       = !empty(trim($data[8] ?? '')) ? trim($data[8]) : 'Active';

                        if (empty($adm_no) || empty($first_name) || empty($last_name)) {
                            $error_count++;
                            $log_messages[] = "Row #{$row_number}: Skipped due to missing required fields (Admission No, First Name, or Last Name).";
                            continue;
                        }

                        // Check duplicate admission number
                        $check_stmt->execute([$adm_no]);
                        if ($check_stmt->fetch()) {
                            $error_count++;
                            $log_messages[] = "Row #{$row_number}: Admission No '{$adm_no}' already exists in database. Skipped.";
                            continue;
                        }

                        // Match or Create Class
                        $class_id = null;
                        if (!empty($class_name)) {
                            $lower_c_name = strtolower($class_name);
                            if (isset($class_map[$lower_c_name])) {
                                $class_id = $class_map[$lower_c_name];
                            } else {
                                // Create new class automatically
                                $insert_class->execute([$class_name]);
                                $new_c_id = $pdo->lastInsertId();
                                $class_map[$lower_c_name] = $new_c_id;
                                $class_id = $new_c_id;
                            }
                        }

                        // Insert student record
                        $insert_pupil->execute([
                            $adm_no,
                            $first_name,
                            $last_name,
                            $gender,
                            $class_id,
                            $dob,
                            $parent_name,
                            $parent_phone,
                            $status
                        ]);

                        $imported_count++;
                    }

                    $pdo->commit();
                    fclose($handle);

                    $message = "Batch import complete! <strong>{$imported_count} pupils successfully imported.</strong>";
                    if ($error_count > 0) {
                        $message .= " ({$error_count} rows were skipped or contained errors).";
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    fclose($handle);
                    $message = "Error processing file: " . $e->getMessage();
                    $message_type = "danger";
                }
            }
        }
    }
}

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>" style="background: <?= $message_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $message_type === 'success' ? '#bbf7d0' : '#fecaca' ?>; color: <?= $message_type === 'success' ? '#166534' : '#991b1b' ?>; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <?= $message ?>
    </div>
<?php endif; ?>

<div class="grid" style="grid-template-columns: 420px 1fr;">
    <!-- 1. FILE UPLOAD CARD -->
    <div class="card">
        <h2>📥 Import Student Batch (Excel / CSV)</h2>
        <form action="import_pupils.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Select CSV File (from Excel)</label>
                <input type="file" name="excel_file" accept=".csv" required style="padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
            </div>

            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem; color: #1e3a8a;">
                <strong>💡 Instructions for Excel:</strong><br>
                1. Prepare your student list in Microsoft Excel.<br>
                2. Click <strong>File &gt; Save As</strong>.<br>
                3. Select <strong>CSV (Comma delimited) (*.csv)</strong> as the file type.<br>
                4. Upload the saved <code>.csv</code> file here.
            </div>

            <button type="submit" class="btn" style="background: #166534; font-size: 1rem; padding: 10px;">
                🚀 Upload & Import Students
            </button>
        </form>
    </div>

    <!-- 2. CSV TEMPLATE GUIDELINES & LOGS -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h2 style="margin:0;">Excel / CSV Column Structure Guide</h2>
            <button type="button" class="btn" style="background: #1e3a8a; padding: 6px 12px; font-size: 0.8rem; width: auto;" onclick="downloadSampleCSV()">
                📥 Download Sample Excel Template
            </button>
        </div>

        <p style="font-size: 0.85rem; color: #64748b;">Ensure your Excel spreadsheet columns match this exact order (with or without header row):</p>

        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table style="width:100%; border-collapse: collapse; font-size: 0.8rem;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th>Col A</th>
                        <th>Col B</th>
                        <th>Col C</th>
                        <th>Col D</th>
                        <th>Col E</th>
                        <th>Col F</th>
                        <th>Col G</th>
                        <th>Col H</th>
                        <th>Col I</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>admission_no</strong></td>
                        <td><strong>first_name</strong></td>
                        <td><strong>last_name</strong></td>
                        <td><strong>gender</strong></td>
                        <td><strong>class_name</strong></td>
                        <td><strong>date_of_birth</strong></td>
                        <td><strong>parent_name</strong></td>
                        <td><strong>parent_phone</strong></td>
                        <td><strong>status</strong></td>
                    </tr>
                    <tr style="color: #64748b;">
                        <td>CPS/2026/001</td>
                        <td>John</td>
                        <td>Kato</td>
                        <td>Male</td>
                        <td>Primary 1</td>
                        <td>2018-05-12</td>
                        <td>David Kato</td>
                        <td>0771234567</td>
                        <td>Active</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($log_messages)): ?>
            <h3 style="font-size: 0.95rem; color: #991b1b; margin-bottom: 8px;">⚠️ Import Logs & Notifications</h3>
            <div style="background: #fff1f2; border: 1px solid #fecdd3; padding: 10px; border-radius: 6px; max-height: 200px; overflow-y: auto; font-size: 0.8rem; color: #9f1239;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($log_messages as $log): ?>
                        <li><?= htmlspecialchars($log) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// DOWNLOAD SAMPLE CSV FILE FOR EXCEL EDITING
function downloadSampleCSV() {
    const csvContent = "data:text/csv;charset=utf-8," 
        + "admission_no,first_name,last_name,gender,class_name,date_of_birth,parent_name,parent_phone,status\n"
        + "CPS/2026/001,John,Kato,Male,Primary 1,2018-05-12,David Kato,0771234567,Active\n"
        + "CPS/2026/002,Mary,Nakamya,Female,Primary 2,2017-09-24,Sarah Nakamya,0759876543,Active\n";

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "pupils_import_sample_comrades.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</body>
</html>