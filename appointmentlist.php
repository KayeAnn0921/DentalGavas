<?php 
include 'config.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $appointmentId = $_POST['appointment_id'];
        $newStatus = $_POST['status'];
        
        // Update the status in database
        $updateSql = "UPDATE appointments SET status = :status WHERE appointment_id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            'status' => $newStatus,
            'id' => $appointmentId
        ]);
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $statusUpdateError = "Error updating status: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment List | Gavas Dental Clinic</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/apointmentlist.css"/>

  <style>
    
  </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <h1 class="form-header">Appointment List</h1>

  <?php if (isset($statusUpdateError)): ?>
    <div class="status-message" style="background:red; color:white; padding:10px; margin-bottom:15px;">
      <?php echo htmlspecialchars($statusUpdateError); ?>
    </div>
  <?php endif; ?>

  <div class="search-container">
    <form method="GET" action="">
      <input type="text" name="search" placeholder="Search appointments..." 
             value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Patient Name</th>
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
    $sql = "
      SELECT a.*, c.name as service_name, c.price
      FROM appointments a
      LEFT JOIN classification c ON a.classification_id = c.classification_id
      WHERE a.first_name LIKE :search 
          OR a.last_name LIKE :search
          OR a.appointment_id LIKE :search 
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
            $currentStatus = strtolower($appt['status']);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($appt['appointment_id']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['first_name']) . " " . htmlspecialchars($appt['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['type_of_visit']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['appointment_date']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['appointment_time']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['contact_number']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['service_name'] ?? 'Not specified') . "</td>";
            echo "<td>₱" . number_format($appt['price'] ?? 0, 2) . "</td>";

            echo "<td>
                  <form method='POST' action='' class='status-form'>
                    <input type='hidden' name='appointment_id' value='" . htmlspecialchars($appt['appointment_id']) . "'>
                    <select name='status' class='status-select " . $currentStatus . "'>
                      <option value='pending' " . ($currentStatus == 'pending' ? 'selected' : '') . ">Pending</option>
                      <option value='confirmed' " . ($currentStatus == 'confirmed' ? 'selected' : '') . ">Confirmed</option>
                      <option value='cancelled' " . ($currentStatus == 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                    </select>
                    <input type='hidden' name='update_status' value='1'>
                  </form>
                  </td>";

            echo "<td class='action-buttons'>";
            echo "<a href='edit_appointment.php?id=" . htmlspecialchars($appt['appointment_id']) . "' class='edit-btn'><i class='fas fa-edit'></i> Edit</a> ";
            echo "<a href='delete_appointment.php?id=" . htmlspecialchars($appt['appointment_id']) . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this appointment?\")'><i class='fas fa-trash'></i> Delete</a> ";

            if ($currentStatus == 'confirmed') {
                echo "<a href='patient.php?appointment_id=" . htmlspecialchars($appt['appointment_id']) . "' class='fill-form-btn'>Fill Patient Form</a>";
            }
            echo "</td>";

            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No appointments found.</td></tr>";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='10'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}
?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update select colors and handle form submission
    const statusSelects = document.querySelectorAll('.status-select');
    
    statusSelects.forEach(select => {
        // Set initial color
        updateSelectColor(select);
        
        // Handle change event
        select.addEventListener('change', function() {
            updateSelectColor(this);
            this.closest('form').submit();
        });
    });
    
    function updateSelectColor(selectElement) {
        // Remove all status classes
        selectElement.classList.remove('pending', 'confirmed', 'cancelled');
        
        // Add the appropriate class based on current value
        selectElement.classList.add(selectElement.value.toLowerCase());
    }
});
</script>

</body>
</html>