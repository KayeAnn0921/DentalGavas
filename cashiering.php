<?php
include 'config.php';

try {
    $stmt = $pdo->query("SELECT patient_id, CONCAT(last_name, ', ', first_name, ' ', middle_name) AS full_name FROM patients ORDER BY last_name, first_name");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching patients: " . $e->getMessage());
}

// Fetch services for a selected patient
$services = [];
$total_price = 0;
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';
if (!empty($patient_id)) {
    try {
        $stmt = $pdo->prepare("SELECT s.name, s.price 
                               FROM patient_services ps 
                               JOIN services s ON ps.service_id = s.service_id 
                               WHERE ps.patient_id = ?");
        $stmt->execute([$patient_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($services as $service) {
            $total_price += (float)$service['price'];
        }
    } catch (PDOException $e) {
        die("Error fetching services: " . $e->getMessage());
    }
}

// Fetch discount types for dropdown
$discount_types = [
    'none' => 'None',
    'pwd' => 'PWD',
    'student' => 'Student',
    'health_insurance' => 'Health Insurance',
    'philhealth' => 'PhilHealth',
    'senior' => 'Senior Citizen'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $amount_paid = isset($_POST['amount_paid']) ? (float)$_POST['amount_paid'] : 0;
    $discount_type = $_POST['discount_type'] ?? 'none';
    $discount_id = $_POST['discount_id'] ?? null;
    $payment_type = $_POST['payment_type'] ?? 'fully_paid';
    $months = ($payment_type === 'staggered') ? (isset($_POST['months']) ? (int)$_POST['months'] : 1) : null;

    // Get total price for patient
    try {
        $stmt = $pdo->prepare("SELECT SUM(s.price) AS total_price 
                               FROM patient_services ps 
                               JOIN services s ON ps.service_id = s.service_id 
                               WHERE ps.patient_id = ?");
        $stmt->execute([$patient_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_price = isset($result['total_price']) ? (float)$result['total_price'] : 0;
    } catch (PDOException $e) {
        die("Error fetching total price: " . $e->getMessage());
    }

    // Fetch discount rate for the selected type and current date
    $discount_rate = 0;
    $today = date('Y-m-d');
    if ($discount_type !== 'none') {
        $stmt = $pdo->prepare("SELECT rate FROM discount_rates WHERE discount_type = ? AND start_date <= ? AND (end_date >= ? OR end_date IS NULL) ORDER BY start_date DESC LIMIT 1");
        $stmt->execute([$discount_type, $today, $today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $discount_rate = (float)$row['rate'];
        }
    }
   $discount = $total_price * ($discount_rate / 100);
    $total = $total_price - $discount;
    if ($total < 0) $total = 0;

    // Fix the balance calculation for staggered payments
    if ($payment_type === 'staggered') {
        $balance = $total - $amount_paid; // This is the remaining balance after initial payment
        if ($balance < 0) $balance = 0;
    } else {
        // For fully paid, balance should be zero if amount_paid >= total
        $balance = max(0, $total - $amount_paid);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO billing (patient_id, total, discount, amount_paid, balance, payment_type, months, discount_id, discount_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $total, $discount, $amount_paid, $balance, $payment_type, $months, $discount_id, $discount_type]);

        $success_message = "Billing record saved successfully!";
        header("Location: receipt.php?patient_id=" . urlencode($patient_id));
        exit;
    } catch (PDOException $e) {
        die("Error saving billing record: " . $e->getMessage());
    }
}

$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Billing record saved successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashiering</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <style>
    body {
      background: #e3f2fd;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .main-content {
      width: 1100px;
      margin: 40px auto 0 auto;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 24px rgba(25,118,210,0.10);
      padding: 32px 28px 28px 28px;
    }
    .container {
      width: 100%;
    }
    h2 {
      color: #1976d2;
      font-weight: 700;
      margin-bottom: 24px;
      text-align: center;
      letter-spacing: 1px;
    }
    .success-message {
      background: #e8f5e9;
      color: #388e3c;
      padding: 12px 18px;
      border-radius: 7px;
      margin-bottom: 18px;
      font-weight: 500;
      text-align: center;
    }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #1976d2; }
    .form-group input, .form-group select {
      width: 100%;
      padding: 9px 12px;
      border-radius: 7px;
      border: 1px solid #b6d4fe;
      font-size: 1em;
      background: #f7fbff;
      margin-bottom: 4px;
      transition: border 0.2s;
    }
    .form-group input:focus, .form-group select:focus {
      border: 1.5px solid #1976d2;
      outline: none;
      background: #e3f2fd;
    }
    .btn-group {
      margin-top: 18px;
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }
    .btn-group button {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 7px;
      padding: 10px 22px;
      font-size: 1.05em;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-group button[type="reset"] {
      background: #e5e7eb;
      color: #222;
    }
    .btn-group button:hover {
      background: #1256a3;
    }
    .btn-group button[type="reset"]:hover {
      background: #cbd5e1;
    }
    #months_group { display: none; }
    #monthly_payment_group { display: none; }
    ul#services-list {
      list-style: none;
      padding: 0;
      margin: 0;
      background: #f7fbff;
      border-radius: 7px;
      border: 1px solid #b6d4fe;
      margin-bottom: 8px;
    }
    ul#services-list li {
      display: flex;
      justify-content: space-between;
      padding: 7px 12px;
      border-bottom: 1px solid #e3f2fd;
      font-size: 1em;
    }
    ul#services-list li:last-child {
      border-bottom: none;
    }
    .installment-indicator {
      margin:18px 0 10px 0;
      background:#e3f2fd;
      border-radius:8px;
      padding:12px 18px;
      font-size: 1em;
    }
    .installment-indicator span {
      display:inline-block;
      min-width:110px;
      margin:2px 8px 2px 0;
      padding:4px 10px;
      border-radius:6px;
      font-weight:600;
      font-size:0.97em;
    }
    .installment-indicator .paid {
      background:#c8e6c9;
      color:#388e3c;
    }
    .installment-indicator .unpaid {
      background:#ffcdd2;
      color:#b71c1c;
    }
    @media (max-width: 700px) {
      .main-content { padding: 10px 2vw; }
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="container">
    <h2><i class="fa fa-cash-register" style="color:#4299e1"></i> Cashiering</h2>

    <?php if (!empty($success_message)): ?>
      <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
      <div class="form-group">
        <label for="patient_id"><i class="fa fa-user"></i> Select Patient:</label>
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
        <label><i class="fa fa-tooth"></i> Services:</label>
        <ul id="services-list">
          <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
              <li>
                <span><?= htmlspecialchars($service['name']) ?></span>
                <span>₱<?= number_format($service['price'], 2) ?></span>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No services found for this patient.</li>
          <?php endif; ?>
        </ul>
      </div>

      <?php
      // Installment indicator for latest staggered billing
    if (!empty($patient_id) && !empty($services)) {
        $stmt = $pdo->prepare("SELECT * FROM billing WHERE patient_id = ? AND months IS NOT NULL AND months > 1 ORDER BY billing_id DESC LIMIT 1");
        $stmt->execute([$patient_id]);
        $latestBill = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($latestBill) {
            $remainingBalance = $latestBill['total'] - $latestBill['amount_paid'];
            $perMonth = $remainingBalance / $latestBill['months'];
            $monthsPaid = floor($latestBill['amount_paid'] / $perMonth);
            $createdAt = new DateTime($latestBill['created_at']);
            echo "<div class='installment-indicator'><b>Installment Schedule:</b><br>";
            for ($i = 1; $i <= $latestBill['months']; $i++) {
                $monthDate = clone $createdAt;
                $monthDate->modify('+' . ($i-1) . ' months');
                $paid = ($i <= $monthsPaid);
                echo "<span class='" . ($paid ? "paid" : "unpaid") . "'>";
                echo "Month $i: " . $monthDate->format('M Y') . " " . ($paid ? "Paid" : "Unpaid");
                echo "</span>";
            }
            echo "</div>";
        }
    }
      ?>

      <div class="form-group">
        <label for="original_total"><i class="fa fa-calculator"></i> Original Total:</label>
        <input type="hidden" id="original_total" value="<?= isset($total_price) ? number_format($total_price, 2, '.', '') : '0.00' ?>">
        <input type="text" id="original_total_display" value="₱<?= number_format($total_price, 2) ?>" readonly>
      </div>

      <div class="form-group">
        <label for="discount_type"><i class="fa fa-percent"></i> Discount Type:</label>
        <select name="discount_type" id="discount_type" onchange="setDiscountAmount(); toggleIdField();">
          <?php foreach ($discount_types as $key => $label): ?>
            <option value="<?= $key ?>"><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" id="id_field_group" style="display:none;">
        <label for="discount_id" id="id_field_label"><i class="fa fa-id-card"></i> Discount ID Number:</label>
        <input type="text" name="discount_id" id="discount_id" maxlength="50" placeholder="Enter ID Number">
      </div>

      <div class="form-group">
        <label for="discount"><i class="fa fa-coins"></i> Discount Amount:</label>
        <input type="hidden" name="discount" id="discount" step="0.01" min="0" readonly>
        <input type="text" id="discount_display" value="₱0.00" readonly>
      </div>

      <div class="form-group">
        <label for="total_amount"><i class="fa fa-calculator"></i> Total Amount (After Discount):</label>
        <input type="hidden" name="total_amount" id="total_amount" value="<?= isset($total_price) ? number_format($total_price, 2, '.', '') : '0.00' ?>" readonly>
        <input type="text" id="total_amount_display" value="₱<?= number_format($total_price, 2) ?>" readonly>
      </div>
      <div class="form-group">
        <label for="amount_paid"><i class="fa fa-money-bill-wave"></i> Amount Paid:</label>
        <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="0" required>
      </div>

      <div class="form-group">
        <label for="payment_type"><i class="fa fa-credit-card"></i> Payment Type:</label>
        <select name="payment_type" id="payment_type" required onchange="toggleMonthsField(); updateMonthlyPayment();">
          <option value="fully_paid">Fully Paid</option>
          <option value="staggered">Staggered</option>
        </select>
      </div>
      <div class="form-group" id="months_group">
        <label for="months"><i class="fa fa-calendar-alt"></i> Number of Months:</label>
        <input type="number" name="months" id="months" min="1" value="1">
      </div>
      <div class="form-group" id="monthly_payment_group" style="display:none;">
        <label><i class="fa fa-calendar"></i> Monthly Payment:</label>
        <input type="text" id="monthly_payment_display" value="₱0.00" readonly>
      </div>

      <div class="btn-group">
        <button type="submit"><i class="fa fa-save"></i> Save Bill</button>
        <button type="reset"><i class="fa fa-undo"></i> Reset</button>
      </div>
    </form>
  </div>
</div>

<script>
function formatPeso(num) {
  return '₱' + (parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}));
}

function fetchServices(patientId) {
  if (patientId) {
    window.location.href = `cashiering.php?patient_id=${patientId}`;
  }
}

function toggleMonthsField() {
  var paymentType = document.getElementById('payment_type').value;
  var monthsGroup = document.getElementById('months_group');
  monthsGroup.style.display = (paymentType === 'staggered') ? 'block' : 'none';
  updateMonthlyPayment();
}

function toggleIdField() {
  var discountType = document.getElementById('discount_type').value;
  var idFieldGroup = document.getElementById('id_field_group');
  var idFieldLabel = document.getElementById('id_field_label');

  if (discountType === 'pwd') {
    idFieldGroup.style.display = 'block';
    idFieldLabel.innerHTML = '<i class="fa fa-id-card"></i> PWD ID Number:';
  } else if (discountType === 'philhealth') {
    idFieldGroup.style.display = 'block';
    idFieldLabel.innerHTML = '<i class="fa fa-id-card"></i> PhilHealth ID Number:';
  } else if (discountType === 'senior') {
    idFieldGroup.style.display = 'block';
    idFieldLabel.innerHTML = '<i class="fa fa-id-card"></i> Senior Citizen ID Number:';
  } else {
    idFieldGroup.style.display = 'none';
    idFieldLabel.innerHTML = '<i class="fa fa-id-card"></i> Discount ID Number:';
    document.getElementById('discount_id').value = '';
  }
}

function updateMonthlyPayment() {
  var paymentType = document.getElementById('payment_type').value;
  var months = parseInt(document.getElementById('months').value) || 1;
  var totalInput = document.getElementById('total_amount');
  var amountPaidInput = document.getElementById('amount_paid');
  var monthlyGroup = document.getElementById('monthly_payment_group');
  var monthlyDisplay = document.getElementById('monthly_payment_display');
  
  var totalAmount = parseFloat(totalInput.value) || 0;
  var amountPaid = parseFloat(amountPaidInput.value) || 0;
  var balance = totalAmount - amountPaid;

  if (paymentType === 'staggered' && months > 0) {
    var perMonth = balance / months;
    if (perMonth < 0) perMonth = 0;
    monthlyGroup.style.display = 'block';
    monthlyDisplay.value = formatPeso(perMonth);
  } else {
    monthlyGroup.style.display = 'none';
    monthlyDisplay.value = formatPeso(0);
  }
}
// AJAX fetch discount rate for selected type and update totals
function setDiscountAmount() {
  var discountType = document.getElementById('discount_type').value;
  var originalInput = document.getElementById('original_total');
  var totalInput = document.getElementById('total_amount');
  var discountInput = document.getElementById('discount');
  var originalDisplay = document.getElementById('original_total_display');
  var discountDisplay = document.getElementById('discount_display');
  var totalDisplay = document.getElementById('total_amount_display');
  var baseTotal = parseFloat(originalInput.value) || 0;

  originalDisplay.value = formatPeso(baseTotal);

  if (discountType === 'none') {
    discountInput.value = '0.00';
    discountDisplay.value = formatPeso(0);
    totalInput.value = baseTotal.toFixed(2);
    totalDisplay.value = formatPeso(baseTotal);
    updateMonthlyPayment();
    return;
  }

  // AJAX call to get rate
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'get_discount_rate.php?discount_type=' + encodeURIComponent(discountType), true);
  xhr.onload = function() {
    var rate = parseFloat(xhr.responseText) || 0;
    var discount = baseTotal * (rate / 100);
    discountInput.value = discount.toFixed(2);
    discountDisplay.value = formatPeso(discount);
    var newTotal = baseTotal - discount;
    if (newTotal < 0) newTotal = 0;
    totalInput.value = newTotal.toFixed(2);
    totalDisplay.value = formatPeso(newTotal);
    updateMonthlyPayment();
  };
  xhr.send();
}

document.addEventListener('DOMContentLoaded', function() {
  setDiscountAmount();
  toggleMonthsField();
  toggleIdField();
  updateMonthlyPayment();
  
  document.getElementById('payment_type').addEventListener('change', function() {
    toggleMonthsField();
    updateMonthlyPayment();
  });
  
  document.getElementById('months').addEventListener('input', updateMonthlyPayment);
  document.getElementById('total_amount').addEventListener('input', updateMonthlyPayment);
  document.getElementById('amount_paid').addEventListener('input', updateMonthlyPayment); // Add this line
  
  document.getElementById('discount_type').addEventListener('change', function() {
    setDiscountAmount();
    toggleIdField();
  });
});
</script>
</body>
</html>