<?php
require_once 'db.php';

$message = '';

// ========================================================
// 1. POST HANDLER: PROCESS NEW PAYSLIP TRANSACTION
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payroll'])) {
    $teacher_id      = (int)$_POST['teacher_id'];
    $payment_type    = $_POST['payment_type'] ?? 'Monthly';
    $pay_period      = trim($_POST['pay_period']);
    $base_salary     = (float)$_POST['base_salary'];
    $allowances      = (float)$_POST['allowances'];
    $allowance_notes = trim($_POST['allowance_notes']);
    $deductions      = (float)$_POST['deductions'];
    $deduction_notes = trim($_POST['deduction_notes']);
    $payment_date    = $_POST['payment_date'] ?? date('Y-m-d');
    $payment_mode    = trim($_POST['payment_mode']);

    $net_pay = ($base_salary + $allowances) - $deductions;

    if ($teacher_id && $base_salary >= 0 && !empty($pay_period)) {
        try {
            $pdo->beginTransaction();

            // Insert Payslip Record
            $stmt = $pdo->prepare("
                INSERT INTO Payslips (teacher_id, pay_period, payment_type, base_salary, allowances, allowance_notes, deductions, deduction_notes, net_pay, payment_date, payment_mode)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$teacher_id, $pay_period, $payment_type, $base_salary, $allowances, $allowance_notes, $deductions, $deduction_notes, $net_pay, $payment_date, $payment_mode]);
            $new_payslip_id = $pdo->lastInsertId();

            // Update Teacher's total amount paid in Teachers table
            $up_stmt = $pdo->prepare("UPDATE Teachers SET amount_paid = amount_paid + ? WHERE teacher_id = ?");
            $up_stmt->execute([$net_pay, $teacher_id]);

            $pdo->commit();
            $message = "Payroll processed successfully! Net Pay: UGX " . number_format($net_pay) . " <button type='button' class='btn' style='padding:2px 8px; font-size:0.8rem; background:#166534;' onclick='printPayslip(" . $new_payslip_id . ")'>Print Payslip Now</button>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error processing payroll: " . $e->getMessage();
        }
    }
}

// ========================================================
// 2. FETCH DATA & RECENT PAYSLIPS
// ========================================================
$teachers = $pdo->query("
    SELECT t.*, c.class_name 
    FROM Teachers t 
    LEFT JOIN Classes c ON t.assigned_class_id = c.class_id 
    ORDER BY t.teacher_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Search & Filter History
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_sql = "";
if (!empty($search_query)) {
    $where_sql = " WHERE t.teacher_name LIKE " . $pdo->quote("%$search_query%") . " 
                   OR t.staff_code LIKE " . $pdo->quote("%$search_query%") . " 
                   OR ps.pay_period LIKE " . $pdo->quote("%$search_query%");
}

$payslips = $pdo->query("
    SELECT ps.*, t.teacher_name, t.staff_code, t.role, t.national_id, c.class_name
    FROM Payslips ps
    JOIN Teachers t ON ps.teacher_id = t.teacher_id
    LEFT JOIN Classes c ON t.assigned_class_id = c.class_id
    $where_sql
    ORDER BY ps.payslip_id DESC
    LIMIT 25
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert"><?= $message ?></div>
<?php endif; ?>

<div class="grid">
    <!-- 1. PAYROLL ENTRY FORM -->
    <div class="card">
        <h2>Process Teacher Payroll</h2>
        <form action="payslip.php" method="POST">
            <input type="hidden" name="process_payroll" value="1">

            <div class="form-group">
                <label>Select Teacher / Staff</label>
                <select name="teacher_id" id="teacher_select" required>
                    <option value="">-- Choose Staff Member --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['teacher_id'] ?>" 
                                data-salary="<?= $t['salary'] ?>" 
                                data-termly="<?= $t['termly_salary'] ?>">
                            <?= htmlspecialchars(($t['staff_code'] ?: 'TR-'.$t['teacher_id']) . ' - ' . $t['teacher_name']) ?> (<?= htmlspecialchars($t['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Cycle Type</label>
                <select name="payment_type" id="payment_type" required>
                    <option value="Monthly">Monthly Salary</option>
                    <option value="Termly">Termly Contract Fee</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pay Period / Term</label>
                <input type="text" name="pay_period" placeholder="e.g. October 2026 or Term 3 2026" required>
            </div>

            <div class="form-group">
                <label>Base Salary (UGX)</label>
                <input type="number" step="1000" name="base_salary" id="base_salary" placeholder="0.00" required>
            </div>

            <div style="margin-top:15px; font-weight:bold; color:var(--primary);">Allowances & Bonus</div>
            <div class="form-group">
                <label>Additional Allowances (UGX)</label>
                <input type="number" step="500" name="allowances" id="allowances" value="0.00">
            </div>
            <div class="form-group">
                <label>Allowance Description</label>
                <input type="text" name="allowance_notes" placeholder="e.g. Responsibility Allowance, Overtime">
            </div>

            <div style="margin-top:15px; font-weight:bold; color:var(--primary);">Deductions & Advances</div>
            <div class="form-group">
                <label>Total Deductions (UGX)</label>
                <input type="number" step="500" name="deductions" id="deductions" value="0.00">
            </div>
            <div class="form-group">
                <label>Deduction Reason / Notes</label>
                <input type="text" name="deduction_notes" placeholder="e.g. Salary Advance, NSSF, PAYE">
            </div>

            <div style="margin-top:15px; font-weight:bold; color:var(--primary);">Payment Disbursement Info</div>
            <div class="form-group">
                <label>Disbursement Date</label>
                <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Payment Mode</label>
                <select name="payment_mode" required>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="MTN MoMo">MTN Mobile Money</option>
                    <option value="Airtel Money">Airtel Money</option>
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div style="background:#f8fafc; border:1px solid #cbd5e1; padding:12px; border-radius:6px; margin:15px 0;">
                <div style="font-size:0.85rem; color:#64748b;">NET CALCULATED SALARY</div>
                <strong style="font-size:1.3rem; color:#166534;">UGX <span id="calc_net_pay">0.00</span></strong>
            </div>

            <button type="submit" class="btn">Generate & Save Payslip</button>
        </form>
    </div>

    <!-- 2. RECENT PAYSLIPS HISTORY TABLE -->
    <div class="card">
        <h2>Generated Payslips Registry</h2>

        <form action="payslip.php" method="GET" style="margin-bottom: 15px;">
            <div class="form-group" style="margin:0;">
                <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="🔍 Search by teacher name, staff code, or period..." onchange="this.form.submit()">
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff Name</th>
                        <th>Period</th>
                        <th>Base Salary</th>
                        <th>Net Pay</th>
                        <th>Mode</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payslips)): ?>
                        <tr><td colspan="7" style="text-align: center;">No payslips generated yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payslips as $ps): ?>
                            <tr>
                                <td><small><?= date('d M Y', strtotime($ps['payment_date'])) ?></small></td>
                                <td>
                                    <strong><?= htmlspecialchars($ps['teacher_name']) ?></strong>
                                    <br><small style="color:#64748b;"><?= htmlspecialchars($ps['staff_code']) ?></small>
                                </td>
                                <td><span class="badge" style="background:#eff6ff; color:#1e3a8a;"><?= htmlspecialchars($ps['pay_period']) ?></span></td>
                                <td>UGX <?= number_format($ps['base_salary']) ?></td>
                                <td><strong style="color:#166534;">UGX <?= number_format($ps['net_pay']) ?></strong></td>
                                <td><small><?= htmlspecialchars($ps['payment_mode']) ?></small></td>
                                <td>
                                    <button type="button" class="btn" style="padding: 4px 8px; font-size: 0.75rem; background:#166534;" 
                                            onclick="printPayslip(<?= $ps['payslip_id'] ?>)">
                                        Print Payslip
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

<!-- JAVASCRIPT PAYROLL COMPUTATION & PAYSLIP POPUP ENGINE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const teacherSelect = document.getElementById('teacher_select');
    const paymentType   = document.getElementById('payment_type');
    const baseInput     = document.getElementById('base_salary');
    const allowInput    = document.getElementById('allowances');
    const dedInput      = document.getElementById('deductions');
    const netOut        = document.getElementById('calc_net_pay');

    function autofillSalary() {
        const selected = teacherSelect.options[teacherSelect.selectedIndex];
        if (!selected || !selected.value) return;

        const monthlySalary = parseFloat(selected.getAttribute('data-salary')) || 0;
        const termlySalary  = parseFloat(selected.getAttribute('data-termly')) || 0;

        if (paymentType.value === 'Termly') {
            baseInput.value = termlySalary > 0 ? termlySalary : (monthlySalary * 3);
        } else {
            baseInput.value = monthlySalary;
        }
        updateNetPay();
    }

    function updateNetPay() {
        const base  = parseFloat(baseInput.value) || 0;
        const allow = parseFloat(allowInput.value) || 0;
        const ded   = parseFloat(dedInput.value) || 0;

        const net = (base + allow) - ded;
        netOut.textContent = net.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    teacherSelect.addEventListener('change', autofillSalary);
    paymentType.addEventListener('change', autofillSalary);
    baseInput.addEventListener('input', updateNetPay);
    allowInput.addEventListener('input', updateNetPay);
    dedInput.addEventListener('input', updateNetPay);
});

// POPUP PRINT ENGINE FOR SINGLE PAYSLIP
function printPayslip(payslipId) {
    window.open('print_payslip.php?payslip_id=' + payslipId, '_blank', 'width=750,height=700');
}
</script>
</body>
</html>