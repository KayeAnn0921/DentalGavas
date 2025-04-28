<?php
include 'config.php';

if (!isset($_GET['id'])) {
  echo "No ID specified.";
  exit;
}

$id = $_GET['id'];

// Fetch the appointment details
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
  echo "Appointment not found.";
  exit;
}

// Fetch available services for the dropdown
$services = $pdo->query("SELECT * FROM classification")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type_of_visit = $_POST['type_of_visit'];
  $appointment_date = $_POST['appointment_date'];
  $appointment_time = $_POST['appointment_time'];
  $contact_number = $_POST['contact_number'];
  $status = $_POST['status'];
  $classification_id = $_POST['classification_id'];

  $update = $pdo->prepare("UPDATE appointments 
    SET type_of_visit=?, appointment_date=?, appointment_time=?, contact_number=?, status=?, classification_id=? 
    WHERE appointment_id=?");
  $update->execute([$type_of_visit, $appointment_date, $appointment_time, $contact_number, $status, $classification_id, $id]);

  header("Location: appointmentlist.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Appointment</title>
  <link rel="stylesheet" href="css/editappointment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main>
  <h2>Edit Appointment</h2>
  <form method="POST">
    <label>Type of Visit:</label>
    <input type="text" name="type_of_visit" value="<?php echo htmlspecialchars($appointment['type_of_visit']); ?>" required><br>

    <label>Date:</label>
    <input type="date" name="appointment_date" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required><br>

    <label>Time:</label>
    <input type="time" name="appointment_time" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required><br>

    <label>Contact Number:</label>
    <input type="text" name="contact_number" value="<?php echo htmlspecialchars($appointment['contact_number']); ?>" required><br>

    <label>Status:</label>
    <select name="status" required>
      <option value="Pending" <?php if ($appointment['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
      <option value="Confirmed" <?php if ($appointment['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
      <option value="Cancelled" <?php if ($appointment['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
      <option value="Completed" <?php if ($appointment['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
    </select><br>

    <label>Service:</label>
    <select name="classification_id" required>
      <?php foreach ($services as $service): ?>
        <option value="<?php echo $service['classification_id']; ?>" 
          <?php if ($service['classification_id'] == $appointment['classification_id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($service['name']) . " (₱" . number_format($service['price'], 2) . ")"; ?>
        </option>
      <?php endforeach; ?>
    </select><br>

    <button type="submit">Update Appointment</button>
  </form>
</main>
</body>
</html>
