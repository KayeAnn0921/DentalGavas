<?php
include 'config.php'; // Include the database configuration

// Fetch medications from the database
try {
    $stmt = $pdo->query("SELECT id, name FROM medications ORDER BY name");
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching medications: " . $e->getMessage());
}

// Fetch patient details from the database
$patientId = $_GET['patient_id'] ?? 0;
try {
    $stmt = $pdo->prepare("SELECT first_name FROM patients WHERE patient_id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    $patientName = $patient ? $patient['first_name'] : "Unknown Patient";
} catch (PDOException $e) {
    die("Error fetching patient details: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $medicines = $_POST['medicines'] ?? [];
    $sigs = $_POST['sig'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    try {
        $pdo->beginTransaction();

        foreach ($medicines as $index => $medicineId) {
            if (!empty($medicineId) && !empty($sigs[$index]) && !empty($quantities[$index])) {
                $stmt = $pdo->prepare("INSERT INTO prescription (patient_id, med_id, sig, quantity) VALUES (?, ?, ?, ?)");
                $stmt->execute([$patientId, $medicineId, $sigs[$index], $quantities[$index]]);
            }
        }

        $pdo->commit();
        header("Location: prescription.php"); // Redirect back to prescription_list.php
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error saving prescription: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Enter Prescription</title>
  <link rel="stylesheet" href="css/prescription.css"/>
  <style>
    .prescription-form { max-width: 600px; margin: auto; }
    .prescription-form label { display: block; margin-top: 10px; }
    .medicine-group { border: 1px solid #ccc; padding: 10px; margin-top: 10px; }
    button { margin-top: 10px; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<a href="prescription_list.php" class="back-btn">← Back</a>
<div class="prescription-form">
  <h2>Prescription for: <?= htmlspecialchars($patientName) ?></h2>
  <form action="" method="POST" id="prescriptionForm">
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">

    <div id="medicineContainer">
  <div class="medicine-group">
    <label>Medicine:
      <select name="medicines[]">
        <option value="">-- Select Medicine --</option>
        <?php foreach ($medications as $medication): ?>
          <option value="<?= htmlspecialchars($medication['id']) ?>">
            <?= htmlspecialchars($medication['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>SIG (Directions): <input type="text" name="sig[]"></label>
    <label>Tab Quantity: <input type="number" name="quantity[]" min="1"></label>
  </div>
</div>
<button type="button" onclick="addMedicine()">+ Add Another Medicine</button>
    <br><br>
    <button type="submit">Save</button>
    <a href="print_prescription.php?id=<?= $patientId ?>" class="print-btn" target="_blank">Print</a>
  </form>
</div>

<script>
function addMedicine() {
  const container = document.getElementById("medicineContainer");
  const newGroup = document.createElement("div");
  newGroup.className = "medicine-group";
  newGroup.innerHTML = `
    <label>Medicine:
      <select name="medicines[]">
        <option value="">-- Select Medicine --</option>
        <?php foreach ($medications as $medication): ?>
          <option value="<?= htmlspecialchars($medication['id']) ?>">
            <?= htmlspecialchars($medication['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>SIG (Directions): <input type="text" name="sig[]"></label>
    <label>Tab Quantity: <input type="number" name="quantity[]" min="1"></label>
  `;
  container.appendChild(newGroup);
}
</script>
</body>
</html>