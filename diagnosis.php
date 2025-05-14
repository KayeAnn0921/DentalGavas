<?php 
include 'config.php';
include 'sidebar.php';

$search = $_GET['search'] ?? '';
$patient_id = $_GET['patient_id'] ?? null;

// Fetch patient data
$patient = null;
$dentalCharts = [];

if ($patient_id) {
    $patient_id = intval($patient_id);
    
    // Fetch patient info
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($patient) {
        // Fetch dental charts for this patient
        $stmt = $pdo->prepare("SELECT * FROM dental_charts WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patient_id]);
        $dentalCharts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For each chart, fetch tooth conditions
        foreach ($dentalCharts as &$chart) {
            $stmt = $pdo->prepare("SELECT * FROM tooth_condition WHERE chart_id = ?");
            $stmt->execute([$chart['chart_id']]);
            $chart['conditions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($chart); // Break the reference
    }
} else {
    // Fetch all patients for the list
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ? ORDER BY last_name, first_name");
        $searchWildcard = "%$search%";
        $stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard]);
        $patients = $stmt;
    } else {
        $stmt = $pdo->query("SELECT * FROM patients ORDER BY last_name, first_name");
        $patients = $stmt;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Diagnosis List | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/listprescription.css"/>
  <style>
    .patient-details {
        margin-top: 30px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 5px;
    }
    
    .chart {
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .tooth-conditions {
        margin-top: 15px;
    }
    
    .tooth-conditions table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .tooth-conditions th, .tooth-conditions td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
    }
    
    .tooth-conditions th {
        background-color: #f2f2f2;
    }
    
    .btn {
        display: inline-block;
        padding: 8px 15px;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 10px;
        margin-right: 10px;
    }
    
    .btn:hover {
        background: #45a049;
    }
    
    .btn-back {
        background: #6c757d;
    }
    
    .btn-back:hover {
        background: #5a6268;
    }
  </style>
</head>
<body>
<section class="patient-list-section">
    <?php if ($patient): ?>
        <!-- Patient Details View -->
        <div class="patient-details">
            <h3>Patient Details: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h3>
            <p><strong>Address:</strong> <?= htmlspecialchars($patient['home_address']) ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($patient['mobile_number']) ?></p>
            
            <?php if (!empty($dentalCharts)): ?>
                <div class="dental-charts">
                    <h4>Dental Charts History</h4>
                    <?php foreach ($dentalCharts as $chart): ?>
                        <div class="chart">
                            <div class="chart-header">
                                <span><strong>Date:</strong> <?= htmlspecialchars(date('M d, Y', strtotime($chart['created_at']))) ?></span>
                                <span><strong>Service:</strong> <?= htmlspecialchars($chart['service']) ?></span>
                            </div>
                            <div class="chart-description">
                                <strong>Description:</strong> <?= htmlspecialchars($chart['description']) ?>
                            </div>
                            
                            <div class="tooth-conditions">
                                <h5>Tooth Conditions:</h5>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tooth Number</th>
                                            <th>Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($chart['conditions'] as $condition): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($condition['tooth_number']) ?></td>
                                                <td><?= htmlspecialchars($condition['condition_code']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No dental charts found for this patient.</p>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="toothchart.php?patient_id=<?= $patient_id ?>" class="btn">Add New Chart</a>
                <a href="diagnosis.php" class="btn btn-back">Back to Patient List</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Patient List View -->
        <div class="list-header">
            <h3>Patient List</h3>
            <form method="GET" action="diagnosis.php" style="display:flex; gap:10px;">
                <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="diagnosis.php" class="btn btn-back">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Patient Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($patients && $patients->rowCount() > 0): ?>
                        <?php foreach ($patients as $row): ?>
                            <tr>
                                <td><?= $row['patient_id'] ?></td>
                                <td><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']) ?></td>
                                <td><?= htmlspecialchars($row['home_address']) ?></td>
                                <td><?= htmlspecialchars($row['mobile_number']) ?></td>
                                <td class="action-icons">
                                    <a href="diagnosis.php?patient_id=<?= $row['patient_id'] ?>" title="View Diagnosis"><i class="fas fa-eye"></i></a>
                                    <a href="toothchart.php?patient_id=<?= $row['patient_id'] ?>" title="Add Chart"><i class="fas fa-plus"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No patients found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
</body>
</html>