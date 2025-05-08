<?php
include 'config.php'; // Include the database configuration

// Fetch patients
try {
    $stmt = $pdo->query("SELECT patient_id, CONCAT(last_name, ', ', first_name, ' ', middle_name) AS full_name FROM patients ORDER BY last_name, first_name");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching patients: " . $e->getMessage());
}

// Fetch services for a selected patient
$services = [];
if (isset($_GET['patient_id']) && !empty($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    try {
        $stmt = $pdo->prepare("SELECT s.name, s.price 
                               FROM patient_services ps 
                               JOIN services s ON ps.service_id = s.service_id 
                               WHERE ps.patient_id = ?");
        $stmt->execute([$patient_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching services: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $amount_paid = $_POST['amount_paid'];
    $discount = $_POST['discount'] ?? 0;

    // Calculate total price of services
    try {
        $stmt = $pdo->prepare("SELECT SUM(s.price) AS total_price 
                               FROM patient_services ps 
                               JOIN services s ON ps.service_id = s.service_id 
                               WHERE ps.patient_id = ?");
        $stmt->execute([$patient_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_price = $result['total_price'] ?? 0;

        // Apply discount and calculate balance
        $total = $total_price - $discount;
        $balance = $total - $amount_paid;

        // Insert into billing table
        $stmt = $pdo->prepare("INSERT INTO billing (patient_id, total, discount, amount_paid, balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $total, $discount, $amount_paid, $balance]);

        $success_message = "Billing record saved successfully!";
    } catch (PDOException $e) {
        die("Error saving billing record: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashiering</title>
  <style>
    /* Existing styles */
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 0;
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      margin: auto;
      padding: 20px;
      max-width: 800px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }

    .form-group {
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }

    .form-group label {
      display: inline-block;
      width: 150px;
      font-weight: 500;
      color: #34495e;
    }

    .form-group input,
    .form-group select {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 15px;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }

    .btn-group {
      text-align: center;
      margin-top: 25px;
    }

    button {
      padding: 10px 25px;
      margin: 0 10px;
      cursor: pointer;
      border: none;
      border-radius: 4px;
      font-weight: 500;
      font-size: 15px;
      transition: all 0.2s;
    }

    button:nth-child(1) {
      background-color: #95a5a6;
      color: white;
    }

    button:nth-child(1):hover {
      background-color: #7f8c8d;
    }

    button:nth-child(2) {
      background-color: #3498db;
      color: white;
    }

    button:nth-child(2):hover {
      background-color: #2980b9;
    }

    .bills-container {
      margin-top: 20px;
    }

    .bills-container table {
      width: 100%;
      border-collapse: collapse;
    }

    .bills-container th, .bills-container td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    .bills-container th {
      background-color: #f4f4f4;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="container">
    <h2>Cashiering</h2>

    <?php if (!empty($success_message)): ?>
      <div class="success-message" style="color: green; text-align: center;"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
      <div class="form-group">
        <label for="patient_id">Select Patient:</label>
        <select name="patient_id" id="patient_id" required onchange="fetchServices(this.value)">
          <option value="">-- Select Patient --</option>
          <?php foreach ($patients as $patient): ?>
            <option value="<?= htmlspecialchars($patient['patient_id']) ?>" <?= isset($_GET['patient_id']) && $_GET['patient_id'] == $patient['patient_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($patient['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Services:</label>
        <ul id="services-list">
          <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
              <li><?= htmlspecialchars($service['name']) ?> - ₱<?= number_format($service['price'], 2) ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No services found for this patient.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="form-group">
        <label for="amount_paid">Amount Paid:</label>
        <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="0" required>
      </div>

      <div class="form-group">
        <label for="discount">Discount:</label>
        <input type="number" name="discount" id="discount" step="0.01" min="0">
      </div>

      <div class="btn-group">
        <button type="submit">Save Bill</button>
      </div>
    </form>
  </div>
</div>

<script>
  function fetchServices(patientId) {
    if (patientId) {
      window.location.href = `cashiering.php?patient_id=${patientId}`;
    }
  }
</script>

</body>
</html>