<?php
require_once 'db.php';

$message = '';

// ========================================================
// 1. POST HANDLERS (FEES SETUP, CUSTOM TUITION, PAYMENTS)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. SAVE / UPDATE CLASS STANDARD FEE STRUCTURE
    if (isset($_POST['save_fee_structure'])) {
        $class_id      = (int)$_POST['class_id'];
        $term          = trim($_POST['term']);
        $academic_year = (int)$_POST['academic_year'];
        $total_fee     = (float)$_POST['total_fee'];

        if ($class_id && !empty($term) && $total_fee >= 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Fee_Structures (class_id, term, academic_year, total_fee)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE total_fee = VALUES(total_fee)
                ");
                $stmt->execute([$class_id, $term, $academic_year, $total_fee]);
                $message = "Class standard fee structure saved successfully!";
            } catch (PDOException $e) {
                $message = "Error setting fee structure: " . $e->getMessage();
            }
        }
    }

    // B. SET CUSTOM PUPIL TUITION OVERRIDE
    elseif (isset($_POST['set_custom_fee'])) {
        $pupil_id   = (int)$_POST['pupil_id'];
        $use_custom = isset($_POST['use_custom_fee']) ? 1 : 0;
        $custom_fee = $use_custom ? (float)$_POST['custom_fee_amount'] : null;

        if ($pupil_id) {
            try {
                $stmt = $pdo->prepare("UPDATE Pupils SET custom_fee = ? WHERE pupil_id = ?");
                $stmt->execute([$custom_fee, $pupil_id]);
                $message = "Pupil tuition setting updated successfully!";
            } catch (PDOException $e) {
                $message = "Error setting custom tuition: " . $e->getMessage();
            }
        }
    }

    // C. RECORD NEW PAYMENT TRANSACTION
    elseif (isset($_POST['record_payment'])) {
        $pupil_id     = (int)$_POST['pupil_id'];
        $amount_paid  = (float)$_POST['amount_paid'];
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_mode = trim($_POST['payment_mode']);
        $reference_no = trim($_POST['reference_no']);
        $received_by  = trim($_POST['received_by']);

        if ($pupil_id && $amount_paid > 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Fee_Payments (pupil_id, amount_paid, payment_date, payment_mode, reference_no, received_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$pupil_id, $amount_paid, $payment_date, $payment_mode, $reference_no, $received_by]);
                $new_payment_id = $pdo->lastInsertId();
                $message = "Payment of UGX " . number_format($amount_paid) . " recorded! <a href='print_receipt.php?payment_id=" . $new_payment_id . "' target='_blank' style='color:#fff; text-decoration:underline;'>Print Receipt Now</a>";
            } catch (PDOException $e) {
                $message = "Error recording payment: " . $e->getMessage();
            }
        }
    }
}

// ========================================================
// 2. FETCH DATA & COMPUTE PUPIL FINANCIAL LEDGERS
// ========================================================
$classes = $pdo->query("SELECT * FROM Classes ORDER BY class_id ASC")->fetchAll(PDO::FETCH_ASSOC);

$pupils = $pdo->query("
    SELECT p.pupil_id, p.admission_no, p.first_name, p.last_name, p.class_id, p.custom_fee, c.class_name 
    FROM Pupils p 
    LEFT JOIN Classes c ON p.class_id = c.class_id 
    ORDER BY p.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ========================================================
// 3. FINANCIAL SUMMARY METRICS (WHOLE SCHOOL & PER CLASS)
// ========================================================

// A. Whole School Totals
$overall_stats = $pdo->query("
    SELECT 
        SUM(COALESCE(p.custom_fee, fs.total_fee, 0)) AS total_billed,
        SUM(COALESCE(fp_sum.total_paid, 0)) AS total_paid
    FROM Pupils p
    LEFT JOIN Fee_Structures fs ON p.class_id = fs.class_id AND fs.academic_year = YEAR(CURRENT_DATE)
    LEFT JOIN (
        SELECT pupil_id, SUM(amount_paid) AS total_paid 
        FROM Fee_Payments 
        GROUP BY pupil_id
    ) fp_sum ON p.pupil_id = fp_sum.pupil_id
")->fetch(PDO::FETCH_ASSOC);

$school_total_billed = (float)($overall_stats['total_billed'] ?? 0);
$school_total_paid   = (float)($overall_stats['total_paid'] ?? 0);
$school_total_bal    = $school_total_billed - $school_total_paid;
$collection_rate     = $school_total_billed > 0 ? ($school_total_paid / $school_total_billed) * 100 : 0;

// B. Fees Paid & Balance Per Class
$class_stats = $pdo->query("
    SELECT 
        c.class_id, c.class_name,
        COUNT(p.pupil_id) AS total_students,
        SUM(COALESCE(p.custom_fee, fs.total_fee, 0)) AS class_billed,
        SUM(COALESCE(fp_sum.total_paid, 0)) AS class_paid,
        (SUM(COALESCE(p.custom_fee, fs.total_fee, 0)) - SUM(COALESCE(fp_sum.total_paid, 0))) AS class_balance
    FROM Classes c
    LEFT JOIN Pupils p ON c.class_id = p.class_id
    LEFT JOIN Fee_Structures fs ON c.class_id = fs.class_id AND fs.academic_year = YEAR(CURRENT_DATE)
    LEFT JOIN (
        SELECT pupil_id, SUM(amount_paid) AS total_paid 
        FROM Fee_Payments 
        GROUP BY pupil_id
    ) fp_sum ON p.pupil_id = fp_sum.pupil_id
    GROUP BY c.class_id
    ORDER BY c.class_id ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Filters for Pupil Ledger
$filter_class_id = isset($_GET['filter_class']) && !empty($_GET['filter_class']) ? (int)$_GET['filter_class'] : null;
$search_query    = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filter for Payment History Search Bar
$payment_search  = isset($_GET['payment_search']) ? trim($_GET['payment_search']) : '';

$where_clauses = [];
if ($filter_class_id) $where_clauses[] = "p.class_id = " . $filter_class_id;
if ($search_query) $where_clauses[] = "(p.first_name LIKE " . $pdo->quote("%$search_query%") . " OR p.last_name LIKE " . $pdo->quote("%$search_query%") . " OR p.admission_no LIKE " . $pdo->quote("%$search_query%") . ")";

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch aggregated pupil fee statements
$sql = "
    SELECT 
        p.pupil_id, p.admission_no, p.first_name, p.last_name, p.custom_fee, c.class_name,
        COALESCE(p.custom_fee, fs.total_fee, 0) AS current_term_fee,
        COALESCE(SUM(fp.amount_paid), 0) AS total_paid,
        (COALESCE(p.custom_fee, fs.total_fee, 0) - COALESCE(SUM(fp.amount_paid), 0)) AS balance_due,
        MAX(fp.payment_date) AS last_payment_date
    FROM Pupils p
    LEFT JOIN Classes c ON p.class_id = c.class_id
    LEFT JOIN Fee_Structures fs ON p.class_id = fs.class_id AND fs.academic_year = YEAR(CURRENT_DATE)
    LEFT JOIN Fee_Payments fp ON p.pupil_id = fp.pupil_id
    $where_sql
    GROUP BY p.pupil_id
    ORDER BY balance_due DESC, p.first_name ASC
";
$ledger = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Search Query for Recent Payment Transactions Table
$payment_where = "";
if (!empty($payment_search)) {
    $payment_where = " WHERE p.first_name LIKE " . $pdo->quote("%$payment_search%") . " 
                       OR p.last_name LIKE " . $pdo->quote("%$payment_search%") . " 
                       OR p.admission_no LIKE " . $pdo->quote("%$payment_search%") . " 
                       OR fp.payment_mode LIKE " . $pdo->quote("%$payment_search%") . " 
                       OR fp.reference_no LIKE " . $pdo->quote("%$payment_search%");
}

$recent_payments = $pdo->query("
    SELECT fp.*, p.first_name, p.last_name, p.admission_no, c.class_name
    FROM Fee_Payments fp
    JOIN Pupils p ON fp.pupil_id = p.pupil_id
    LEFT JOIN Classes c ON p.class_id = c.class_id
    $payment_where
    ORDER BY fp.payment_id DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert"><?= $message ?></div>
<?php endif; ?>

<!-- ======================================================== -->
<!-- TOP FINANCIAL SUMMARY: WHOLE SCHOOL & CLASS BREAKDOWN   -->
<!-- ======================================================== -->
<div class="card" style="margin-bottom: 20px; background: #ffffff;">
    <h2 style="margin-top:0; color:#1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
         Financial Executive Summary (Current Term)
    </h2>

    <!-- WHOLE SCHOOL SUMMARY CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px; text-align: center;">
            <span style="font-size: 0.8rem; font-weight: bold; color: #1e40af; text-transform: uppercase;">Total Billed (Whole School)</span>
            <div style="font-size: 1.4rem; font-weight: bold; color: #1e3a8a; margin-top: 5px;">
                UGX <?= number_format($school_total_billed) ?>
            </div>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; text-align: center;">
            <span style="font-size: 0.8rem; font-weight: bold; color: #166534; text-transform: uppercase;">Total Fees Paid (Collected)</span>
            <div style="font-size: 1.4rem; font-weight: bold; color: #15803d; margin-top: 5px;">
                UGX <?= number_format($school_total_paid) ?>
            </div>
            <small style="color: #166534; font-weight: bold;"><?= number_format($collection_rate, 1) ?>% Collected</small>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; text-align: center;">
            <span style="font-size: 0.8rem; font-weight: bold; color: #991b1b; text-transform: uppercase;">Total Outstanding Balance</span>
            <div style="font-size: 1.4rem; font-weight: bold; color: #dc2626; margin-top: 5px;">
                UGX <?= number_format($school_total_bal) ?>
            </div>
        </div>
    </div>

    <!-- FEES PAID & BALANCE BREAKDOWN PER CLASS -->
    <h3 style="font-size: 1rem; color: #1e3a8a; margin-bottom: 10px;"> Class-by-Class Fees & Balance Summary</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f1f5f9; color: #1e3a8a;">
                    <th>Class Level</th>
                    <th>Students</th>
                    <th>Total Billed (UGX)</th>
                    <th>Total Paid (UGX)</th>
                    <th>Outstanding Balance (UGX)</th>
                    <th>Collection Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($class_stats)): ?>
                    <tr><td colspan="6" style="text-align: center;">No class statistics recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($class_stats as $cs): ?>
                        <?php 
                            $c_billed  = (float)$cs['class_billed'];
                            $c_paid    = (float)$cs['class_paid'];
                            $c_bal     = (float)$cs['class_balance'];
                            $c_pct     = $c_billed > 0 ? ($c_paid / $c_billed) * 100 : 0;
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cs['class_name']) ?></strong></td>
                            <td><?= $cs['total_students'] ?> Pupils</td>
                            <td><?= number_format($c_billed) ?></td>
                            <td><strong style="color: #166534;"><?= number_format($c_paid) ?></strong></td>
                            <td><strong style="color: <?= $c_bal > 0 ? '#dc2626' : '#166534' ?>;"><?= number_format($c_bal) ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                                        <div style="width: <?= min(100, $c_pct) ?>%; background: <?= $c_pct >= 80 ? '#166534' : ($c_pct >= 50 ? '#d97706' : '#dc2626') ?>; height: 100%;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: bold; width: 40px; text-align: right;"><?= number_format($c_pct, 0) ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="grid">
    <div>
        <!-- 1. RECORD PAYMENT FORM WITH LIVE PUPIL SEARCH -->
        <div class="card">
            <h2>Record Fee Payment</h2>
            <form action="fees.php" method="POST">
                <input type="hidden" name="record_payment" value="1">
                
                <div class="form-group">
                    <label>Find & Select Pupil</label>
                    <input type="text" id="payment_pupil_search" placeholder=" Type student name or admission no..." style="margin-bottom: 8px;">
                    <select name="pupil_id" id="payment_pupil_select" required size="5" style="height: auto;">
                        <option value="" disabled selected>-- Select Student Below --</option>
                        <?php foreach ($pupils as $p): ?>
                            <option value="<?= $p['pupil_id'] ?>">
                                <?= htmlspecialchars($p['admission_no'] . ' - ' . $p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['class_name'] ?? 'No Class') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Amount Paid (UGX)</label>
                    <input type="number" step="500" min="1000" name="amount_paid" placeholder="e.g. 250000" required>
                </div>

                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Payment Mode</label>
                    <select name="payment_mode" required>
                        <option value="Cash">Cash</option>
                        <option value="MTN MoMo">MTN Mobile Money</option>
                        <option value="Airtel Money">Airtel Money</option>
                        <option value="Bank Deposit">Bank Deposit</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Reference / Bank Slip No.</label>
                    <input type="text" name="reference_no" placeholder="e.g. TXN94820148">
                </div>

                <div class="form-group">
                    <label>Received By</label>
                    <input type="text" name="received_by" value="Bursar Office" required>
                </div>

                <button type="submit" class="btn">Record Payment & Generate Receipt</button>
            </form>
        </div>

        <!-- 2. CUSTOM INDIVIDUAL PUPIL TUITION OVERRIDE -->
        <div class="card">
            <h2>Individual Custom Tuition Setting</h2>
            <form action="fees.php" method="POST">
                <input type="hidden" name="set_custom_fee" value="1">

                <div class="form-group">
                    <label>Select Pupil</label>
                    <select name="pupil_id" id="custom_pupil_select" required>
                        <option value="">-- Select Pupil --</option>
                        <?php foreach ($pupils as $p): ?>
                            <option value="<?= $p['pupil_id'] ?>" data-customfee="<?= $p['custom_fee'] !== null ? $p['custom_fee'] : '' ?>">
                                <?= htmlspecialchars($p['admission_no'] . ' - ' . $p['first_name'] . ' ' . $p['last_name']) ?> 
                                <?= $p['custom_fee'] !== null ? '(Custom: UGX '.number_format($p['custom_fee']).')' : '(Standard Class Fee)' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="cursor:pointer;">
                        <input type="checkbox" name="use_custom_fee" id="use_custom_fee_chk"> Enable Custom Tuition Override
                    </label>
                </div>

                <div class="form-group" id="custom_fee_box" style="display:none;">
                    <label>Custom Billed Fee Amount (UGX)</label>
                    <input type="number" step="1000" name="custom_fee_amount" id="custom_fee_amount" placeholder="e.g. 150000">
                    <small style="color:#64748b;">(Use for bursary, staff child, or special fee discount)</small>
                </div>

                <button type="submit" class="btn">Save Pupil Fee Setting</button>
            </form>
        </div>

        <!-- 3. SETUP STANDARD CLASS FEE STRUCTURE -->
        <div class="card">
            <h2>Set Standard Class Fee Structure</h2>
            <form action="fees.php" method="POST">
                <input type="hidden" name="save_fee_structure" value="1">

                <div class="form-group">
                    <label>Class Level</label>
                    <select name="class_id" required>
                        <option value="">-- Choose Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Term</label>
                    <select name="term" required>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="number" name="academic_year" value="<?= date('Y') ?>" required>
                </div>

                <div class="form-group">
                    <label>Standard Class Billed Fee (UGX)</label>
                    <input type="number" step="1000" name="total_fee" placeholder="e.g. 450000" required>
                </div>

                <button type="submit" class="btn">Save Standard Class Fee</button>
            </form>
        </div>
    </div>

    <!-- 4. FINANCIAL LEDGER & RECENT PAYMENTS WITH SEARCH -->
    <div>
        <!-- PUPIL FEE BALANCES LEDGER -->
        <div class="card">
            <h2>Pupil Fee Balances Ledger</h2>

            <form action="fees.php" method="GET" style="margin-bottom: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group" style="margin:0;">
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
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search student name or adm no..." onchange="this.form.submit()">
                </div>
            </form>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Adm No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Fee Rate</th>
                            <th>Billed (UGX)</th>
                            <th>Paid (UGX)</th>
                            <th>Balance (UGX)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ledger)): ?>
                            <tr><td colspan="8" style="text-align: center;">No pupil balance records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ledger as $l): ?>
                                <?php 
                                    $bal = (float)$l['balance_due'];
                                    $status_badge = $bal <= 0 ? 'badge-success' : ($l['total_paid'] > 0 ? 'badge-warning' : 'badge-danger');
                                    $status_text  = $bal <= 0 ? 'Cleared' : ($l['total_paid'] > 0 ? 'Partial' : 'Unpaid');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($l['admission_no']) ?></td>
                                    <td><strong><?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($l['class_name'] ?? 'Unassigned') ?></td>
                                    <td><small><?= $l['custom_fee'] !== null ? '<span style="color:#d97706;">Custom</span>' : 'Standard' ?></small></td>
                                    <td><?= number_format($l['current_term_fee']) ?></td>
                                    <td><?= number_format($l['total_paid']) ?></td>
                                    <td><strong><?= number_format($bal) ?></strong></td>
                                    <td><span class="badge <?= $status_badge ?>"><?= $status_text ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT PAYMENTS TRANSACTIONS WITH SEARCH BAR -->
        <div class="card">
            <h2>Received Payment Transactions</h2>

            <form action="fees.php" method="GET" style="margin-bottom: 15px;">
                <div class="form-group" style="margin:0;">
                    <input type="text" name="payment_search" value="<?= htmlspecialchars($payment_search) ?>" placeholder=" Search payment by pupil name, adm no, ref, or mode..." onchange="this.form.submit()">
                </div>
            </form>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Adm No</th>
                            <th>Pupil Name</th>
                            <th>Amount (UGX)</th>
                            <th>Mode</th>
                            <th>Ref No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_payments)): ?>
                            <tr><td colspan="7" style="text-align: center;">No payment transactions found matching search.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_payments as $rp): ?>
                                <tr>
                                    <td><small><?= date('d M Y', strtotime($rp['payment_date'])) ?></small></td>
                                    <td><?= htmlspecialchars($rp['admission_no']) ?></td>
                                    <td><strong><?= htmlspecialchars($rp['first_name'] . ' ' . $rp['last_name']) ?></strong></td>
                                    <td><strong><?= number_format($rp['amount_paid']) ?></strong></td>
                                    <td><small><?= htmlspecialchars($rp['payment_mode']) ?></small></td>
                                    <td><small><?= htmlspecialchars($rp['reference_no'] ?: '-') ?></small></td>
                                    <td>
                                        <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem; background:#166534;" 
                                                onclick="printReceipt(<?= $rp['payment_id'] ?>)">
                                            Print Receipt
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. LIVE SEARCH FILTER FOR RECORD PAYMENT PUPIL DROPDOWN
    const pupilSearchInput = document.getElementById('payment_pupil_search');
    const pupilSelect      = document.getElementById('payment_pupil_select');

    if (pupilSearchInput && pupilSelect) {
        pupilSearchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const options = pupilSelect.options;

            for (let i = 0; i < options.length; i++) {
                if (options[i].disabled) continue;
                const text = options[i].text.toLowerCase();
                options[i].style.display = text.includes(filter) ? '' : 'none';
            }
        });
    }

    // 2. CUSTOM TUITION OVERRIDE TOGGLE
    const customSelect = document.getElementById('custom_pupil_select');
    const chk          = document.getElementById('use_custom_fee_chk');
    const box          = document.getElementById('custom_fee_box');
    const input        = document.getElementById('custom_fee_amount');

    if (customSelect) {
        customSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const fee      = selected.getAttribute('data-customfee');

            if (fee !== '' && fee !== null) {
                chk.checked = true;
                box.style.display = 'block';
                input.value = fee;
            } else {
                chk.checked = false;
                box.style.display = 'none';
                input.value = '';
            }
        });

        chk.addEventListener('change', function() {
            box.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) input.value = '';
        });
    }
});

function printReceipt(paymentId) {
    window.open('print_receipt.php?payment_id=' + paymentId, '_blank', 'width=700,height=600');
}
</script>
</body>
</html>