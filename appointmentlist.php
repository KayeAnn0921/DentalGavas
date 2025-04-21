<?php include 'config.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Appointment List | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/schedule.css"/>
  
</head>
<body>
  <div class="main-content">
    <h1 class="form-header">Appointment List</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Patient ID</th>
          <th>Type of Visit</th>
          <th>Appointment Date</th>
          <th>Time</th>
          <th>Contact Number</th>
          <th>Service</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
          // Fetch appointments and join with services table
          $stmt = $pdo->prepare("
            SELECT a.*, s.service_name 
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.service_id
            ORDER BY a.appointment_date DESC, a.appointment_time ASC
          ");
          $stmt->execute();
          $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if ($appointments) {
            foreach ($appointments as $appt) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($appt['appointment_id']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['patient_id']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['type_of_visit']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['appointment_date']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['appointment_time']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['contact_number']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['service_name']) . "</td>";
              echo "<td>" . htmlspecialchars($appt['status']) . "</td>";
              echo "<td class='action-buttons'>
                      <a href='edit_appointment.php?id=" . $appt['appointment_id'] . "' class='edit-btn'>Edit</a>
                      <a href='delete_appointment.php?id=" . $appt['appointment_id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this appointment?\")'>Delete</a>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='9'>No appointments found.</td></tr>";
          }
        } catch (PDOException $e) {
          echo "<tr><td colspan='9'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>
