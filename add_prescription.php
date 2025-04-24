<?php
$patientId = $_GET['id'] ?? 0;
// Example patient name retrieval
$patientName = "Juan Dela Cruz"; 
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
  <form action="save_prescription.php" method="POST" id="prescriptionForm">
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">

    <div id="medicineContainer">
      <div class="medicine-group">
        <label>Medicine:
          <select name="medicines[]">
            <option value="">-- Select Medicine --</option>
            <option value="Amoxicillin">Amoxicillin</option>
            <option value="Mefenamic Acid">Mefenamic Acid</option>
            <option value="Paracetamol">Paracetamol</option>
          </select>
        </label>
        <label>SIG (Directions): <input type="text" name="sig[]"></label>
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
        <option value="Amoxicillin">Amoxicillin</option>
        <option value="Mefenamic Acid">Mefenamic Acid</option>
        <option value="Paracetamol">Paracetamol</option>
      </select>
    </label>
    <label>SIG (Directions): <input type="text" name="sig[]"></label>
  `;
  container.appendChild(newGroup);
}
</script>
</body>
</html>
