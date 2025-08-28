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
    $directions = $_POST['direction'] ?? [];

    try {
        $pdo->beginTransaction();

        foreach ($medicines as $index => $medicineId) {
          if (!empty($medicineId) && !empty($sigs[$index]) && !empty($directions[$index]) && !empty($quantities[$index])) {
              $stmt = $pdo->prepare("INSERT INTO prescription (patient_id, med_id, sig, direction, quantity) VALUES (?, ?, ?, ?, ?)");
              $stmt->execute([$patientId, $medicineId, $sigs[$index], $directions[$index], $quantities[$index]]);
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
    <label>Medicine
      <select name="medicines[]" required>
        <option value="">-- Select Medicine --</option>
        <?php foreach ($medications as $medication): ?>
          <option value="<?= htmlspecialchars($medication['id']) ?>">
            <?= htmlspecialchars($medication['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>SIG
      <input type="text" name="sig[]" placeholder="e.g. 1 tab" required>
    </label>
    <label>Direction
      <input type="text" name="direction[]" placeholder="e.g. every 8 hours" required>
    </label>
    <label>Tab Quantity
      <input type="number" name="quantity[]" min="1" placeholder="e.g. 10" required>
    </label>
    <button type="button" class="remove-btn" onclick="removeMedicine(this)">Remove</button>
  </div>
</div>
<button type="button" onclick="addMedicine()" class="add-btn">+ Add Another Medicine</button>
<button type="submit" class="save-btn">Save Prescription</button>
  </form>
</div>

<script>
function addMedicine() {
  const container = document.getElementById("medicineContainer");
  const newGroup = document.createElement("div");
  newGroup.className = "medicine-group";
  newGroup.innerHTML = `
    <label>Medicine
      <select name="medicines[]" required>
        <option value="">-- Select Medicine --</option>
        <?php foreach ($medications as $medication): ?>
          <option value="<?= htmlspecialchars($medication['id']) ?>">
            <?= htmlspecialchars($medication['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>SIG
      <input type="text" name="sig[]" placeholder="e.g. 1 tab" required>
    </label>
    <label>Direction
      <input type="text" name="direction[]" placeholder="e.g. every 8 hours" required>
    </label>
    <label>Tab Quantity
      <input type="number" name="quantity[]" min="1" placeholder="e.g. 10" required>
    </label>
    <button type="button" class="remove-btn" onclick="removeMedicine(this)">Remove</button>
  `;
  container.appendChild(newGroup);
}
function removeMedicine(btn) {
  btn.parentElement.remove();
}

// Hide the remove button for the first group on page load
window.onload = function() {
  const firstRemoveBtn = document.querySelector('#medicineContainer .medicine-group .remove-btn');
  if (firstRemoveBtn) firstRemoveBtn.style.display = 'none';
};
</script>
</body>
</html>