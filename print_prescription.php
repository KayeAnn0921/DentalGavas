<?php
include 'config.php'; // Include the database configuration

$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    die("Patient ID is required.");
}

// Fetch patient details
try {
  $stmt = $pdo->prepare("SELECT first_name, last_name, home_address, 
                                FLOOR(DATEDIFF(CURDATE(), birthdate) / 365) AS age 
                         FROM patients WHERE patient_id = ?");
  $stmt->execute([$patient_id]);
  $patient = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$patient) {
      die("Patient not found.");
  }
} catch (PDOException $e) {
  die("Error fetching patient details: " . $e->getMessage());
}

// Fetch prescriptions for the patient
try {
    $stmt = $pdo->prepare("SELECT medications.name AS medicine, prescription.sig, prescription.quantity 
                           FROM prescription
                           JOIN medications ON prescription.med_id = medications.id 
                           WHERE prescription.patient_id = ?");
    $stmt->execute([$patient_id]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching prescriptions: " . $e->getMessage());
}
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
    @media print {
      .back-btn, .print-btn {
        display: none;
      }
    }
  </style>
</head>
<body>
<a href="prescription.php?id=<?= $patient_id ?>" class="back-btn">← Back</a>
<div class="rx-container">
  <div class="clinic-info">
    <strong>Dr. Glenn E. Gavas, DMD</strong><br>
    Gavas Dental Clinic<br>
    Door 1, Ferrer Building, Saging Street, General Santos City<br>
    Phone: (083) 123-4567
  </div>

  <div class="field"><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></div>
  <div class="field"><strong>Age:</strong> <?= htmlspecialchars($patient['age'])?></div>
  <div class="field"><strong>Date:</strong> <?= date("M d, Y") ?></div>
  <div class="field"><strong>Address:</strong> <?= htmlspecialchars($patient['home_address']) ?></div>

  <div class="rx-content">
    <div class="rx-symbol">℞</div>
    <?php if (!empty($prescriptions)): ?>
      <?php foreach ($prescriptions as $rx): ?>
        <div class="field"><strong>Drug:</strong> <?= htmlspecialchars($rx["medicine"]) ?></div>
        <div class="field"><strong>Tabs No.:</strong> <?= htmlspecialchars($rx["quantity"] ?? 'N/A') ?></div>
        <div class="field"><strong>Sig:</strong> <?= htmlspecialchars($rx["sig"]) ?></div>
        <hr>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="field">No prescriptions found for this patient.</div>
    <?php endif; ?>
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