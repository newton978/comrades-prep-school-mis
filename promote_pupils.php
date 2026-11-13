<?php
require_once 'db.php';

$message = '';
$message_type = 'success';

// ========================================================
// 1. DEFINE CLASS PROGRESSION ORDER (LOWEST TO HIGHEST)
// ========================================================
// Fetch all classes ordered by class_id ASC
$classes = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map each class_id to the next class_id in sequence
$class_map = [];
$total_classes = count($classes);
for ($i = 0; $i < $total_classes; $i++) {
    $current_id = (int)$classes[$i]['class_id'];
    $next_id    = ($i + 1 < $total_classes) ? (int)$classes[$i + 1]['class_id'] : null; // null means Graduation
    $class_map[$current_id] = [
        'current_name' => $classes[$i]['class_name'],
        'next_id'      => $next_id,
        'next_name'    => ($i + 1 < $total_classes) ? $classes[$i + 1]['class_name'] : 'Graduated / Alumnus'
    ];
}

// ========================================================
// 2. POST HANDLER: EXECUTE PROMOTION
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_promotion'])) {
    $promotion_scope = $_POST['promotion_scope'] ?? 'all';
    $selected_class  = !empty($_POST['selected_class_id']) ? (int)$_POST['selected_class_id'] : null;

    try {
        $pdo->beginTransaction();

        $promoted_count  = 0;
        $graduated_count = 0;

        // Fetch target pupils
        $where_sql = " WHERE p.status = 'Active'";
        if ($promotion_scope === 'single' && $selected_class) {
            $where_sql .= " AND p.class_id = " . $selected_class;
        }

        $pupils_to_promote = $pdo->query("SELECT pupil_id, class_id FROM Pupils p $where_sql")->fetchAll(PDO::FETCH_ASSOC);

        $update_stmt   = $pdo->prepare("UPDATE Pupils SET class_id = ? WHERE pupil_id = ?");
        $graduate_stmt = $pdo->prepare("UPDATE Pupils SET status = 'Graduated' WHERE pupil_id = ?");

        foreach ($pupils_to_promote as $pupil) {
            $c_id = (int)$pupil['class_id'];
            $p_id = (int)$pupil['pupil_id'];

            if (isset($class_map[$c_id])) {
                $next_class_id = $class_map[$c_id]['next_id'];

                if ($next_class_id !== null) {
                    // Move to next class
                    $update_stmt->execute([$next_class_id, $p_id]);
                    $promoted_count++;
                } else {
                    // Top class graduate
                    $graduate_stmt->execute([$p_id]);
                    $graduated_count++;
                }
            }
        }

        $pdo->commit();
        $message = "End-of-Year Promotion Complete! <strong>$promoted_count</strong> pupils promoted to the next class level, and <strong>$graduated_count</strong> pupils marked as Graduated.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Error during promotion: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// ========================================================
// 3. FETCH CLASS DISTRIBUTION STATS FOR PREVIEW
// ========================================================
$class_stats = $pdo->query("
    SELECT c.class_id, c.class_name, COUNT(p.pupil_id) AS active_pupils
    FROM Classes c
    LEFT JOIN Pupils p ON c.class_id = p.class_id AND p.status = 'Active'
    GROUP BY c.class_id
    ORDER BY c.class_id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$total_active_pupils = $pdo->query("SELECT COUNT(*) FROM Pupils WHERE status = 'Active'")->fetchColumn();

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>" style="background: <?= $message_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $message_type === 'success' ? '#bbf7d0' : '#fecaca' ?>; color: <?= $message_type === 'success' ? '#166534' : '#991b1b' ?>; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <?= $message ?>
    </div>
<?php endif; ?>

<div class="grid">
    <!-- 1. PROMOTION CONTROLS & SELECTION FORM -->
    <div>
        <div class="card">
            <h2>End-of-Year Student Promotion Tool</h2>
            <p style="color:#64748b; font-size:0.9rem;">
                Use this utility at the conclusion of Term 3 to automatically roll over active pupils into their next academic class level according to the school progression structure.
            </p>

            <form action="promote_pupils.php" method="POST" onsubmit="return confirm('Are you sure you want to execute batch student promotion? This will update student class levels across the system.');">
                <input type="hidden" name="execute_promotion" value="1">

                <div class="form-group">
                    <label>Promotion Target Scope</label>
                    <select name="promotion_scope" id="promotion_scope" required>
                        <option value="all">Promote Entire School (All Classes)</option>
                        <option value="single">Promote Specific Class Level Only</option>
                    </select>
                </div>

                <div class="form-group" id="class_select_group" style="display: none;">
                    <label>Select Source Class to Promote</label>
                    <select name="selected_class_id" id="selected_class_id">
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>">
                                <?= htmlspecialchars($c['class_name']) ?> &rarr; <?= htmlspecialchars($class_map[$c['class_id']]['next_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:12px; border-radius:6px; margin:15px 0; font-size:0.85rem; color:#1e3a8a;">
                    <strong>ℹ️ Promotion Rule Summary:</strong>
                    <ul style="margin: 5px 0 0 18px; padding: 0;">
                        <li>Students move forward exactly one grade level (e.g., P.4 &rarr; P.5).</li>
                        <li>Students in the highest class level (<?= htmlspecialchars(end($classes)['class_name'] ?? 'Top Class') ?>) will be marked as <strong>Graduated</strong>.</li>
                        <li>Historical exam results and past fee payments remain unchanged.</li>
                    </ul>
                </div>

                <button type="submit" class="btn" style="background:#166534; width:100%; padding:10px; font-size:1rem;">
                    🚀 Execute Batch Class Promotion Now
                </button>
            </form>
        </div>
    </div>

    <!-- 2. CLASS PROGRESSION MAP & ACTIVE PUPIL COUNTS -->
    <div>
        <div class="card">
            <h2>Current Class Population & Progression Map</h2>
            <div style="margin-bottom:12px; font-size:0.9rem;">
                Total Active Students Registered: <strong><?= number_format($total_active_pupils) ?></strong>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Current Class</th>
                            <th>Active Pupils</th>
                            <th>Target Promoted Class</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($class_stats)): ?>
                            <tr><td colspan="3" style="text-align:center;">No class levels defined.</td></tr>
                        <?php else: ?>
                            <?php foreach ($class_stats as $cs): ?>
                                <?php $next_title = $class_map[$cs['class_id']]['next_name']; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cs['class_name']) ?></strong></td>
                                    <td>
                                        <span class="badge" style="background:#f1f5f9; color:#334155;">
                                            <?= number_format($cs['active_pupils']) ?> Students
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: <?= $next_title === 'Graduated / Alumnus' ? '#d97706' : '#166534' ?>;">
                                            &rarr; <?= htmlspecialchars($next_title) ?>
                                        </strong>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeSelect = document.getElementById('promotion_scope');
    const classGroup  = document.getElementById('class_select_group');

    scopeSelect.addEventListener('change', function() {
        if (this.value === 'single') {
            classGroup.style.display = 'block';
        } else {
            classGroup.style.display = 'none';
        }
    });
});
</script>
</body>
</html>