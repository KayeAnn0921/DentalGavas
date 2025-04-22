<?php include 'config.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scheduling | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/schedule.css"/>
</head>
<body>

<?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="scheduling-form">
      <h1 class="form-header">Scheduling</h1>
      <form action="save_appointment.php" method="POST">
        <div class="form-group">
          <label for="visitType">Type of visit:</label>
          <select id="visitType" name="visitType" required>
            <option value="appointment">Appointment</option>
            <option value="walk-in">Walk-in</option>
          </select>
        </div>

        <div class="form-group">
          <label for="appointmentDate">Appointment Date:</label>
          <input type="date" id="appointmentDate" name="appointmentDate" required/>
        </div>

        <div class="form-group">
          <label for="appointmentTime">Appointment Time:</label>
          <input type="time" id="appointmentTime" name="appointmentTime" required/>
        </div>

        <div class="form-group">
          <label for="contactNumber">Contact Number:</label>
          <input type="text" id="contactNumber" name="contactNumber" placeholder="e.g. 09123456789" required/>
        </div>

        <div class="form-group">
          <label for="classification_id">Service:</label>
          <select id="classification_id" name="classification_id" required>
            <option value="">-- Select a Service --</option>

            <?php
try {
    // Prepare and execute the query using PDO to get all classifications
    $stmt = $pdo->prepare("SELECT classification_id, name, parent_id, price FROM classification");
    $stmt->execute();

    // Fetch all classifications
    $classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($classifications) {
        // Function to recursively build options with hierarchy
        function buildOptions($classifications, $parent_id = null, $indent = 0) {
            foreach ($classifications as $row) {
                if ($row['parent_id'] == $parent_id) {
                    $indentStr = str_repeat('&nbsp;&nbsp;&nbsp;', $indent);
                    echo '<option value="' . htmlspecialchars($row['classification_id']) . '">' 
                        . $indentStr . htmlspecialchars($row['name']) 
                        . ' (₱' . number_format($row['price'], 2) . ')' 
                        . '</option>';
                    // Recursively call for child items
                    buildOptions($classifications, $row['classification_id'], $indent + 1);
                }
            }
        }

        // Start building the options
        buildOptions($classifications);
    } else {
        echo '<option value="">No classifications available</option>';
    }
} catch (PDOException $e) {
    echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
}
?>

          </select>
        </div>

        <button type="submit" class="btn">
          SUBMIT
        </button>
      </form>
    </div>
  </div>
</body>
</html>
