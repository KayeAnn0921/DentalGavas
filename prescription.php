<?php 
include 'config.php';

$search = $_GET['search'] ?? ''; // initialize $search

$result = null; // make sure it's always defined

if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $result = $stmt;
} else {
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
  <link rel="stylesheet" href="css/prescription.css"/>
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
                <?php if ($result && $result->rowCount() > 0): ?>

                    <?php foreach ($result as $row): ?>
                        <tr>
                            <td><?= $row['patient_id'] ?></td>
                            <td><?= $row['last_name'] ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?></td>
                            <td><?= $row['home_address'] ?></td>
                            <td><?= $row['mobile_number'] ?></td>
                            <td class="action-icons">
                                <a href="add_prescription.php?id=<?= $row['patient_id'] ?>" title="View"><i class="fas fa-eye"></i></a>
                                <a href="add_prescription.php?patient_id=<?= $row['patient_id'] ?>" title="Prescription"><i class="fas fa-notes-medical"></i></a>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No patients found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</body>
</html>
