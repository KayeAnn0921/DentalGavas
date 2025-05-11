<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scheduling | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/schedule.css"/>
  <style>
    .btn {
      display: inline-block;
      padding: 10px 15px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
    }

    .btn:hover { background-color: #0056b3; }

    .doctor-schedule-btn { margin-bottom: 20px; text-align: right; }

    .time-slots {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .time-slot {
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }

    .time-slot.available {
      background-color: #28a745;
      color: white;
    }

    .time-slot.booked {
      background-color: #dc3545;
      color: white;
      cursor: not-allowed;
    }

    .time-slot.selected {
      background-color: #007bff;
      color: white;
    }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
function fetchAvailableTimeSlots(date, doctor) {
  // Display a loading message while fetching data
  $('#timeSlotsContainer').html('<p><i class="fas fa-spinner fa-spin"></i> Loading time slots...</p>');

  $.ajax({
    url: 'doctor_schedule.php',
    type: 'GET',
    data: { fetch_schedule: true, doctor: doctor, date: date }, // Ensure both parameters are included
    success: function(response) {
      try {
        const schedules = JSON.parse(response);

        if (schedules.error) {
          $('#timeSlotsContainer').html(`<p class="text-danger">${schedules.error}</p>`);
          return;
        }

        let availableSlots = '';
        schedules.forEach(schedule => {
          if (schedule.status === 'Available') {
            const startTime = new Date(`1970-01-01T${schedule.start_time}`);
            const endTime = new Date(`1970-01-01T${schedule.end_time}`);
            const interval = 30; // 30 minutes

            for (let time = startTime; time < endTime; time.setMinutes(time.getMinutes() + interval)) {
              const formattedTime = time.toTimeString().slice(0, 5);
              availableSlots += `<div class="time-slot available" onclick="selectTimeSlot('${formattedTime}')">${formattedTime}</div>`;
            }
          }
        });

        $('#timeSlotsContainer').html(availableSlots || '<p>No available slots for this date.</p>');
      } catch (error) {
        $('#timeSlotsContainer').html('<p class="text-danger">An error occurred while processing the schedule data. Please try again later.</p>');
      }
    },
    error: function(xhr, status, error) {
      $('#timeSlotsContainer').html('<p class="text-danger">Failed to load time slots. Please check your internet connection or try again later.</p>');
    }
  });
}

    function selectTimeSlot(time) {
      $('#appointmentTime').val(time);
      $('.time-slot').removeClass('selected');
      $(`.time-slot:contains('${time}')`).addClass('selected');
    }

  $(document).ready(function() {
  $('#appointmentDate, #doctor').on('change', function() {
    const date = $('#appointmentDate').val();
    const doctor = $('#doctor').val();

    if (date && doctor) {
      fetchAvailableTimeSlots(date, doctor);
    } else {
      $('#timeSlotsContainer').html('<p>Please select both a date and a doctor to view available time slots.</p>');
    }
  });
});
  </script>
</head>

<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success">Appointment successfully scheduled!</div>
  <?php endif; ?>

  <div class="scheduling-form">
    <div class="doctor-schedule-btn">
      <a href="doctor_schedule.php" class="btn">
        <i class="fas fa-calendar-alt"></i> Doctor Schedule
      </a>
    </div>

    <h1 class="form-header">Scheduling</h1>
    <form action="save_appointment.php" method="POST">
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
        <label for="doctor">Doctor:</label>
        <select id="doctor" name="doctor" required>
          <option value="">-- Select Doctor --</option>
          <option value="Dr. Glenn Gavas">Dr. Glenn Gavas</option>
          <option value="Dr. Anna Patricia Gavas-Paña">Dr. Anna Patricia Gavas-Paña</option>
        </select>
      </div>

      <div class="form-group">
        <label for="appointmentTime">Appointment Time:</label>
        <input type="hidden" id="appointmentTime" name="appointmentTime" required/>
        <div id="timeSlotsContainer">
          <p>Please select a date and doctor first</p>
        </div>
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
            $stmt = $pdo->prepare("SELECT service_id, name, parent_id, price FROM services ORDER BY parent_id, service_id");
            $stmt->execute();
            $classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            function buildOptions($data, $parent = null, $indent = 0) {
              foreach ($data as $row) {
                if ($row['parent_id'] == $parent) {
                  echo '<option value="' . htmlspecialchars($row['service_id']) . '">' .
                    str_repeat('&nbsp;&nbsp;', $indent) . htmlspecialchars($row['name']) .
                    ' (₱' . number_format($row['price'], 2) . ')</option>';
                  buildOptions($data, $row['service_id'], $indent + 1);
                }
              }
            }

            buildOptions($classifications);
          } catch (PDOException $e) {
            echo '<option>Error loading services</option>';
          }
          ?>
        </select>
      </div>

      <button type="submit" class="btn">SUBMIT</button>
    </form>
  </div>
</div>
</body>
</html>
