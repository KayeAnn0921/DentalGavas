<?php
require 'config.php';

$patient_id = $_GET['patient_id'] ?? '';

if (empty($patient_id)) {
    die("Invalid patient ID.");
}

// Fetch latest billing record
$stmt = $pdo->prepare("SELECT * FROM billing WHERE patient_id = ? ORDER BY billing_id DESC LIMIT 1");
$stmt->execute([$patient_id]);
$billing = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$billing) {
    die("No billing record found.");
}

// Get patient full name
$stmt = $pdo->prepare("SELECT CONCAT(last_name, ', ', first_name, ' ', middle_name) AS full_name FROM patients WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
$full_name = $patient['full_name'] ?? 'N/A';

// Fetch all services
$stmt = $pdo->prepare("SELECT s.name, s.price FROM patient_services ps JOIN services s ON ps.service_id = s.service_id WHERE ps.patient_id = ?");
$stmt->execute([$patient_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Receipt</title>
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
    .print-button, .back-button {
      text-align: center;
      margin-top: 20px;
    }
    button, .btn-link {
      background: #1976d2;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      margin: 5px;
    }
    .footer-note {
      text-align: center;
      font-size: 0.95em;
      color: #444;
      margin-top: 30px;
    }
    @media print {
      .print-button, .back-button {
        display: none;
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

  <div class="report-title">Payment Receipt</div>

  <div class="date-range">
    Receipt No.: <b>#<?= $billing['billing_id'] ?></b><br>
    Patient: <b><?= htmlspecialchars($full_name) ?></b><br>
    Patient ID: <b><?= htmlspecialchars($patient_id) ?></b><br>
    Payment Date: <b><?= date('F j, Y', strtotime($billing['created_at'])) ?></b>
  </div>

  <table>
    <thead>
      <tr>
        <th>Service</th>
        <th>Price (₱)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $service): ?>
        <tr>
          <td><?= htmlspecialchars($service['name']) ?></td>
          <td><?= number_format($service['price'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th>Subtotal</th>
        <td><?= number_format($billing['total'] + $billing['discount'], 2) ?></td>
      </tr>
      <tr>
        <th>Discount (<?= ucfirst($billing['discount_type']) ?>)</th>
        <td>-<?= number_format($billing['discount'], 2) ?></td>
      </tr>
      <tr>
        <th>Total Paid</th>
        <td><strong>₱<?= number_format($billing['amount_paid'], 2) ?></strong></td>
      </tr>
      <tr>
        <th>Balance</th>
        <td>₱<?= number_format($billing['balance'], 2) ?></td>
      </tr>
      <?php if (!empty($billing['discount_id'])): ?>
      <tr>
        <th>Discount ID</th>
        <td><?= htmlspecialchars($billing['discount_id']) ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <th>Payment Type</th>
        <td><?= ucfirst(str_replace('_', ' ', $billing['payment_type'])) ?></td>
      </tr>
    </tfoot>
  </table>

  <div class="signature" style="text-align:right;margin-top:60px;font-size:1.1em;">
    <strong>Dr. Glenn E. Gavas, DMD</strong><br>
    DEA No. 1234563<br>
    State License No. 65432
  </div>

  <div class="footer-note">
    <em>Thank you for choosing Gavas Dental Clinic!<br>
    This receipt serves as an official proof of payment.</em>
  </div>
</div>

<div class="back-button">
  <a href="cashiering.php" class="btn-link">← Back to Cashiering</a>
  <button onclick="window.print()">🖨️ Print Receipt</button>
</div>
</body>
</html>
