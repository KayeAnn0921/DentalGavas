<?php
include 'config.php';

if (!isset($_GET['id'])) {
  echo "No ID specified.";
  exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
  echo "Appointment not found.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = $_POST['type'];
  $date = $_POST['date'];
  $time = $_POST['time'];
  $contact = $_POST['contact'];
  $status = $_POST['status'];

  $update = $pdo->prepare("UPDATE appointments SET type_of_visit=?, appointment_date=?, appointment_time=?, contact_number=?, status=? WHERE appointment_id=?");
  $update->execute([$type, $date, $time, $contact, $status, $id]);

  header("Location: appointment_list.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Appointment</title>
  <link rel="stylesheet" href="css/form.css">
</head>
<body>
  <h2>Edit Appointment</h2>
  <form method="POST">
    <label>Type of Visit:</label>
    <input type="text" name="type" value="<?php echo htmlspecialchars($appointment['type_of_visit']); ?>" required><br>

    <label>Date:</label>
    <input type="date" name="date" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required><br>

    <label>Time:</label>
    <input type="time" name="time" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required><br>

    <label>Contact Number:</label>
    <input type="text" name="contact" value="<?php echo htmlspecialchars($appointment['contact_number']); ?>" required><br>

    <label>Status:</label>
    <select name="status" required>
      <option value="Pending" <?php if ($appointment['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
      <option value="Confirmed" <?php if ($appointment['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
      <option value="Cancelled" <?php if ($appointment['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
      <option value="Completed" <?php if ($appointment['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
    </select><br>

    <button type="submit">Update Appointment</button>
  </form>
</body>
</html>
