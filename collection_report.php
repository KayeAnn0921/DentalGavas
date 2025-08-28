<?php
include 'config.php';

// Filter setup (from current version)
$filter_type = $_GET['filter_type'] ?? 'day';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$filter_value = $_GET['filter_value'] ?? date('Y-m-d');
$start_month = $_GET['start_month'] ?? date('Y-m');
$end_month = $_GET['end_month'] ?? date('Y-m');
$start_year = $_GET['start_year'] ?? date('Y');
$end_year = $_GET['end_year'] ?? date('Y');

// WHERE clause for billing (from current version)
$where = [];
$params = [];

if ($filter_type === 'range') {
    $where[] = 'DATE(b.created_at) BETWEEN ? AND ?';
    $params[] = $start_date;
    $params[] = $end_date;
} elseif ($filter_type === 'day') {
    $where[] = 'DATE(b.created_at) = ?';
    $params[] = $filter_value;
} elseif ($filter_type === 'month') {
    $where[] = 'DATE_FORMAT(b.created_at, "%Y-%m") BETWEEN ? AND ?';
    $params[] = $start_month;
    $params[] = $end_month;
} elseif ($filter_type === 'year') {
    $where[] = 'YEAR(b.created_at) BETWEEN ? AND ?';
    $params[] = $start_year;
    $params[] = $end_year;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch billing info (from current version)
$sql = "SELECT 
            b.billing_id,
            b.created_at,
            b.total,
            b.discount,
            b.amount_paid,
            b.balance,
            b.months,
            CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS full_name
        FROM billing b
        JOIN patients p ON b.patient_id = p.patient_id
        $where_sql
        ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$billings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ledger filter for accurate total (from current version)
$ledger_where = [];
$ledger_params = [];

if ($filter_type === 'range') {
    $ledger_where[] = 'DATE(l.payment_date) BETWEEN ? AND ?';
    $ledger_params[] = $start_date;
    $ledger_params[] = $end_date;
} elseif ($filter_type === 'day') {
    $ledger_where[] = 'DATE(l.payment_date) = ?';
    $ledger_params[] = $filter_value;
} elseif ($filter_type === 'month') {
    $ledger_where[] = 'DATE_FORMAT(l.payment_date, "%Y-%m") BETWEEN ? AND ?';
    $ledger_params[] = $start_month;
    $ledger_params[] = $end_month;
} elseif ($filter_type === 'year') {
    $ledger_where[] = 'YEAR(l.payment_date) BETWEEN ? AND ?';
    $ledger_params[] = $start_year;
    $ledger_params[] = $end_year;
}

$ledger_where_sql = $ledger_where ? 'WHERE ' . implode(' AND ', $ledger_where) : '';

$sql_ledger = "SELECT 
                    l.billing_id,
                    l.payment_date,
                    l.payment_amount,
                    l.remarks,
                    CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS full_name
                FROM ledger l
                JOIN billing b ON l.billing_id = b.billing_id
                JOIN patients p ON b.patient_id = p.patient_id
                $ledger_where_sql
                ORDER BY l.payment_date ASC";
$stmt_ledger = $pdo->prepare($sql_ledger);
$stmt_ledger->execute($ledger_params);
$ledger_rows = $stmt_ledger->fetchAll(PDO::FETCH_ASSOC);

// Group ledger payments by billing_id (for old design)
$ledger_payments = [];
foreach ($ledger_rows as $row) {
    $ledger_payments[$row['billing_id']][] = $row;
}

$total_collected = array_sum(array_column($ledger_rows, 'payment_amount')) + array_sum(array_column($billings, 'amount_paid'));

// Prepare print link (from current version)
$print_url = "print_collection_report.php?filter_type=" . urlencode($filter_type);
if ($filter_type === 'day') {
    $print_url .= "&filter_value=" . urlencode($filter_value);
} elseif ($filter_type === 'range') {
    $print_url .= "&start_date=" . urlencode($start_date) . "&end_date=" . urlencode($end_date);
} elseif ($filter_type === 'month') {
    $print_url .= "&start_month=" . urlencode($start_month) . "&end_month=" . urlencode($end_month);
} elseif ($filter_type === 'year') {
    $print_url .= "&start_year=" . urlencode($start_year) . "&end_year=" . urlencode($end_year);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collection Report</title>
    <link rel="stylesheet" href="css/apointmentlist.css">
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .main-content {
            width: 1100px;
            margin: 40px auto 30px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.09);
            padding: 36px 36px 24px 36px;
        }
        h1 {
            color: #1976d2;
            font-size: 2.1em;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .filter-form label {
            font-weight: 500;
            color: #333;
        }
        .filter-form select, .filter-form input {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #b0bec5;
            font-size: 1em;
        }
        .print-btn {
            display:inline-block;
            margin-bottom:15px;
            padding:8px 18px;
            background:#1976d2;
            color:#fff;
            border-radius:4px;
            text-decoration:none;
            font-weight:bold;
            transition: background 0.2s;
        }
        .print-btn:hover {
            background: #1256a3;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            background: #fff;
        }
        th, td {
            border: 1px solid #e3e7ea;
            padding: 10px 8px;
            text-align: left;
        }
        th {
            background: #e3f0fc;
            color: #1976d2;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f8fbfd;
        }
        .sub-row td {
            background: #f3f8fd;
            color: #1976d2;
            font-size: 0.97em;
            border-top: none;
        }
        .total-table {
            margin-top: 18px;
            width: 320px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(25,118,210,0.07);
        }
        .total-table th {
            background: #1976d2;
            color: #fff;
            font-size: 1.1em;
            text-align: left;
        }
        .total-table td {
            font-size: 1.2em;
            font-weight: bold;
            color: #1976d2;
            background: #f8fbfd;
        }
        @media (max-width: 900px) {
            .main-content { padding: 16px 2vw; }
            table, th, td { font-size: 0.97em; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <h1>Collection Report</h1>
    <form method="get" class="filter-form">
        <label for="filter_type">Filter by:</label>
        <select name="filter_type" id="filter_type" onchange="updateFilterInput()">
            <option value="day" <?= $filter_type === 'day' ? 'selected' : '' ?>>Day</option>
            <option value="range" <?= $filter_type === 'range' ? 'selected' : '' ?>>Date Range</option>
            <option value="month" <?= $filter_type === 'month' ? 'selected' : '' ?>>Month/Range</option>
            <option value="year" <?= $filter_type === 'year' ? 'selected' : '' ?>>Year/Range</option>
        </select>
        <span id="filter-input">
            <?php if ($filter_type === 'day'): ?>
                <input type="date" name="filter_value" value="<?= htmlspecialchars($filter_value) ?>">
            <?php elseif ($filter_type === 'range'): ?>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                to
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <?php elseif ($filter_type === 'month'): ?>
                <input type="month" name="start_month" value="<?= htmlspecialchars($start_month) ?>">
                to
                <input type="month" name="end_month" value="<?= htmlspecialchars($end_month) ?>">
            <?php elseif ($filter_type === 'year'): ?>
                <input type="number" name="start_year" min="2000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($start_year) ?>">
                to
                <input type="number" name="end_year" min="2000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($end_year) ?>">
            <?php endif; ?>
        </span>
        <button type="submit" class="print-btn" style="margin-bottom:0;">Filter</button>
        <a href="<?= $print_url ?>" target="_blank" class="print-btn" style="margin-left:10px;">🖨️ Print Report</a>
    </form>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Billing Date</th>
                    <th>Patient Name</th>
                    <th>Billing ID</th>
                    <th>Total</th>
                    <th>Discount</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Months</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($billings)): ?>
                    <tr><td colspan="8">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($billings as $bill): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($bill['created_at']))) ?></td>
                            <td><?= htmlspecialchars($bill['full_name']) ?></td>
                            <td><?= htmlspecialchars($bill['billing_id']) ?></td>
                            <td>₱<?= number_format($bill['total'], 2) ?></td>
                            <td>₱<?= number_format($bill['discount'], 2) ?></td>
                            <td>₱<?= number_format($bill['amount_paid'], 2) ?></td>
                            <td>₱<?= number_format($bill['balance'], 2) ?></td>
                            <td><?= $bill['months'] ? htmlspecialchars($bill['months']) : '-' ?></td>
                        </tr>
                        <?php if (!empty($ledger_payments[$bill['billing_id']])): ?>
                            <tr class="sub-row">
                                <td colspan="8">
                                    <b>Payments:</b>
                                    <table style="width:100%;margin-top:5px;">
                                        <tr>
                                            <th style="width:30%;">Payment Date</th>
                                            <th style="width:30%;">Amount</th>
                                            <th style="width:40%;">Remarks</th>
                                        </tr>
                                        <?php foreach ($ledger_payments[$bill['billing_id']] as $pay): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($pay['payment_date']) ?></td>
                                                <td>₱<?= number_format($pay['payment_amount'], 2) ?></td>
                                                <td><?= htmlspecialchars($pay['remarks']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr class="sub-row">
                                <td colspan="8" style="color:#b71c1c;">No payments yet.</td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <table class="total-table">
            <tr>
                <th>Total Collected</th>
            </tr>
            <tr>
                <td>₱<?= number_format($total_collected, 2) ?></td>
            </tr>
        </table>
    </div>
</div>
<script>
function updateFilterInput() {
    var type = document.getElementById('filter_type').value;
    var filterInput = document.getElementById('filter-input');
    var today = new Date();
    if (type === 'day') {
        filterInput.innerHTML = '<input type="date" name="filter_value" value="<?= date('Y-m-d') ?>">';
    } else if (type === 'range') {
        filterInput.innerHTML = '<input type="date" name="start_date" value="<?= date('Y-m-d') ?>"> to <input type="date" name="end_date" value="<?= date('Y-m-d') ?>">';
    } else if (type === 'month') {
        filterInput.innerHTML = '<input type="month" name="start_month" value="<?= date('Y-m') ?>"> to <input type="month" name="end_month" value="<?= date('Y-m') ?>">';
    } else if (type === 'year') {
        filterInput.innerHTML = '<input type="number" name="start_year" min="2000" max="' + today.getFullYear() + '" value="<?= date('Y') ?>"> to <input type="number" name="end_year" min="2000" max="' + today.getFullYear() + '" value="<?= date('Y') ?>">';
    }
}
</script>
</body>
</html>