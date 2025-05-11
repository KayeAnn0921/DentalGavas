<?php
include 'config.php';

// Fetch existing schedules for the table
$schedules = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedule ORDER BY schedule_date DESC, doctor_name");
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $schedule_error = "Error fetching schedules: " . $e->getMessage();
}

if (isset($_GET['fetch_schedule']) && isset($_GET['doctor']) && isset($_GET['date'])) {
    $doctor = $_GET['doctor'];
    $date = $_GET['date'];

    try {
        $stmt = $pdo->prepare("SELECT schedule_date, start_time, end_time, status 
                               FROM doctor_schedule 
                               WHERE doctor_name = ? AND schedule_date = ?");
        $stmt->execute([$doctor, $date]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($schedules);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }   
}

// Handle POST form submission to save schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor = $_POST['doctor'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];
    $selectedDates = json_decode($_POST['selected_date_statuses'], true);

    if (!empty($doctor) && !empty($selectedDates)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO doctor_schedule (doctor_name, schedule_date, status, start_time, end_time) 
                                   VALUES (?, ?, ?, ?, ?)");

            foreach ($selectedDates as $date => $_) {
                $stmt->execute([$doctor, $date, $status, $start_time, $end_time]);
            }

            // Return success message and refresh the page to show updated table
            echo "<script>
                alert('Schedule saved successfully.');
                window.location.href='doctor_schedule.php';
            </script>";
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . $e->getMessage() . "'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Please select a doctor and at least one date.'); history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Schedule | Gavas Dental Clinic</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet" href="css/doctor_schedule.css"/>
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="form-container">
    <h2>Set Doctor Availability</h2>
    <form action="" method="POST">
      <label for="doctor">Doctor:</label>
      <select name="doctor" id="doctor" required>
        <option value="">-- Select Doctor --</option>
        <option value="Dr. Glenn Gavas">Dr. Glenn Gavas</option>
        <option value="Dr. Anna Patricia Gavas-Paña">Dr. Anna Patricia Gavas-Paña</option>
      </select>

      <label for="schedule_dates">Select Multiple Dates:</label>
      <input type="text" id="schedule_dates" placeholder="Click to select multiple dates" readonly required>
      <input type="hidden" name="selected_date_statuses" id="selected_date_statuses">

      <label class="availability-type">Availability:</label>
      <select name="status" id="status" required>
        <option value="Available">🟢 Available</option>
        <option value="Not Available">🔴 Not Available</option>
      </select>

      <label for="start_time">Start Time:</label>
      <input type="time" name="start_time" required>

      <label for="end_time">End Time:</label>
      <input type="time" name="end_time" required>

      <button type="submit" class="submit-btn">Save Schedule</button>
    </form>

    <a href="schedule.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
  </div>

  <!-- Schedule Table Section -->
  <div class="table-container">
    <h3>Current Doctor Schedules</h3>
    <?php if (isset($schedule_error)): ?>
      <div class="alert alert-danger"><?php echo $schedule_error; ?></div>
    <?php elseif (empty($schedules)): ?>
      <p>No schedules found. Please add schedules using the form above.</p>
    <?php else: ?>
      <table id="schedulesTable" class="display">
        <thead>
          <tr>
            <th>Doctor</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($schedules as $schedule): ?>
            <tr>
              <td><?php echo htmlspecialchars($schedule['doctor_name']); ?></td>
              <td><?php echo date('M j, Y', strtotime($schedule['schedule_date'])); ?></td>
              <td><?php echo date('g:i A', strtotime($schedule['start_time'])); ?></td>
              <td><?php echo date('g:i A', strtotime($schedule['end_time'])); ?></td>
              <td>
                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $schedule['status'])); ?>">
                  <?php echo $schedule['status']; ?>
                </span>
              </td>
              <td>
                <button class="edit-btn" data-id="<?php echo $schedule['id']; ?>">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-btn" data-id="<?php echo $schedule['id']; ?>">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#schedulesTable').DataTable({
      responsive: true,
      order: [[1, 'asc']] // Sort by date by default
    });

    // Datepicker initialization
    let selectedDates = {};

    function refreshDatepicker() {
      $("#selected_date_statuses").val(JSON.stringify(selectedDates));
      $("#schedule_dates").val(Object.keys(selectedDates).join(", "));
      $("#schedule_dates").datepicker("refresh");
    }

    $("#schedule_dates").datepicker({
      dateFormat: "yy-mm-dd",
      beforeShowDay: function(date) {
        let formatted = $.datepicker.formatDate('yy-mm-dd', date);
        if (selectedDates[formatted] === 'Available') {
          return [true, "available-day"];
        } else if (selectedDates[formatted] === 'Not Available') {
          return [true, "unavailable-day"];
        }
        return [true, ""];
      },
      onSelect: function(dateText) {
        let status = $("#status").val();
        if (selectedDates[dateText] === status) {
          delete selectedDates[dateText]; // Toggle off
        } else {
          selectedDates[dateText] = status;
        }
        refreshDatepicker();
      }
    });

    $("#status").on("change", function() {
      refreshDatepicker();
    });

    // Delete button handler
    $('.delete-btn').click(function() {
      if (confirm('Are you sure you want to delete this schedule?')) {
        const id = $(this).data('id');
        $.post('delete_schedule.php', {id: id}, function(response) {
          if (response.success) {
            alert('Schedule deleted successfully.');
            location.reload();
          } else {
            alert('Error: ' + response.error);
          }
        }, 'json');
      }
    });
  });
</script>

<style>
  /* Table Styles */
  .table-container {
    margin-top: 40px;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .table-container h3 {
    color: #0052cc;
    margin-bottom: 20px;
  }

  #schedulesTable {
    width: 100%;
    border-collapse: collapse;
  }

  #schedulesTable th {
    background-color: #0052cc;
    color: white;
    padding: 12px;
    text-align: left;
  }

  #schedulesTable td {
    padding: 10px;
    border-bottom: 1px solid #e1f0ff;
  }

  #schedulesTable tr:hover {
    background-color: #f8fbff;
  }

  .status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
  }

  .status-badge.available {
    background-color: #28a745;
    color: white;
  }

  .status-badge.not-available {
    background-color: #dc3545;
    color: white;
  }

  .edit-btn, .delete-btn {
    padding: 5px 10px;
    margin-right: 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
  }

  .edit-btn {
    background-color: #ffc107;
    color: #212529;
  }

  .delete-btn {
    background-color: #dc3545;
    color: white;
  }

  .edit-btn:hover, .delete-btn:hover {
    opacity: 0.8;
  }
</style>
</body>
</html>