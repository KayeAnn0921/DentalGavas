<?php
include 'config.php';

// Copy filter logic from your main report
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Collection Report</title>
    <style>
        body {
            font-family: Georgia, serif;
            background: #fff;
        }
        .report-container {
            max-width: 1100px;
            margin: 30px auto;
            border: 2px solid #1976d2;
            border-radius: 8px;
            background: #fff;
            padding: 40px 30px 30px 30px;
        }
        .clinic-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            text-align: center;
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1976d2;
        }
        .date-range {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.1em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background: #f5f5f5;
        }
        .sub-row td {
            background: #f9f9f9;
            color: #1976d2;
            font-size: 0.97em;
        }
        .signature {
            text-align: right;
            margin-top: 60px;
            font-size: 1.1em;
        }
        @media print {
            body {
                background: #fff !important;
            }
            .report-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
<div class="report-container">
    <div class="clinic-info">
        <strong>Dr. Glenn E. Gavas, DMD</strong><br>
        Gavas Dental Clinic<br>
        Door 1, Ferrer Building, Saging Street, General Santos City<br>
        Phone: (083) 123-4567
    </div>
    <div class="report-title">Collection Report</div>
    <div class="date-range">
        <?php
        if ($filter_type === 'day') {
            echo "For: <b>" . htmlspecialchars(date('F d, Y', strtotime($filter_value))) . "</b>";
        } elseif ($filter_type === 'range') {
            echo "From <b>" . htmlspecialchars(date('F d, Y', strtotime($start_date))) . "</b> to <b>" . htmlspecialchars(date('F d, Y', strtotime($end_date))) . "</b>";
        } elseif ($filter_type === 'month') {
            echo "From <b>" . htmlspecialchars(date('F Y', strtotime($start_month))) . "</b> to <b>" . htmlspecialchars(date('F Y', strtotime($end_month))) . "</b>";
        } elseif ($filter_type === 'year') {
            echo "From <b>" . htmlspecialchars($start_year) . "</b> to <b>" . htmlspecialchars($end_year) . "</b>";
        }
        ?>
    </div>
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
        <div style="margin-top:16px;">
            <table style="width:300px;">
                <tr>
                    <th style="text-align:left;">Total Collected</th>
                </tr>
                <tr>
                    <td style="font-size:1.2em;"><b>₱<?= number_format($total_collected, 2) ?></b></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="signature">
        <strong>Dr. Glenn E. Gavas, DMD</strong><br>
        DEA No. 1234563<br>
        State License No. 65432
    </div>
</div>
</body>
</html>