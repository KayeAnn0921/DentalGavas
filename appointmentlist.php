<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Appointment List | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/apointmentlist.css"/>
</head>

<body>
<?php include 'sidebar.php'; ?>
  <div class="main-content">
    <h1 class="form-header">Appointment List</h1>

    <div class="search-container">
      <form method="GET" action="">
        <input type="text" name="search" placeholder="Search appointments..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Contact</th>
            <th>Service</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          try {
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            
            // Updated SQL query to join with classification table
            // Check your appointments table structure to find the correct column name
// It's likely something like 'service_id' or 'classification' instead of 'classification_id'

            $sql = "
            SELECT a.*, c.name as service_name, c.price
            FROM appointments a
            LEFT JOIN classification c ON a.classification_id = c.classification_id
            WHERE a.appointment_id LIKE :search 
              OR a.type_of_visit LIKE :search 
              OR a.appointment_date LIKE :search 
              OR a.appointment_time LIKE :search 
              OR a.contact_number LIKE :search 
              OR c.name LIKE :search 
              OR a.status LIKE :search
            ORDER BY a.appointment_date DESC, a.appointment_time ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => "%$search%"]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($appointments) {
              foreach ($appointments as $appt) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($appt['appointment_id']) . "</td>";
                echo "<td>" . htmlspecialchars($appt['type_of_visit']) . "</td>";
                echo "<td>" . htmlspecialchars($appt['appointment_date']) . "</td>";
                echo "<td>" . htmlspecialchars($appt['appointment_time']) . "</td>";
                echo "<td>" . htmlspecialchars($appt['contact_number']) . "</td>";
                echo "<td>" . htmlspecialchars($appt['service_name'] ?? 'Not specified') . "</td>";
                echo "<td>₱" . number_format($appt['price'] ?? 0, 2) . "</td>";
                echo "<td>" . htmlspecialchars($appt['status']) . "</td>";
                echo "<td class='action-buttons'>
                        <a href='edit_appointment.php?id=" . $appt['appointment_id'] . "' class='edit-btn'><i class='fas fa-edit'></i> Edit</a>
                        <a href='delete_appointment.php?id=" . $appt['appointment_id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this appointment?\")'><i class='fas fa-trash'></i> Delete</a>
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
  </div>
</body>
</html>