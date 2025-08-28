<?php
include 'config.php';

// Handle filter input (MATCHING PATIENT REPORT)
$filter_type = $_GET['filter_type'] ?? 'day';
$filter_value = $_GET['filter_value'] ?? date('Y-m-d');
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$start_month = $_GET['start_month'] ?? date('Y-m');
$end_month = $_GET['end_month'] ?? date('Y-m');
$start_year = $_GET['start_year'] ?? date('Y');
$end_year = $_GET['end_year'] ?? date('Y');

$where = '';
$params = [];

if ($filter_type === 'day') {
    $where = 'WHERE DATE(appointment_date) = ?';
    $params[] = $filter_value;
} elseif ($filter_type === 'range') {
    $where = 'WHERE DATE(appointment_date) BETWEEN ? AND ?';
    $params[] = $start_date;
    $params[] = $end_date;
} elseif ($filter_type === 'month') {
    if (!empty($_GET['start_month']) && !empty($_GET['end_month'])) {
        $where = 'WHERE DATE_FORMAT(appointment_date, "%Y-%m") BETWEEN ? AND ?';
        $params[] = $start_month;
        $params[] = $end_month;
    } else {
        $where = 'WHERE DATE_FORMAT(appointment_date, "%Y-%m") = ?';
        $params[] = $filter_value;
    }
} elseif ($filter_type === 'year') {
    if (!empty($_GET['start_year']) && !empty($_GET['end_year'])) {
        $where = 'WHERE YEAR(appointment_date) BETWEEN ? AND ?';
        $params[] = $start_year;
        $params[] = $end_year;
    } else {
        $where = 'WHERE YEAR(appointment_date) = ?';
        $params[] = $filter_value;
    }
}

$sql = "SELECT * FROM appointments $where ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build the print URL with current filter parameters
$print_url = "print_appointment_report.php?filter_type=" . urlencode($filter_type);
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
    <title>Appointment Report</title>
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
        .filter-inputs {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-form button,
        .filter-form a.print-btn {
            margin-bottom: 0 !important;
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
        @media (max-width: 900px) {
            .main-content { padding: 16px 2vw; }
            table, th, td { font-size: 0.97em; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <h1>Appointment Report</h1>
    <form method="get" class="filter-form">
        <label for="filter_type">Filter by:</label>
        <select name="filter_type" id="filter_type" onchange="updateFilterInput()">
            <option value="day" <?= $filter_type === 'day' ? 'selected' : '' ?>>Day</option>
            <option value="range" <?= $filter_type === 'range' ? 'selected' : '' ?>>Date Range</option>
            <option value="month" <?= $filter_type === 'month' ? 'selected' : '' ?>>Month/Range</option>
            <option value="year" <?= $filter_type === 'year' ? 'selected' : '' ?>>Year/Range</option>
        </select>
        <span id="filter-input" class="filter-inputs">
            <?php if ($filter_type === 'day'): ?>
                <input type="date" name="filter_value" value="<?= htmlspecialchars($filter_value) ?>">
            <?php elseif ($filter_type === 'range'): ?>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                <span style="margin: 0 6px;">to</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <?php elseif ($filter_type === 'month'): ?>
                <input type="month" name="start_month" value="<?= htmlspecialchars($start_month) ?>">
                <span style="margin: 0 6px;">to</span>
                <input type="month" name="end_month" value="<?= htmlspecialchars($end_month) ?>">
            <?php elseif ($filter_type === 'year'): ?>
                <input type="number" name="start_year" min="2000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($start_year) ?>">
                <span style="margin: 0 6px;">to</span>
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
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="9">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><?= htmlspecialchars($appt['appointment_id']) ?></td>
                            <td><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></td>
                            <td><?= htmlspecialchars($appt['type_of_visit']) ?></td>
                            <td><?= htmlspecialchars($appt['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                            <td><?= htmlspecialchars($appt['contact_number']) ?></td>
                            <td><?= htmlspecialchars($appt['service_name'] ?? 'Not specified') ?></td>
                            <td>₱<?= number_format($appt['price'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($appt['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
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
        filterInput.innerHTML = '<input type="date" name="start_date" value="<?= date('Y-m-d') ?>"> <span style="margin:0 6px;">to</span> <input type="date" name="end_date" value="<?= date('Y-m-d') ?>">';
    } else if (type === 'month') {
        filterInput.innerHTML = '<input type="month" name="start_month" value="<?= date('Y-m') ?>"> <span style="margin:0 6px;">to</span> <input type="month" name="end_month" value="<?= date('Y-m') ?>">';
    } else if (type === 'year') {
        filterInput.innerHTML = '<input type="number" name="start_year" min="2000" max="' + today.getFullYear() + '" value="<?= date('Y') ?>"> <span style="margin:0 6px;">to</span> <input type="number" name="end_year" min="2000" max="' + today.getFullYear() + '" value="<?= date('Y') ?>">';
    }
}
</script>
</body>
</html>