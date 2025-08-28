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
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f7fafd;
        margin: 0;
        padding: 0;
    }
    
    .patient-list-section {
        width: 95%;
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(30,136,229,0.07);
    }
    
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .list-header h3 {
        color: #1976d2;
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0;
    }
    
    .search-box {
        padding: 10px 12px;
        font-size: 1rem;
        border: 1px solid #d1d9e6;
        border-radius: 6px;
        background: #f8fafc;
        min-width: 200px;
        flex-grow: 1;
        transition: border 0.2s;
    }
    
    .search-box:focus {
        border: 1.5px solid #1976d2;
        outline: none;
        background: #fff;
    }
    
    .list-header button {
        background: #1976d2;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px 18px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .list-header button:hover {
        background: #1256a3;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 20px;
    }
    
    .table-container table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        min-width: 600px;
    }
    
    .table-container th, .table-container td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #e6eaf0;
        font-size: 0.95rem;
        vertical-align: middle;
    }
    
    .table-container th {
        background: #f6f8fa;
        font-weight: 600;
        color: #2d3a4b;
    }
    
    .table-container tr:hover td {
        background-color: #f0f4fa;
        transition: background 0.2s;
    }
    
    .action-icons {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
        align-items: center;
    }
    
    .action-icons a {
        color: #1976d2;
        background: #e3e8ee;
        border-radius: 6px;
        padding: 6px 8px;
        transition: background 0.18s, color 0.18s;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .action-icons a:hover {
        background: #1976d2;
        color: #fff;
    }
    
    .action-icons i {
        font-size: 1rem;
    }
    
    .patient-details {
        width: 95%;
        max-width: 800px;
        margin: 20px auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(25, 118, 210, 0.07);
        padding: 25px 20px;
    }
    
    .patient-header {
        border-bottom: 1.5px solid #e3e8ee;
        margin-bottom: 15px;
        padding-bottom: 10px;
    }
    
    .patient-header h2 {
        color: #1976d2;
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .patient-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        color: #607d8b;
        font-size: 0.95rem;
    }
    
    .patient-meta i {
        margin-right: 5px;
        color: #1976d2;
    }
    
    .dental-charts h3 {
        color: #1976d2;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .chart {
        background: #f8fafc;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.04);
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .chart-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        font-size: 0.95rem;
        margin-bottom: 8px;
        color: #333;
        gap: 10px;
    }
    
    .chart-description {
        font-size: 0.95rem;
        margin-bottom: 10px;
        color: #444;
    }
    
    .tooth-conditions h4 {
        color: #1976d2;
        font-size: 0.95rem;
        margin-bottom: 8px;
        margin-top: 10px;
    }
    
    .tooth-conditions table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 0.9rem;
    }
    
    .tooth-conditions th, .tooth-conditions td {
        padding: 8px;
        border-bottom: 1px solid #e6eaf0;
        text-align: left;
    }
    
    .tooth-conditions th {
        background: #f6f8fa;
        font-weight: 600;
        color: #2d3a4b;
    }
    
    .no-charts {
        color: #c62828;
        font-weight: 500;
        margin-top: 15px;
    }
    
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
    }
    
    .btn-primary {
        background: #1976d2;
        color: #fff;
    }
    
    .btn-primary:hover {
        background: #1256a3;
    }
    
    .btn-secondary {
        background: #e3e8ee;
        color: #1976d2;
    }
    
    .btn-secondary:hover {
        background: #cfd8dc;
        color: #1256a3;
    }
    
    @media (max-width: 768px) {
        .patient-list-section {
            padding: 15px;
        }
        
        .list-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-box {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .patient-details {
            padding: 15px;
        }
        
        .patient-meta {
            flex-direction: column;
            gap: 8px;
        }
        
        .chart-header {
            flex-direction: column;
            gap: 5px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .table-container th, 
        .table-container td,
        .tooth-conditions th,
        .tooth-conditions td {
            padding: 8px 5px;
            font-size: 0.85rem;
        }
        
        .action-icons a {
            padding: 5px;
        }
        
        .patient-header h2 {
            font-size: 1.2rem;
        }
    }
  </style>
</head>
<body>
<section class="patient-list-section">
    <?php if ($patient): ?>
        <!-- Patient Details View -->
      <div class="patient-details">
    <div class="patient-header">
        <h2>
            <i class="fas fa-user-injured"></i>
            <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
        </h2>
        <div class="patient-meta">
            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($patient['home_address']) ?></span>
            <span><i class="fas fa-phone"></i> <?= htmlspecialchars($patient['mobile_number']) ?></span>
        </div>
    </div>
    <?php if (!empty($dentalCharts)): ?>
        <div class="dental-charts">
            <h3><i class="fas fa-notes-medical"></i> Dental Charts History</h3>
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
                        <h4>Tooth Conditions</h4>
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
        <p class="no-charts">No dental charts found for this patient.</p>
    <?php endif; ?>
    <div class="action-buttons">
        <a href="toothchart.php?patient_id=<?= $patient_id ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Chart</a>
        <a href="diagnosis.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Patient List</a>
    </div>
</div>
    <?php else: ?>
        <!-- Patient List View -->
        <div class="list-header">
            <h3>Patient List</h3>
            <form method="GET" action="diagnosis.php" class="search-form">
                <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="diagnosis.php" class="btn btn-secondary">Clear</a>
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