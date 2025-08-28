<?php
include 'config.php';
include 'sidebar.php';

// Get total number of patients
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM patients");
$totalPatients = $stmt->fetchColumn();

// Get total collections
$stmt = $pdo->query("SELECT SUM(amount_paid) AS total FROM billing");
$totalCollections = $stmt->fetchColumn() ?? 0;

// Get total appointments
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM appointments");
$totalAppointments = $stmt->fetchColumn();

// Get total cancelled appointments
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'Cancelled'");
$totalCancelled = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Clinic Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fafc;
      color: #2c3e50;
    }

    .main-content {
      margin-left: 250px;
      padding: 30px;
      box-sizing: border-box;
    }

    h1 {
      font-size: 28px;
      margin-bottom: 25px;
      color: #004aad;
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .card {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 12px;
      border-left: 5px solid #004aad;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .card h2 {
      font-size: 16px;
      margin-bottom: 8px;
      color: #7f8c8d;
    }

    .card p {
      font-size: 26px;
      font-weight: bold;
      color: #2c3e50;
    }

    .stat-up {
      color: #16a085;
      font-size: 14px;
    }

    .stat-down {
      color: #e74c3c;
      font-size: 14px;
    }

    .view-link {
      display: inline-block;
      margin-top: 10px;
      font-size: 13px;
      color: #004aad;
      text-decoration: none;
    }

    .view-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      .main-content {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h1>Clinic Dashboard</h1>
    <div class="card-grid">
      <!-- Total Patients -->
      <div class="card">
        <h2>Total Patients</h2>
        <p><?= $totalPatients ?></p>
        <div class="stat-up">+12 today</div>
        <a class="view-link" href="patient_report.php">View Patients</a>
      </div>

      <!-- Total Collections -->
      <div class="card">
        <h2>Total Collections</h2>
        <p>₱<?= number_format($totalCollections, 2) ?></p>
        <div class="stat-up">+ ₱1,230 today</div>
        <a class="view-link" href="collection_report.php">View Report</a>
      </div>

      <!-- Total Appointments -->
      <div class="card">
        <h2>Total Appointments</h2>
        <p><?= $totalAppointments ?></p>
        <div class="stat-up">+8 scheduled</div>
        <a class="view-link" href="appointment_report.php">View Appointments</a>
      </div>

      <!-- Cancelled Appointments -->
      <div class="card">
        <h2>Cancelled Appointments</h2>
        <p><?= $totalCancelled ?></p>
        <div class="stat-down">-2 today</div>
        <a class="view-link" href="appointment_report.php">View Log</a>
      </div>
    </div>
  </div>
</body>
</html>
