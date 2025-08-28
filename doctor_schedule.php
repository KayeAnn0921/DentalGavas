<?php
include 'config.php';

// --- AJAX: Fetch schedule by ID for editing ---
if (isset($_GET['fetch_schedule_by_id']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedule WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $sched = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($sched ?: []);
    exit;
}

// --- AJAX: Update schedule ---
if (isset($_POST['update_schedule'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $stmt = $pdo->prepare("UPDATE doctor_schedule SET status=?, start_time=?, end_time=? WHERE id=?");
    $ok = $stmt->execute([$status, $start_time, $end_time, $id]);
    echo json_encode(['success' => $ok]);
    exit;
}

// --- AJAX: Fetch available dates for doctor ---
if (isset($_GET['fetch_available_dates']) && isset($_GET['doctor'])) {
    $doctor = $_GET['doctor'];
    $stmt = $pdo->prepare("SELECT schedule_date FROM doctor_schedule WHERE doctor_name = ? AND status = 'Available'");
    $stmt->execute([$doctor]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($dates);
    exit;
}

// --- AJAX: Fetch schedule for doctor/date (for appointment page) ---
if (isset($_GET['fetch_schedule']) && isset($_GET['doctor']) && isset($_GET['date'])) {
    $doctor = $_GET['doctor'];
    $date = $_GET['date'];
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedule WHERE doctor_name = ? AND schedule_date = ? AND status = 'Available'");
    $stmt->execute([$doctor, $date]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($schedules);
    exit;
}

// Fetch existing schedules for the table
$schedules = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedule ORDER BY doctor_name ASC, schedule_date ASC");
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $schedule_error = "Error fetching schedules: " . $e->getMessage();
}

// Organize schedules by doctor for easy lookup
$doctors = [];
foreach ($schedules as $s) {
    $doctors[$s['doctor_name']][] = $s['schedule_date'];
}

// Generate a date range (today to today+7)
$today = date('Y-m-d');
$futureDays = 7;
$dateRange = [];
for ($i = 0; $i <= $futureDays; $i++) {
    $dateRange[] = date('Y-m-d', strtotime("+$i days"));
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

  <style>
  body {
    background: #f4f8fb;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #222;
    margin: 0;
    padding: 0;
  }
  .main-content {
    max-width: 1100px;
    margin: 40px auto 0 auto;
    padding: 0 2vw 40px 2vw;
  }
  .card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(25,118,210,0.10);
    padding: 32px 28px 28px 28px;
    margin-bottom: 32px;
    transition: box-shadow 0.2s;
  }
  .card h2, .card h3 {
    margin-top: 0;
    color: #1976d2;
    font-weight: 700;
    letter-spacing: 1px;
  }
  .form-container label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
    color: #1976d2;
  }
  .form-container input[type="text"],
  .form-container input[type="time"],
  .form-container select {
    width: 100%;
    padding: 10px 12px;
    margin: 6px 0 16px 0;
    border: 1px solid #b6d4fe;
    border-radius: 7px;
    background: #f7fbff;
    font-size: 1em;
    transition: border 0.2s;
  }
  .form-container input[type="text"]:focus,
  .form-container input[type="time"]:focus,
  .form-container select:focus {
    border: 1.5px solid #1976d2;
    outline: none;
    background: #e3f2fd;
  }
  .submit-btn, .back-btn {
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 10px 22px;
    font-size: 1.05em;
    font-weight: 600;
    cursor: pointer;
    margin-top: 8px;
    margin-right: 10px;
    transition: background 0.2s;
    text-decoration: none;
    display: inline-block;
  }
  .submit-btn:hover, .back-btn:hover {
    background: #1256a3;
  }
  .alert-danger {
    background: #ffeaea;
    color: #d32f2f;
    padding: 12px 18px;
    border-radius: 7px;
    margin-bottom: 18px;
    font-weight: 500;
  }
  .table-container {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(25,118,210,0.10);
    padding: 32px 28px 28px 28px;
    overflow-x: auto;
  }
  #schedulesTable {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #f9fafb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(25,118,210,0.07);
    margin-top: 18px;
    min-width: 700px;
  }
  #schedulesTable th {
    background-color: #1976d2;
    color: white;
    padding: 14px 10px;
    text-align: left;
    font-size: 1.07em;
    font-weight: 600;
    border-bottom: 2px solid #e3f2fd;
  }
  #schedulesTable td {
    padding: 12px 10px;
    border-bottom: 1px solid #e1f0ff;
    font-size: 1em;
    background: #fff;
  }
  #schedulesTable tr:hover {
    background-color: #e3f2fd;
  }
  .status-badge {
    padding: 6px 16px;
    border-radius: 16px;
    font-size: 0.98em;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: inline-block;
  }
  .status-badge.available {
    background-color: #43a047;
    color: #fff;
  }
  .status-badge.not-available {
    background-color: #e53935;
    color: #fff;
  }
  .edit-btn, .delete-btn {
    padding: 6px 14px;
    margin-right: 5px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.97em;
    transition: background 0.2s;
    outline: none;
  }
  .edit-btn {
    background-color: #ffc107;
    color: #212529;
  }
  .delete-btn {
    background-color: #e53935;
    color: #fff;
  }
  .edit-btn:hover, .delete-btn:hover {
    opacity: 0.85;
  }
  /* Modal styles */
  #editScheduleModal {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.4);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  #editScheduleModal[style*="flex"] {
    display: flex !important;
  }
  #editScheduleModal .modal-content {
    background: #fff;
    border-radius: 14px;
    padding: 32px 28px 24px 28px;
    min-width: 320px;
    max-width: 95vw;
    margin: auto;
    position: relative;
    box-shadow: 0 8px 32px rgba(25,118,210,0.13);
    animation: fadeIn 0.2s;
  }
  #editScheduleModal h3 {
    margin-top: 0;
    color: #1976d2;
    font-weight: 700;
    margin-bottom: 18px;
  }
  #editScheduleModal form > div {
    margin-bottom: 14px;
  }
  #editScheduleModal label {
    display: block;
    font-weight: 600;
    color: #1976d2;
    margin-bottom: 4px;
  }
  #editScheduleModal input[type="text"],
  #editScheduleModal input[type="time"],
  #editScheduleModal select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #b6d4fe;
    border-radius: 7px;
    background: #f7fbff;
    font-size: 1em;
    transition: border 0.2s;
  }
  #editScheduleModal input[type="text"]:focus,
  #editScheduleModal input[type="time"]:focus,
  #editScheduleModal select:focus {
    border: 1.5px solid #1976d2;
    outline: none;
    background: #e3f2fd;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-30px);}
    to { opacity: 1; transform: translateY(0);}
  }

  /* Responsive styles for laptops and below */
  @media (max-width: 1200px) {
    .main-content { max-width: 98vw; }
    .card, .table-container { padding: 18px 8px; }
    #schedulesTable th, #schedulesTable td { padding: 10px 6px; }
  }
  @media (max-width: 900px) {
    .main-content { max-width: 100vw; padding: 0 1vw 30px 1vw; }
    .card, .table-container { padding: 10px 2vw; }
    #schedulesTable th, #schedulesTable td { padding: 8px 4px; font-size: 0.97em; }
    #editScheduleModal .modal-content { padding: 18px 8px; }
  }
  @media (max-width: 700px) {
    .main-content { padding: 0 0.5vw 20px 0.5vw; }
    .card, .table-container { padding: 6px 2px; }
    #schedulesTable { min-width: 500px; font-size: 0.93em; }
    #editScheduleModal .modal-content { padding: 10px 2px; }
  }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="card form-container">
    <h2>Set Doctor Availability</h2>
    <form action="" method="POST" autocomplete="off">
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

      <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Save Schedule</button>
      <a href="schedule.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    </form>
  </div>

  <div class="table-container card">
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
<?php
foreach (array_keys($doctors) as $doctor) {
    foreach ($dateRange as $date) {
        $found = false;
        foreach ($schedules as $schedule) {
            if ($schedule['doctor_name'] == $doctor && $schedule['schedule_date'] == $date) {
                $found = true;
                echo '<tr>
                    <td>' . htmlspecialchars($doctor) . '</td>
                    <td>' . date('M j, Y', strtotime($date)) . '</td>
                    <td>' . date('g:i A', strtotime($schedule['start_time'])) . '</td>
                    <td>' . date('g:i A', strtotime($schedule['end_time'])) . '</td>
                    <td>
                        <span class="status-badge ' . (strtolower($schedule['status']) === 'available' ? 'available' : 'not-available') . '">' . $schedule['status'] . '</span>
                    </td>
                    <td>
                        <button class="edit-btn" data-id="' . $schedule['id'] . '"><i class="fas fa-edit"></i> Edit</button>
                        <button class="delete-btn" data-id="' . $schedule['id'] . '"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                </tr>';
                break;
            }
        }
        // If no schedule, show Not Available
        if (!$found) {
            echo '<tr>
                <td>' . htmlspecialchars($doctor) . '</td>
                <td>' . date('M j, Y', strtotime($date)) . '</td>
                <td>--</td>
                <td>--</td>
                <td>
                    <span class="status-badge not-available">Not Available</span>
                </td>
                <td>--</td>
            </tr>';
        }
    }
}
?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Schedule Modal -->
<div id="editScheduleModal">
  <div class="modal-content">
    <h3>Edit Schedule</h3>
    <form id="editScheduleForm">
      <input type="hidden" id="edit_id" name="id">
      <div>
        <label>Doctor:</label>
        <input type="text" id="edit_doctor" name="doctor" readonly>
      </div>
      <div>
        <label>Date:</label>
        <input type="text" id="edit_date" name="date" readonly>
      </div>
      <div>
        <label>Status:</label>
        <select id="edit_status" name="status" required>
          <option value="Available">Available</option>
          <option value="Not Available">Not Available</option>
        </select>
      </div>
      <div>
        <label>Start Time:</label>
        <input type="time" id="edit_start_time" name="start_time" required>
      </div>
      <div>
        <label>End Time:</label>
        <input type="time" id="edit_end_time" name="end_time" required>
      </div>
      <div style="margin-top:18px; text-align:right;">
        <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Save</button>
        <button type="button" class="back-btn" id="closeEditModal" style="background:#aaa;"><i class="fas fa-times"></i> Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {
    // Initialize DataTable with sorting by Doctor Name (column 0)
    $('#schedulesTable').DataTable({
      responsive: true,
      order: [[0, 'asc']]
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

    // --- Edit button handler ---
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      $.get('doctor_schedule.php', {fetch_schedule_by_id: 1, id: id}, function(data) {
        let sched;
        try {
          sched = JSON.parse(data);
        } catch(e) {
          alert('Failed to load schedule.');
          return;
        }
        if (!sched || !sched.id) {
          alert('Schedule not found.');
          return;
        }
        $('#edit_id').val(sched.id);
        $('#edit_doctor').val(sched.doctor_name);
        $('#edit_date').val(sched.schedule_date);
        $('#edit_status').val(sched.status);
        $('#edit_start_time').val(sched.start_time.slice(0,5));
        $('#edit_end_time').val(sched.end_time.slice(0,5));
        $('#editScheduleModal').css('display','flex');
      });
    });

    // Hide modal
    $('#closeEditModal').on('click', function() {
      $('#editScheduleModal').hide();
    });

    // Submit edit form
    $('#editScheduleForm').on('submit', function(e) {
      e.preventDefault();
      $.post('doctor_schedule.php', $(this).serialize() + '&update_schedule=1', function(resp) {
        try {
          let res = JSON.parse(resp);
          if (res.success) {
            alert('Schedule updated!');
            location.reload();
          } else {
            alert(res.error || 'Update failed.');
          }
        } catch {
          alert('Update failed.');
        }
      });
    });
  });
</script>
</body>
</html>