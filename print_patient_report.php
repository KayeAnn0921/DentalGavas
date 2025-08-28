<?php
require_once 'config.php';

// Copy filter logic from above
$filter_type = $_GET['filter_type'] ?? 'daily';
$filter_value = $_GET['filter_value'] ?? date('Y-m-d');
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$start_month = $_GET['start_month'] ?? date('Y-m');
$end_month = $_GET['end_month'] ?? date('Y-m');
$start_year = $_GET['start_year'] ?? date('Y');
$end_year = $_GET['end_year'] ?? date('Y');

$where = '';
$params = [];

if ($filter_type === 'daily') {
    $where = 'WHERE visit_date = ?';
    $params[] = $filter_value;
} elseif ($filter_type === 'range') {
    $where = 'WHERE visit_date BETWEEN ? AND ?';
    $params[] = $start_date;
    $params[] = $end_date;
} elseif ($filter_type === 'monthly') {
    if (!empty($_GET['start_month']) && !empty($_GET['end_month'])) {
        $where = 'WHERE DATE_FORMAT(visit_date, "%Y-%m") BETWEEN ? AND ?';
        $params[] = $start_month;
        $params[] = $end_month;
    } else {
        $where = 'WHERE DATE_FORMAT(visit_date, "%Y-%m") = ?';
        $params[] = $filter_value;
    }
} elseif ($filter_type === 'annual') {
    if (!empty($_GET['start_year']) && !empty($_GET['end_year'])) {
        $where = 'WHERE YEAR(visit_date) BETWEEN ? AND ?';
        $params[] = $start_year;
        $params[] = $end_year;
    } else {
        $where = 'WHERE YEAR(visit_date) = ?';
        $params[] = $filter_value;
    }
}

$sql = "SELECT p.patient_id, p.last_name, p.first_name, p.middle_name, p.visit_date, 
               GROUP_CONCAT(s.name SEPARATOR ', ') as services
        FROM patients p
        LEFT JOIN patient_services ps ON p.patient_id = ps.patient_id
        LEFT JOIN services s ON ps.service_id = s.service_id
        $where
        GROUP BY p.patient_id, p.visit_date
        ORDER BY p.visit_date DESC, p.last_name, p.first_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Patient Report</title>
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
    <div class="report-title">Patient Report</div>
    <div class="date-range">
        <?php
        if ($filter_type === 'daily') {
            echo "For: <b>" . htmlspecialchars(date('F d, Y', strtotime($filter_value))) . "</b>";
        } elseif ($filter_type === 'range') {
            echo "From <b>" . htmlspecialchars(date('F d, Y', strtotime($start_date))) . "</b> to <b>" . htmlspecialchars(date('F d, Y', strtotime($end_date))) . "</b>";
        } elseif ($filter_type === 'monthly') {
            if (!empty($_GET['start_month']) && !empty($_GET['end_month'])) {
                echo "From <b>" . htmlspecialchars(date('F Y', strtotime($start_month))) . "</b> to <b>" . htmlspecialchars(date('F Y', strtotime($end_month))) . "</b>";
            } else {
                echo "For: <b>" . htmlspecialchars(date('F Y', strtotime($filter_value))) . "</b>";
            }
        } elseif ($filter_type === 'annual') {
            if (!empty($_GET['start_year']) && !empty($_GET['end_year'])) {
                echo "From <b>" . htmlspecialchars($start_year) . "</b> to <b>" . htmlspecialchars($end_year) . "</b>";
            } else {
                echo "For: <b>" . htmlspecialchars($filter_value) . "</b>";
            }
        }
        ?>
    </div>
    <table>
        <thead>
            <tr>
                <th>Visit Date</th>
                <th>Patient Name</th>
                <th>Services Availed</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($patients)): ?>
                <tr><td colspan="3">No records found.</td></tr>
            <?php else: ?>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['visit_date']) ?></td>
                        <td><?= htmlspecialchars($p['last_name'] . ', ' . $p['first_name'] . ' ' . $p['middle_name']) ?></td>
                        <td><?= htmlspecialchars($p['services']) ?></td>
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