<?php
$patient_id = $_GET['id'] ?? null;

// Dummy data – you should pull this from your database based on `$patient_id`
$patient = [
  "name" => "Mary Smith",
  "age" => "35",
  "gender" => "Female",
  "address" => "123 Broad Street"
];

// Dummy prescription array – replace with database query
$prescriptions = [
  ["medicine" => "Lipitor 10 mg", "sig" => "tab i every day", "quantity" => "30", "refill" => "6"],
  // add more if needed
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Prescription Print View</title>
  <style>
    body {
      font-family: Georgia, serif;
      margin: 40px;
      background-color: #fefefe;
    }
    .rx-container {
      max-width: 700px;
      border: 2px solid #0074cc;
      padding: 40px;
      border-radius: 8px;
      position: relative;
    }
    h2, .clinic-info {
      text-align: center;
    }
    .clinic-info {
      margin-bottom: 20px;
    }
    .rx-symbol {
      font-size: 32px;
      font-weight: bold;
      color: #000;
    }
    .rx-content {
      margin-top: 20px;
    }
    .field {
      margin-bottom: 10px;
    }
    .field strong {
      width: 80px;
      display: inline-block;
    }
    .signature {
      text-align: right;
      margin-top: 60px;
    }
    .label-group {
      margin-top: 30px;
    }
    .back-btn {
      margin-bottom: 20px;
      display: inline-block;
      text-decoration: none;
      color: #0074cc;
      font-weight: bold;
    }
    .print-btn {
      margin-top: 30px;
    }
  </style>
</head>
<body>
<a href="view_prescription.php?id=<?= $patient_id ?>" class="back-btn">← Back</a>
<div class="rx-container">
  <div class="clinic-info">
    <strong>Dr. Glenn E. Gavas, DMD</strong><br>
    Gavas Dental Clinic<br>
    Door 1, Ferrer Building, Saging Street, General Santos City<br>
    Phone: (083) 123-4567
  </div>

  <div class="field"><strong>Name:</strong> <?= htmlspecialchars($patient["name"]) ?></div>
  <div class="field"><strong>Date:</strong> <?= date("M d, Y") ?></div>
  <div class="field"><strong>Address:</strong> <?= htmlspecialchars($patient["address"]) ?></div>

  <div class="rx-content">
    <div class="rx-symbol">℞</div>
    <?php foreach ($prescriptions as $rx): ?>
      <div class="field"><strong>Drug:</strong> <?= htmlspecialchars($rx["medicine"]) ?></div>
      <div class="field"><strong>Tabs No.:</strong> <?= htmlspecialchars($rx["quantity"]) ?></div>
      <div class="field"><strong>Sig:</strong> <?= htmlspecialchars($rx["sig"]) ?></div>
      <div class="field"><strong>Refill:</strong> <?= htmlspecialchars($rx["refill"]) ?> times</div>
      <hr>
    <?php endforeach; ?>
  </div>

  <div class="label-group">
    Label: Yes ☐ No ☐<br>
    Generic if available: Yes ☐ No ☐
  </div>

  <div class="signature">
    <strong>Dr. Glenn E. Gavas, DMD</strong><br>
    DEA No. 1234563<br>
    State License No. 65432
  </div>
</div>

<script>
  window.print();
</script>
</body>
</html>
