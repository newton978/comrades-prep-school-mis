<?php
require_once 'db.php';

$payslip_id = isset($_GET['payslip_id']) ? (int)$_GET['payslip_id'] : 0;

if (!$payslip_id) {
    die("Invalid Payslip ID.");
}

$stmt = $pdo->prepare("
    SELECT ps.*, t.teacher_name, t.staff_code, t.phone, t.national_id, t.qualification, t.role, c.class_name
    FROM Payslips ps
    JOIN Teachers t ON ps.teacher_id = t.teacher_id
    LEFT JOIN Classes c ON t.assigned_class_id = c.class_id
    WHERE ps.payslip_id = ?
");
$stmt->execute([$payslip_id]);
$ps = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ps) {
    die("Payslip record not found.");
}

$gross_pay = (float)$ps['base_salary'] + (float)$ps['allowances'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - <?= htmlspecialchars($ps['teacher_name']) ?> (<?= htmlspecialchars($ps['pay_period']) ?>)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #1e293b; max-width: 700px; margin: 0 auto; background: #fff; }
        .payslip-card { border: 2px solid #166534; padding: 25px; border-radius: 8px; }
        .header { text-align: center; border-bottom: 2px solid #166534; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #166534; font-size: 1.6rem; }
        .header p { margin: 3px 0; font-size: 0.85rem; color: #475569; }
        .title { text-align: center; font-weight: bold; font-size: 1.1rem; color: #166534; margin-bottom: 15px; text-transform: uppercase; }
        .info-table, .breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px; font-size: 0.9rem; }
        .breakdown-table th, .breakdown-table td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 0.9rem; }
        .breakdown-table th { background: #f0fdf4; color: #166534; }
        .net-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 20px; }
        .net-box strong { font-size: 1.5rem; color: #166534; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 0.85rem; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="payslip-card">
    <div class="header">
        <h1>COMRADES PREPARATORY SCHOOL</h1>
        <p>Kitebi Campus - P.O. Box Kampala, Uganda | Tel: +256 700 000 000</p>
        <p>Official Staff Salary Voucher & Payslip</p>
    </div>

    <div class="title">PAYSLIP FOR PERIOD: <?= htmlspecialchars($ps['pay_period']) ?></div>

    <table class="info-table">
        <tr>
            <td><strong>Staff Code:</strong> <?= htmlspecialchars($ps['staff_code'] ?: 'TR-'.$ps['teacher_id']) ?></td>
            <td><strong>Disbursement Date:</strong> <?= date('d M Y', strtotime($ps['payment_date'])) ?></td>
        </tr>
        <tr>
            <td><strong>Staff Name:</strong> <?= htmlspecialchars($ps['teacher_name']) ?></td>
            <td><strong>Designation / Role:</strong> <?= htmlspecialchars($ps['role']) ?></td>
        </tr>
        <tr>
            <td><strong>National ID (NIN):</strong> <?= htmlspecialchars($ps['national_id'] ?: 'N/A') ?></td>
            <td><strong>Payment Mode:</strong> <?= htmlspecialchars($ps['payment_mode']) ?></td>
        </tr>
    </table>

    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Earnings & Additions</th>
                <th>Amount (UGX)</th>
                <th>Deductions & Advances</th>
                <th>Amount (UGX)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Base Salary (<?= htmlspecialchars($ps['payment_type']) ?>)</td>
                <td><?= number_format($ps['base_salary'], 2) ?></td>
                <td>Statutory & Advance Deductions</td>
                <td><?= number_format($ps['deductions'], 2) ?></td>
            </tr>
            <tr>
                <td>
                    Allowances / Extra Duty<br>
                    <small style="color:#64748b;"><?= htmlspecialchars($ps['allowance_notes'] ?: 'None') ?></small>
                </td>
                <td><?= number_format($ps['allowances'], 2) ?></td>
                <td>
                    Deduction Details<br>
                    <small style="color:#64748b;"><?= htmlspecialchars($ps['deduction_notes'] ?: 'None') ?></small>
                </td>
                <td>-</td>
            </tr>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td>GROSS EARNINGS</td>
                <td>UGX <?= number_format($gross_pay, 2) ?></td>
                <td>TOTAL DEDUCTIONS</td>
                <td>UGX <?= number_format($ps['deductions'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="net-box">
        <div>TOTAL NET SALARY PAID</div>
        <strong>UGX <?= number_format($ps['net_pay'], 2) ?></strong>
    </div>

    <div class="footer">
        <div>
            Employee Signature: _____________________<br>
            <small>Date: _______________</small>
        </div>
        <div>
            Bursar / Headteacher Stamp: _____________________<br>
            <small>Comrades Prep School</small>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html>