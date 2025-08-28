<?php
include 'config.php';

$search = $_GET['search'] ?? '';
$result = null;
$prescription = [];
$view_patient = null;

// If viewing a specific patient
if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
    // Fetch patient info
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $view_patient = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch prescription for this patient
$presc_stmt = $pdo->prepare("
    SELECT p.prescription_id, p.sig, p.quantity, p.med_id, p.patient_id, m.name
    FROM prescription p
    INNER JOIN medications m ON p.med_id = m.id
    WHERE p.patient_id = ?
");
$presc_stmt->execute([$patient_id]);
$prescription = $presc_stmt->fetchAll(PDO::FETCH_ASSOC);
    // Only show this patient in the table
    $result = new ArrayObject([$view_patient]);
} else {
    // Patient list (with optional search)
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ?");
        $searchWildcard = "%$search%";
        $stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard]);
        $result = $stmt;
    } else {
        $stmt = $pdo->query("SELECT * FROM patients");
        $result = $stmt;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prescription List | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/listprescription.css"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<section class="patient-list-section">
    <div class="list-header">
        <h3>Patient List</h3>
        <form method="GET" action="prescription.php" style="display:flex; gap:10px;">
            <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="prescription.php" class="clear-search">Clear</a>
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
              <?php
$hasRows = false;
if ($result instanceof PDOStatement) {
    $hasRows = $result->rowCount() > 0;
} elseif ($result instanceof ArrayObject) {
    $hasRows = count($result) > 0 && $result[0];
}
?>
<?php if ($hasRows): ?>
    <?php foreach ($result as $row): ?>
        <tr>
           <td><?= $row['patient_id'] ?></td>
            <td><?= $row['last_name'] ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?></td>
            <td><?= $row['home_address'] ?></td>
            <td><?= $row['mobile_number'] ?></td>
           <td class="action-icons">
                <a href="prescription.php?patient_id=<?= $row['patient_id'] ?>" title="View"><i class="fas fa-eye"></i></a>
                <a href="add_prescription.php?patient_id=<?= $row['patient_id'] ?>" title="Prescription"><i class="fas fa-notes-medical"></i></a>
                <a href="print_prescription.php?id=<?= $row['patient_id'] ?>" title="Print" target="_blank"><i class="fas fa-print"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="5">No patients found.</td></tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>

<?php if (isset($_GET['patient_id'])): ?>
    <?php if (empty($prescription)): ?>
        <p>No prescription found for this patient.</p>
    <?php else: ?>
        <div style="margin-top:30px; margin-bottom:10px;">
            <h3>
                Prescription for 
                <?= htmlspecialchars($view_patient['last_name'] . ', ' . $view_patient['first_name'] . ' ' . $view_patient['middle_name']) ?>
            </h3>
        </div>
        <table class="prescription-table">
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Sig</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prescription as $presc): ?>
                    <tr>
                        <td><?= htmlspecialchars($presc['name']) ?></td>
                        <td><?= htmlspecialchars($presc['sig']) ?></td>
                        <td><?= htmlspecialchars($presc['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
</section>
</body>
</html>