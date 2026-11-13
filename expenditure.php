<?php
require_once 'db.php';

$message = '';
$message_type = 'success';
$edit_expense = null;

// Standard Units Array
$standard_units = [
    'Pcs' => 'Pieces (Pcs)',
    'Kgs' => 'Kilograms (Kgs)',
    'Litres' => 'Litres (L)',
    'Reams' => 'Reams (Paper)',
    'Bags' => 'Bags',
    'Sacks' => 'Sacks',
    'Boxes' => 'Boxes',
    'Cartons' => 'Cartons',
    'Packs' => 'Packs',
    'Bundles' => 'Bundles',
    'Pairs' => 'Pairs',
    'Grams' => 'Grams (g)',
    'Millilitres' => 'Millilitres (ml)',
    'Tons' => 'Tons',
    'Metres' => 'Metres (m)',
    'Rolls' => 'Rolls',
    'Yards' => 'Yards',
    'Trips' => 'Trips (Transport)',
    'Hours' => 'Hours',
    'Days' => 'Days',
    'Months' => 'Months',
    'Service Fee' => 'Service Fee / Flat'
];

// ========================================================
// 1. CREATE & UPDATE HANDLERS
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action        = $_POST['action'] ?? '';
    $item_name     = trim($_POST['item_name']);
    $category      = $_POST['category'];
    $quantity      = (float)$_POST['quantity'];
    
    // Handle Unit Selection or Custom Unit
    $unit_measure  = trim($_POST['unit_measure']);
    if ($unit_measure === 'Other' && !empty($_POST['custom_unit_measure'])) {
        $unit_measure = trim($_POST['custom_unit_measure']);
    }

    $unit_cost     = (float)$_POST['unit_cost'];
    $total_cost    = $quantity * $unit_cost;
    $term          = $_POST['term'];
    $academic_year = (int)$_POST['academic_year'];
    $expense_date  = $_POST['expense_date'];
    $purchased_by  = trim($_POST['purchased_by']);
    $receipt_no    = trim($_POST['receipt_no']);
    $description   = trim($_POST['description']);

    if ($action === 'create') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO School_Expenses (item_name, category, quantity, unit_measure, unit_cost, total_cost, term, academic_year, expense_date, purchased_by, receipt_no, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$item_name, $category, $quantity, $unit_measure, $unit_cost, $total_cost, $term, $academic_year, $expense_date, $purchased_by, $receipt_no, $description]);
            $message = "Expense record saved successfully!";
        } catch (PDOException $e) {
            $message = "Error creating record: " . $e->getMessage();
            $message_type = 'danger';
        }
    } elseif ($action === 'update') {
        $expense_id = (int)$_POST['expense_id'];
        try {
            $stmt = $pdo->prepare("
                UPDATE School_Expenses 
                SET item_name=?, category=?, quantity=?, unit_measure=?, unit_cost=?, total_cost=?, term=?, academic_year=?, expense_date=?, purchased_by=?, receipt_no=?, description=?
                WHERE expense_id=?
            ");
            $stmt->execute([$item_name, $category, $quantity, $unit_measure, $unit_cost, $total_cost, $term, $academic_year, $expense_date, $purchased_by, $receipt_no, $description, $expense_id]);
            $message = "Expense record updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating record: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// ========================================================
// 2. DELETE & EDIT FETCH HANDLERS
// ========================================================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM School_Expenses WHERE expense_id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        header("Location: expenditure.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $message = "Error deleting record: " . $e->getMessage();
        $message_type = 'danger';
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM School_Expenses WHERE expense_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_expense = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ========================================================
// 3. FILTER & SUMMARY QUERIES
// ========================================================
$filter_term   = $_GET['filter_term'] ?? '';
$filter_year   = $_GET['filter_year'] ?? '';
$filter_period = $_GET['filter_period'] ?? 'all'; // daily, weekly, monthly, yearly, all
$filter_cat    = $_GET['filter_cat'] ?? '';

$where_clauses = [];
if (!empty($filter_term))   $where_clauses[] = "term = " . $pdo->quote($filter_term);
if (!empty($filter_year))   $where_clauses[] = "academic_year = " . (int)$filter_year;
if (!empty($filter_cat))    $where_clauses[] = "category = " . $pdo->quote($filter_cat);

// Period Filtering Logic
if ($filter_period === 'daily') {
    $where_clauses[] = "expense_date = CURRENT_DATE()";
} elseif ($filter_period === 'weekly') {
    $where_clauses[] = "YEARWEEK(expense_date, 1) = YEARWEEK(CURRENT_DATE(), 1)";
} elseif ($filter_period === 'monthly') {
    $where_clauses[] = "MONTH(expense_date) = MONTH(CURRENT_DATE()) AND YEAR(expense_date) = YEAR(CURRENT_DATE())";
} elseif ($filter_period === 'yearly') {
    $where_clauses[] = "YEAR(expense_date) = YEAR(CURRENT_DATE())";
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

$expenses = $pdo->query("SELECT * FROM School_Expenses $where_sql ORDER BY expense_date DESC, expense_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Calculate Grand Total for Filtered Selection
$grand_total = 0;
foreach ($expenses as $ex) {
    $grand_total += (float)$ex['total_cost'];
}

require_once 'header.php';
?>

<?php if ($message || isset($_GET['msg'])): ?>
    <div class="alert alert-<?= $message_type ?>" style="background: <?= $message_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $message_type === 'success' ? '#bbf7d0' : '#fecaca' ?>; color: <?= $message_type === 'success' ? '#166534' : '#991b1b' ?>; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <?= htmlspecialchars($message ?: ($_GET['msg'] === 'deleted' ? 'Expense record deleted successfully!' : '')) ?>
    </div>
<?php endif; ?>

<div class="grid">
    <!-- 1. ENTRY & EDIT FORM -->
    <div class="card">
        <h2><?= $edit_expense ? 'Edit Expense Record' : 'Record New Expenditure' ?></h2>
        <form action="expenditure.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_expense ? 'update' : 'create' ?>">
            <?php if ($edit_expense): ?>
                <input type="hidden" name="expense_id" value="<?= $edit_expense['expense_id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Item Name / Description</label>
                <input type="text" name="item_name" value="<?= htmlspecialchars($edit_expense['item_name'] ?? '') ?>" placeholder="e.g. Chalk boxes, Posho bags, Fuel" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" id="ex_category" required>
                    <option value="Academic Supplies" <?= ($edit_expense['category'] ?? '') === 'Academic Supplies' ? 'selected' : '' ?>>Academic Supplies</option>
                    <option value="Kitchen & Food" <?= ($edit_expense['category'] ?? '') === 'Kitchen & Food' ? 'selected' : '' ?>>Kitchen & Food Provisions</option>
                    <option value="Utilities & Bills" <?= ($edit_expense['category'] ?? '') === 'Utilities & Bills' ? 'selected' : '' ?>>Utilities & Bills (Water/Elec/Internet)</option>
                    <option value="Maintenance & Repairs" <?= ($edit_expense['category'] ?? '') === 'Maintenance & Repairs' ? 'selected' : '' ?>>Maintenance & Repairs</option>
                    <option value="Transport & Fuel" <?= ($edit_expense['category'] ?? '') === 'Transport & Fuel' ? 'selected' : '' ?>>Transport & Fuel</option>
                    <option value="Staff Welfare" <?= ($edit_expense['category'] ?? '') === 'Staff Welfare' ? 'selected' : '' ?>>Staff Welfare & Lunches</option>
                    <option value="Miscellaneous" <?= ($edit_expense['category'] ?? '') === 'Miscellaneous' ? 'selected' : '' ?>>Miscellaneous</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap: 10px;">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" step="0.01" id="ex_qty" name="quantity" value="<?= htmlspecialchars($edit_expense['quantity'] ?? '1') ?>" required>
                </div>

                <div class="form-group">
                    <label>Unit of Measure</label>
                    <?php 
                        $current_unit = $edit_expense['unit_measure'] ?? 'Pcs';
                        $is_custom = !empty($current_unit) && !array_key_exists($current_unit, $standard_units);
                    ?>
                    <select name="unit_measure" id="ex_unit_select" required>
                        <?php foreach ($standard_units as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($current_unit === $key) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                        <option value="Other" <?= $is_custom ? 'selected' : '' ?>>Other / Custom Unit...</option>
                    </select>

                    <input type="text" name="custom_unit_measure" id="ex_custom_unit" 
                           value="<?= $is_custom ? htmlspecialchars($current_unit) : '' ?>" 
                           placeholder="Type custom unit (e.g. Jerrycans)" 
                           style="margin-top: 5px; display: <?= $is_custom ? 'block' : 'none' ?>;">
                </div>
            </div>

            <div class="form-group">
                <label>Unit Cost (UGX)</label>
                <input type="number" step="100" id="ex_cost" name="unit_cost" value="<?= htmlspecialchars($edit_expense['unit_cost'] ?? '0') ?>" required>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:10px; border-radius:4px; margin-bottom:12px;">
                <span style="font-size:0.8rem; color:#64748b;">Calculated Total Cost:</span><br>
                <strong style="font-size:1.2rem; color:#166534;">UGX <span id="calc_total">0</span></strong>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label>Term</label>
                    <select name="term" required>
                        <option value="Term 1" <?= ($edit_expense['term'] ?? '') === 'Term 1' ? 'selected' : '' ?>>Term 1</option>
                        <option value="Term 2" <?= ($edit_expense['term'] ?? '') === 'Term 2' ? 'selected' : '' ?>>Term 2</option>
                        <option value="Term 3" <?= ($edit_expense['term'] ?? '') === 'Term 3' ? 'selected' : '' ?>>Term 3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="number" name="academic_year" value="<?= htmlspecialchars($edit_expense['academic_year'] ?? date('Y')) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Date Purchased / Incurred</label>
                <input type="date" name="expense_date" value="<?= htmlspecialchars($edit_expense['expense_date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label>Purchased By / Authorized Person</label>
                <input type="text" name="purchased_by" value="<?= htmlspecialchars($edit_expense['purchased_by'] ?? '') ?>" placeholder="e.g. Bursar, Headteacher, Teacher John">
            </div>

            <div class="form-group">
                <label>Voucher / Receipt Reference No.</label>
                <input type="text" name="receipt_no" value="<?= htmlspecialchars($edit_expense['receipt_no'] ?? '') ?>" placeholder="e.g. VOUCH-00491">
            </div>

            <div class="form-group">
                <label>Additional Notes / Usage Purpose</label>
                <textarea name="description" rows="2" placeholder="Where was it used or stored?"><?= htmlspecialchars($edit_expense['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn"><?= $edit_expense ? 'Update Expense Record' : 'Save Expense Record' ?></button>
            <?php if ($edit_expense): ?>
                <p style="text-align:center; margin-top:8px;"><a href="expenditure.php" style="color:var(--text);">Cancel Editing</a></p>
            <?php endif; ?>
        </form>
    </div>

    <!-- 2. DIRECTORY & REPORT GENERATOR TABLE -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:12px;">
            <h2 style="margin:0;">Termly Expenses & Receipt Ledger</h2>
            <button type="button" class="btn" style="background:#166534; padding:6px 12px; font-size:0.8rem;" 
                    onclick='printExpenseReceipt(<?= json_encode($expenses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, "<?= $filter_period ?>", "<?= $grand_total ?>")'>
                 Print Filtered Expense Summary Receipt
            </button>
        </div>

        <!-- PERIOD & TERM FILTERS -->
        <form action="expenditure.php" method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:15px;">
            <div>
                <label style="font-size:0.75rem; font-weight:bold; display:block;">Time Period:</label>
                <select name="filter_period" onchange="this.form.submit()" style="padding:5px;">
                    <option value="all" <?= $filter_period === 'all' ? 'selected' : '' ?>>All Records</option>
                    <option value="daily" <?= $filter_period === 'daily' ? 'selected' : '' ?>>Today's Expenses (Daily)</option>
                    <option value="weekly" <?= $filter_period === 'weekly' ? 'selected' : '' ?>>This Week's Expenses</option>
                    <option value="monthly" <?= $filter_period === 'monthly' ? 'selected' : '' ?>>This Month's Expenses</option>
                    <option value="yearly" <?= $filter_period === 'yearly' ? 'selected' : '' ?>>This Year's Expenses</option>
                </select>
            </div>

            <div>
                <label style="font-size:0.75rem; font-weight:bold; display:block;">Term:</label>
                <select name="filter_term" onchange="this.form.submit()" style="padding:5px;">
                    <option value="">All Terms</option>
                    <option value="Term 1" <?= $filter_term === 'Term 1' ? 'selected' : '' ?>>Term 1</option>
                    <option value="Term 2" <?= $filter_term === 'Term 2' ? 'selected' : '' ?>>Term 2</option>
                    <option value="Term 3" <?= $filter_term === 'Term 3' ? 'selected' : '' ?>>Term 3</option>
                </select>
            </div>

            <div>
                <label style="font-size:0.75rem; font-weight:bold; display:block;">Category:</label>
                <select name="filter_cat" onchange="this.form.submit()" style="padding:5px;">
                    <option value="">All Categories</option>
                    <option value="Academic Supplies" <?= $filter_cat === 'Academic Supplies' ? 'selected' : '' ?>>Academic Supplies</option>
                    <option value="Kitchen & Food" <?= $filter_cat === 'Kitchen & Food' ? 'selected' : '' ?>>Kitchen & Food</option>
                    <option value="Utilities & Bills" <?= $filter_cat === 'Utilities & Bills' ? 'selected' : '' ?>>Utilities & Bills</option>
                    <option value="Maintenance & Repairs" <?= $filter_cat === 'Maintenance & Repairs' ? 'selected' : '' ?>>Maintenance & Repairs</option>
                    <option value="Transport & Fuel" <?= $filter_cat === 'Transport & Fuel' ? 'selected' : '' ?>>Transport & Fuel</option>
                    <option value="Staff Welfare" <?= $filter_cat === 'Staff Welfare' ? 'selected' : '' ?>>Staff Welfare</option>
                    <option value="Miscellaneous" <?= $filter_cat === 'Miscellaneous' ? 'selected' : '' ?>>Miscellaneous</option>
                </select>
            </div>
        </form>

        <!-- SUMMARY STATS BANNER -->
        <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:10px 15px; border-radius:6px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:bold; color:#1e3a8a;">Total Expenditures Count: <?= count($expenses) ?></span>
            <span style="font-weight:bold; color:#166534; font-size:1.1rem;">Grand Total Spent: UGX <?= number_format($grand_total, 2) ?></span>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item & Category</th>
                        <th>Qty / Unit</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Purchased By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="7" style="text-align: center;">No expenditure records found for selected filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $ex): ?>
                            <tr>
                                <td><small><?= date('d M Y', strtotime($ex['expense_date'])) ?></small><br><small style="color:#64748b;"><?= htmlspecialchars($ex['term']) ?></small></td>
                                <td>
                                    <strong><?= htmlspecialchars($ex['item_name']) ?></strong>
                                    <br><span class="badge" style="background:#f1f5f9; color:#334155; font-size:0.7rem;"><?= htmlspecialchars($ex['category']) ?></span>
                                    <?php if (!empty($ex['receipt_no'])): ?>
                                        <br><small style="color:#64748b;">Ref: <?= htmlspecialchars($ex['receipt_no']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($ex['quantity'], 2) ?> <strong><?= htmlspecialchars($ex['unit_measure']) ?></strong></td>
                                <td>UGX <?= number_format($ex['unit_cost'], 2) ?></td>
                                <td><strong style="color:#166534;">UGX <?= number_format($ex['total_cost'], 2) ?></strong></td>
                                <td><small><?= htmlspecialchars($ex['purchased_by'] ?: 'N/A') ?></small></td>
                                <td class="actions">
                                    <button type="button" class="btn" style="padding: 3px 6px; font-size: 0.75rem; background:#1e3a8a; margin-bottom:2px;" 
                                            onclick='printSingleVoucher(<?= json_encode($ex, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        Voucher
                                    </button>
                                    <a href="expenditure.php?edit=<?= $ex['expense_id'] ?>" class="edit">Edit</a>
                                    <a href="expenditure.php?delete=<?= $ex['expense_id'] ?>" class="delete" onclick="return confirm('Delete expense entry?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CALCULATOR & DYNAMIC UNIT SELECTION SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput     = document.getElementById('ex_qty');
    const costInput    = document.getElementById('ex_cost');
    const totalOut     = document.getElementById('calc_total');
    const unitSelect   = document.getElementById('ex_unit_select');
    const customUnit   = document.getElementById('ex_custom_unit');
    const categorySelect = document.getElementById('ex_category');

    function calculateTotal() {
        const qty   = parseFloat(qtyInput.value) || 0;
        const cost  = parseFloat(costInput.value) || 0;
        const total = qty * cost;
        totalOut.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Toggle custom unit text field
    unitSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            customUnit.style.display = 'block';
            customUnit.focus();
        } else {
            customUnit.style.display = 'none';
        }
    });

    // Auto-set unit default based on category (if not editing existing)
    categorySelect.addEventListener('change', function() {
        const cat = this.value;
        if (cat === 'Kitchen & Food') unitSelect.value = 'Kgs';
        else if (cat === 'Academic Supplies') unitSelect.value = 'Reams';
        else if (cat === 'Transport & Fuel') unitSelect.value = 'Litres';
        else if (cat === 'Utilities & Bills') unitSelect.value = 'Service Fee';
        else unitSelect.value = 'Pcs';
        
        unitSelect.dispatchEvent(new Event('change'));
    });

    qtyInput.addEventListener('input', calculateTotal);
    costInput.addEventListener('input', calculateTotal);
    calculateTotal();
});

// PRINT SINGLE EXPENSE PAYMENT VOUCHER
function printSingleVoucher(ex) {
    const printWin = window.open('', '_blank', 'width=750,height=600');
    if (!printWin) { alert('Please allow popups to print voucher.'); return; }

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Voucher - ${ex.item_name}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; max-width: 650px; margin: 0 auto; color: #1e293b; }
                .voucher-card { border: 2px solid #1e3a8a; padding: 20px; border-radius: 8px; }
                .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 15px; }
                .header h1 { margin: 0; color: #1e3a8a; font-size: 1.5rem; }
                .header p { margin: 2px 0 0 0; font-size: 0.8rem; color: #64748b; }
                .title { text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 1.1rem; text-transform: uppercase; color: #1e3a8a; }
                .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
                .total-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 6px; text-align: center; margin: 15px 0; font-size: 1.2rem; font-weight: bold; color: #166534; }
                .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 0.85rem; }
            </style>
        </head>
        <body>
            <div class="voucher-card">
                <div class="header">
                    <h1>COMRADES PREPARATORY SCHOOL</h1>
                    <p>Kitebi Campus - Official Payment & Expenditure Voucher</p>
                </div>
                <div class="title">OFFICIAL PAYMENT VOUCHER</div>

                <div class="row"><span>Voucher / Receipt Ref:</span> <strong>${ex.receipt_no || 'VOUCH-' + ex.expense_id}</strong></div>
                <div class="row"><span>Date Incurred:</span> <strong>${ex.expense_date}</strong></div>
                <div class="row"><span>Term / Year:</span> <strong>${ex.term} ${ex.academic_year}</strong></div>
                <div class="row"><span>Category:</span> <strong>${ex.category}</strong></div>
                <div class="row"><span>Item Description:</span> <strong>${ex.item_name}</strong></div>
                <div class="row"><span>Quantity & Unit:</span> <strong>${ex.quantity} ${ex.unit_measure} @ UGX ${parseFloat(ex.unit_cost).toLocaleString()}</strong></div>
                <div class="row"><span>Purchased / Disbursed To:</span> <strong>${ex.purchased_by || 'N/A'}</strong></div>

                <div class="total-box">
                    TOTAL DISBURSED: UGX ${parseFloat(ex.total_cost).toLocaleString(undefined, {minimumFractionDigits: 2})}
                </div>

                <div class="footer">
                    <div>Authorized By: ___________________<br><small>Bursar / Headteacher</small></div>
                    <div>Received By: ___________________<br><small>Payee Signature</small></div>
                </div>
            </div>
            <script>window.onload = function() { window.print(); };<\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

// PRINT BATCH EXPENSES SUMMARY RECEIPT (DAILY, WEEKLY, MONTHLY, YEARLY)
function printExpenseReceipt(expensesList, periodType, grandTotal) {
    if (!expensesList || expensesList.length === 0) {
        alert('No expenditure records available to print.');
        return;
    }

    const printWin = window.open('', '_blank', 'width=850,height=700');
    if (!printWin) { alert('Please allow popups to print summary.'); return; }

    let rowsHtml = '';
    expensesList.forEach((ex, idx) => {
        rowsHtml += `
            <tr>
                <td>${idx + 1}</td>
                <td>${ex.expense_date}</td>
                <td><strong>${ex.item_name}</strong><br><small>${ex.category}</small></td>
                <td>${ex.quantity} ${ex.unit_measure}</td>
                <td>UGX ${parseFloat(ex.unit_cost).toLocaleString()}</td>
                <td><strong>UGX ${parseFloat(ex.total_cost).toLocaleString()}</strong></td>
                <td><small>${ex.purchased_by || '-'}</small></td>
            </tr>
        `;
    });

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Expenditure Summary Report</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; color: #1e293b; background: #fff; }
                .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
                .header h1 { margin: 0; color: #1e3a8a; font-size: 1.5rem; }
                .header p { margin: 2px 0 0 0; font-size: 0.85rem; color: #64748b; }
                .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.85rem; }
                .report-table th, .report-table td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
                .report-table th { background: #f1f5f9; color: #1e3a8a; }
                .total-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; text-align: right; font-size: 1.2rem; font-weight: bold; color: #166534; margin-bottom: 20px; }
                .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 0.85rem; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>COMRADES PREPARATORY SCHOOL</h1>
                <p>Kitebi Campus - Official Expenditure & Procurement Audit Summary</p>
            </div>
            
            <div style="margin-bottom: 15px; font-size: 0.9rem;">
                <strong>Period Filter:</strong> ${periodType.toUpperCase()} | 
                <strong>Date Printed:</strong> ${new Date().toLocaleDateString()}
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Item & Category</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Purchased By</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
            </table>

            <div class="total-box">
                GRAND TOTAL EXPENDITURE: UGX ${parseFloat(grandTotal).toLocaleString(undefined, {minimumFractionDigits: 2})}
            </div>

            <div class="footer">
                <div>Prepared By: ___________________<br><small>Bursar / Accounts Office</small></div>
                <div>Approved By: ___________________<br><small>Headteacher Signature & Stamp</small></div>
            </div>
            <script>window.onload = function() { window.print(); };<\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>
</body>
</html>