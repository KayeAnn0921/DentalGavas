<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scheduling | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/schedule.css"/>
  <script>
    // Form validation
    function validateForm() {
      const contactNumber = document.getElementById('contactNumber').value;
      const contactPattern = /^(09|\+639)\d{9}$/;
      
      if (!contactPattern.test(contactNumber)) {
        alert('Please enter a valid Philippine mobile number (e.g., 09123456789 or +639123456789)');
        return false;
      }
      
      return true;
    }
  </script>
</head>
<body>

<?php include 'sidebar.php'; ?>
  <div class="main-content">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="alert alert-success">
        Appointment successfully scheduled!
      </div>
    <?php endif; ?>
    
    <div class="scheduling-form">
      <h1 class="form-header">Scheduling</h1>
      <form action="save_appointment.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
          <label for="first_name">First Name:</label>
          <input type="text" id="first_name" name="first_name" required/>
        </div>

        <div class="form-group">
          <label for="last_name">Last Name:</label>
          <input type="text" id="last_name" name="last_name" required/>
        </div>

        <div class="form-group">
          <label for="visitType">Type of visit:</label>
          <select id="visitType" name="visitType" required>
            <option value="">-- Select Visit Type --</option>
            <option value="appointment">Appointment</option>
            <option value="walk-in">Walk-in</option>
          </select>
        </div>

        <div class="form-group">
          <label for="appointmentDate">Appointment Date:</label>
          <input type="date" id="appointmentDate" name="appointmentDate" min="<?php echo date('Y-m-d'); ?>" required/>
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
          <label for="service_id">Service:</label>
          <select id="service_id" name="service_id" required>
            <option value="">-- Select a Service --</option>

            <?php
            try {
                // Prepare and execute the query to get all classifications
                $stmt = $pdo->prepare("SELECT service_id, name, parent_id, price FROM services ORDER BY parent_id, service_id");
                $stmt->execute();

                // Fetch all classifications
                $classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Create an associative array with service_id as key
                $classification_map = [];
                foreach ($classifications as $row) {
                    $classification_map[$row['service_id']] = $row;
                }

                // Function to build options from the map
                function buildOptions($classifications, $classification_map, $parent_id = null, $indent = 0) {
                    foreach ($classifications as $row) {
                        if ($row['parent_id'] == $parent_id) {
                            $indentStr = str_repeat('&nbsp;&nbsp;&nbsp;', $indent);
                            echo '<option value="' . htmlspecialchars($row['service_id']) . '">' 
                                . $indentStr . htmlspecialchars($row['name']) 
                                . ' (₱' . number_format($row['price'], 2) . ')' 
                                . '</option>';

                            // Fetch child classifications
                            $children = array_filter($classifications, function($item) use ($row) {
                                return $item['parent_id'] == $row['service_id'];
                            });
                            buildOptions($children, $classification_map, $row['service_id'], $indent + 1);
                        }
                    }
                }

                // Start building the options
                buildOptions($classifications, $classification_map);

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