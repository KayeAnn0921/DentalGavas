<?php
require_once 'config.php';

// Filter logic (copy from appointment_report.php)
$filter_type = $_GET['filter_type'] ?? 'day';
$filter_value = $_GET['filter_value'] ?? date('Y-m-d');

$where = '';
$params = [];

if ($filter_type === 'day') {
    $where = 'WHERE DATE(appointment_date) = ?';
    $params[] = $filter_value;
} elseif ($filter_type === 'month') {
    $where = 'WHERE DATE_FORMAT(appointment_date, "%Y-%m") = ?';
    $params[] = $filter_value;
} elseif ($filter_type === 'year') {
    $where = 'WHERE YEAR(appointment_date) = ?';
    $params[] = $filter_value;
}

$sql = "SELECT * FROM appointments $where ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Appointment Report</title>
    <style>
        body {
            font-family: Georgia, serif;
            background: #fff;
        }
        .report-container {
            max-width: 900px;
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
    <div class="report-title">Appointment Report</div>
    <div class="date-range">
        <?php
        if ($filter_type === 'day') {
            echo "For: <b>" . htmlspecialchars(date('F d, Y', strtotime($filter_value))) . "</b>";
        } elseif ($filter_type === 'month') {
            echo "For: <b>" . htmlspecialchars(date('F Y', strtotime($filter_value))) . "</b>";
        } elseif ($filter_type === 'year') {
            echo "For: <b>" . htmlspecialchars($filter_value) . "</b>";
        }
        ?>
    </div>
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
    <div class="signature" style="text-align:right;margin-top:60px;font-size:1.1em;">
        <strong>Dr. Glenn E. Gavas, DMD</strong><br>
        DEA No. 1234563<br>
        State License No. 65432
    </div>
</div>
</body>
</html>