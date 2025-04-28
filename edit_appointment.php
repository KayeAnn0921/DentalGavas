<?php
include 'config.php';

if (!isset($_GET['id'])) {
    echo "No ID specified.";
    exit;
}

$id = $_GET['id'];

try {
    // Fetch everything you need once
    $stmt = $pdo->prepare("SELECT appointment_id, patient_id, type_of_visit, appointment_date, appointment_time, contact_number, status FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo "Appointment not found.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $contact = $_POST['contact'];
    $status = $_POST['status'];

    try {
        $update = $pdo->prepare("UPDATE appointments SET type_of_visit=?, appointment_date=?, appointment_time=?, contact_number=?, status=? WHERE appointment_id=?");
        $update->execute([$type, $date, $time, $contact, $status, $id]);

        header("Location: appointmentlist.php");
        exit;
    } catch (PDOException $e) {
        echo "Error updating appointment: " . htmlspecialchars($e->getMessage());
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Appointment</title>
  <link rel="stylesheet" href="css/editappointment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main>
  
    <form method="POST">

    <label>Type of Visit:</label>
<select name="type" required>
  <option value="appointment" <?php if ($appointment['type_of_visit'] == 'appointment') echo 'selected'; ?>>Appointment</option>
  <option value="walk-in" <?php if ($appointment['type_of_visit'] == 'walk-in') echo 'selected'; ?>>Walk-in</option>
</select><br>


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
</main>
</body>
</html>