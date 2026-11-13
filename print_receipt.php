<?php
require_once 'db.php';

$payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;

if (!$payment_id) {
    die("Invalid Payment ID.");
}

// Fetch Payment Details, Custom Tuition Override, and Standard Class Fee Info
$stmt = $pdo->prepare("
    SELECT fp.*, p.admission_no, p.first_name, p.last_name, p.custom_fee, c.class_id, c.class_name,
           COALESCE(p.custom_fee, fs.total_fee, 0) AS term_fee,
           (p.custom_fee IS NOT NULL) AS is_custom_rate
    FROM Fee_Payments fp
    JOIN Pupils p ON fp.pupil_id = p.pupil_id
    LEFT JOIN Classes c ON p.class_id = c.class_id
    LEFT JOIN Fee_Structures fs ON c.class_id = fs.class_id AND fs.academic_year = YEAR(fp.payment_date)
    WHERE fp.payment_id = ?
");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Receipt record not found.");
}

// Calculate Total Cumulative Payments Made by Pupil Up to This Transaction
$stmt_tot = $pdo->prepare("SELECT SUM(amount_paid) FROM Fee_Payments WHERE pupil_id = ? AND payment_id <= ?");
$stmt_tot->execute([$payment['pupil_id'], $payment_id]);
$total_paid_to_date = (float)$stmt_tot->fetchColumn();

// Remaining Balance Calculation
$balance = (float)$payment['term_fee'] - $total_paid_to_date;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #<?= sprintf('%05d', $payment['payment_id']) ?> - Comrades Prep School</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; color: #1e293b; max-width: 650px; margin: 0 auto; }
        .receipt-card { border: 2px solid #1e3a8a; padding: 25px; border-radius: 8px; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 1.6rem; }
        .header p { margin: 3px 0; font-size: 0.85rem; color: #475569; }
        .receipt-title { text-align: center; font-weight: bold; font-size: 1.1rem; color: #1e3a8a; margin-bottom: 15px; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px; font-size: 0.95rem; }
        .amount-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 20px; }
        .amount-box strong { font-size: 1.4rem; color: #166534; }
        .footer { margin-top: 35px; display: flex; justify-content: space-between; font-size: 0.85rem; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <div class="header">
        <h1>COMRADES PREPARATORY SCHOOL</h1>
        <p>Kitebi Campus - P.O. Box Kampala, Uganda | Tel: +256 700 000 000</p>
        <p>Official School Fees Payment Receipt</p>
    </div>

    <div class="receipt-title">RECEIPT #<?= sprintf('%05d', $payment['payment_id']) ?></div>

    <table class="info-table">
        <tr><td><strong>Date:</strong> <?= date('d M Y', strtotime($payment['payment_date'])) ?></td><td><strong>Payment Mode:</strong> <?= htmlspecialchars($payment['payment_mode']) ?></td></tr>
        <tr><td><strong>Student Name:</strong> <?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></td><td><strong>Admission No:</strong> <?= htmlspecialchars($payment['admission_no']) ?></td></tr>
        <tr><td><strong>Class Level:</strong> <?= htmlspecialchars($payment['class_name'] ?? 'N/A') ?></td><td><strong>Reference No:</strong> <?= htmlspecialchars($payment['reference_no'] ?: 'N/A') ?></td></tr>
    </table>

    <div class="amount-box">
        <div>AMOUNT RECEIVED</div>
        <strong>UGX <?= number_format($payment['amount_paid'], 2) ?></strong>
    </div>

    <table class="info-table" style="border-top: 1px dashed #cbd5e1; padding-top: 10px;">
        <tr>
            <td>
                <strong>Billed Term Fee Rate:</strong>
                <?php if ($payment['is_custom_rate']): ?>
                    <small style="color: #d97706; font-weight: bold;">(Individual Custom Tuition)</small>
                <?php else: ?>
                    <small style="color: #64748b;">(Standard Class Fee)</small>
                <?php endif; ?>
            </td>
            <td style="text-align: right;"><strong>UGX <?= number_format($payment['term_fee'], 2) ?></strong></td>
        </tr>
        <tr><td><strong>Total Paid to Date:</strong></td><td style="text-align: right;">UGX <?= number_format($total_paid_to_date, 2) ?></td></tr>
        <tr><td><strong>Balance Remaining:</strong></td><td style="text-align: right; font-weight: bold; color: <?= $balance > 0 ? '#dc2626' : '#166534' ?>;">UGX <?= number_format($balance, 2) ?></td></tr>
    </table>

    <div class="footer">
        <div>Received By: _____________________<br><small>(<?= htmlspecialchars($payment['received_by'] ?: 'Bursar') ?>)</small></div>
        <div>School Stamp & Signature: _____________________</div>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html>