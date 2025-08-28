<?php
include 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Invalid appointment ID.";
    exit;
}

// Fetch appointment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->execute([$id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    echo "Appointment not found.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $type_of_visit = $_POST['type_of_visit'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time']; // This should be a range string
    $contact_number = $_POST['contact_number'];
    $service_id = $_POST['service_id'];
    $status = $_POST['status'];

    $update = $pdo->prepare("UPDATE appointments SET first_name=?, last_name=?, type_of_visit=?, appointment_date=?, appointment_time=?, contact_number=?, service_id=?, status=? WHERE appointment_id=?");
    $update->execute([
        $first_name, $last_name, $type_of_visit, $appointment_date, $appointment_time, $contact_number, $service_id, $status, $id
    ]);
    header("Location: appointmentlist.php");
    exit;
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Appointment</title>
    <link rel="stylesheet" href="css/apointmentlist.css"/>
    <style>
    .edit-form {
    width: 1100px;
    margin: 48px auto 0 auto;
    background: #fff;
    padding: 38px 36px 32px 36px;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(30,136,229,0.10);
}

.edit-form h2 {
    text-align: center;
    color: #1976d2;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 28px;
    letter-spacing: 1px;
}

.edit-form label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    color: #2d3a4b;
    font-size: 1rem;
}

.edit-form input[type="text"],
.edit-form input[type="date"],
.edit-form select {
    width: 100%;
    padding: 11px 14px;
    border: 1.2px solid #d1d9e6;
    border-radius: 6px;
    font-size: 1rem;
    background: #f8fafc;
    color: #2d3a4b;
    margin-bottom: 20px;
    transition: border 0.2s;
}

.edit-form input[type="text"]:focus,
.edit-form input[type="date"]:focus,
.edit-form select:focus {
    border: 1.2px solid #1976d2;
    outline: none;
    background: #fff;
}

.edit-form button[type="submit"] {
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 12px 0;
    font-size: 1rem;
    font-weight: 600;
    width: 100%;
    margin-top: 10px;
    box-shadow: 0 2px 8px rgba(25,118,210,0.07);
    transition: background 0.2s, box-shadow 0.2s;
    letter-spacing: 0.5px;
    cursor: pointer;
}

.edit-form button[type="submit"]:hover {
    background: #1256a3;
    box-shadow: 0 4px 16px rgba(25,118,210,0.13);
}

@media (max-width: 700px) {
    .edit-form {
        padding: 18px 8px 18px 8px;
        max-width: 98vw;
    }
    .edit-form h2 {
        font-size: 1.1rem;
    }
}
    </style>
</head>
<body>
    <div class="edit-form">
        <h2>Edit Appointment</h2>
        <form method="POST">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($appt['first_name']); ?>" required>

            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($appt['last_name']); ?>" required>

            <label>Type of Visit</label>
            <select name="type_of_visit" required>
                <option value="appointment" <?php if($appt['type_of_visit']=='appointment') echo 'selected'; ?>>Appointment</option>
                <option value="walk-in" <?php if($appt['type_of_visit']=='walk-in') echo 'selected'; ?>>Walk-in</option>
            </select>

            <label>Appointment Date</label>
            <input type="date" name="appointment_date" value="<?php echo htmlspecialchars($appt['appointment_date']); ?>" required>

            <label>Appointment Time (Range)</label>
            <input type="text" name="appointment_time" value="<?php echo htmlspecialchars($appt['appointment_time']); ?>" placeholder="e.g. 8:00 AM - 9:00 AM" required>

            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($appt['contact_number']); ?>" required>

            <label>Service</label>
            <select name="service_id" required>
                <?php
                $services = $pdo->query("SELECT service_id, name FROM services")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($services as $service) {
                    $selected = $service['service_id'] == $appt['service_id'] ? 'selected' : '';
                    echo "<option value='{$service['service_id']}' $selected>" . htmlspecialchars($service['name']) . "</option>";
                }
                ?>
            </select>

            <label>Status</label>
            <select name="status" required>
                <option value="pending" <?php if($appt['status']=='pending') echo 'selected'; ?>>Pending</option>
                <option value="confirmed" <?php if($appt['status']=='confirmed') echo 'selected'; ?>>Confirmed</option>
                <option value="cancelled" <?php if($appt['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>

            <button type="submit">Update Appointment</button>
        </form>
    </div>
</body>
</html>