<?php include 'config.php'; ?>
  <?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scheduling | Gavas Dental Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
    body {
      background: #f4f7fa;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .main-content {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left:  20px; /* Adjust based on sidebar width */
    }
    .schedule-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(25,118,210,0.10);
      padding: 40px 36px 32px 36px;
      max-width: 650px;
      width: 100%;
      margin: 40px 0;
    }
    .form-header {
      color: #1976d2;
      font-size: 2em;
      font-weight: 700;
      text-align: center;
      margin-bottom: 8px;
      letter-spacing: 1px;
    }
    .doctor-schedule-btn {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 10px;
    }
    .doctor-schedule-btn .btn {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 18px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .doctor-schedule-btn .btn:hover {
      background: #1256a3;
    }
    form {
      margin-top: 18px;
    }
    .form-row {
      display: flex;
      gap: 18px;
      margin-bottom: 18px;
    }
    .form-group {
      flex: 1 1 0;
      display: flex;
      flex-direction: column;
    }
    .form-group label {
      font-weight: 500;
      margin-bottom: 5px;
      color: #1976d2;
      font-size: 1em;
    }
    .form-group input,
    .form-group select {
      padding: 9px 12px;
      border-radius: 6px;
      border: 1px solid #b0bec5;
      font-size: 1em;
      background: #f7fbff;
      transition: border 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus {
      border: 1.5px solid #1976d2;
      outline: none;
      background: #e3f2fd;
    }
    #timeSlotsContainer {
      margin-top: 6px;
      min-height: 36px;
    }
    .time-slot {
      display: inline-block;
      background: #e3f0fc;
      color: #1976d2;
      border-radius: 5px;
      padding: 6px 12px;
      margin: 3px 5px 3px 0;
      cursor: pointer;
      font-weight: 500;
      border: 1px solid #e3e7ea;
      transition: background 0.2s, color 0.2s;
    }
    .time-slot.selected,
    .time-slot:hover {
      background: #1976d2;
      color: #fff;
    }
    .btn[type="submit"] {
      width: 100%;
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 12px 0;
      font-size: 1.1em;
      font-weight: 700;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.2s;
    }
    .btn[type="submit"]:hover {
      background: #1256a3;
    }
    .alert-success {
      background: #e3f9e5;
      color: #388e3c;
      border-radius: 7px;
      padding: 12px 18px;
      margin-bottom: 18px;
      text-align: center;
      font-weight: 600;
    }
    @media (max-width: 900px) {
      .schedule-card { padding: 18px 6vw; }
      .form-row { flex-direction: column; gap: 0; }
    }
    @media (max-width: 600px) {
      .schedule-card { padding: 8px 2vw; }
      .form-header { font-size: 1.3em; }
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
function pad(num) {
  return num < 10 ? '0' + num : num;
}
function toAmPm(hour, minute = 0) {
  let h = parseInt(hour);
  let m = parseInt(minute);
  let ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12;
  if (h === 0) h = 12;
  return h + ':' + pad(m) + ' ' + ampm;
}
function timeToMinutes(t) {
  const [h, m] = t.split(':');
  return parseInt(h) * 60 + parseInt(m);
}
function isOverlap(startA, endA, startB, endB) {
  return startA < endB && endA > startB;
}
// Example lunch break (static, can be made dynamic)
const lunchStart = "12:00";
const lunchEnd = "13:00";

function fetchAvailableTimeSlots(date, doctor, duration) {
  $('#timeSlotsContainer').html('<p><i class="fas fa-spinner fa-spin"></i> Loading time slots...</p>');
  $.ajax({
    url: 'doctor_schedule.php',
    type: 'GET',
    data: { fetch_schedule: true, doctor: doctor, date: date },
    success: function(response) {
      let schedules;
      try {
        schedules = JSON.parse(response);
      } catch (e) {
        $('#timeSlotsContainer').html('<p class="text-danger">Invalid schedule data.</p>');
        return;
      }
      if (schedules.error) {
        $('#timeSlotsContainer').html(`<p class="text-danger">${schedules.error}</p>`);
        return;
      }
      let availableSlots = '';
      if (!Array.isArray(schedules) || schedules.length === 0) {
        $('#timeSlotsContainer').html('<p class="text-danger">No schedule found.</p>');
        return;
      }
      schedules.forEach(schedule => {
        if (schedule.status === 'Available') {
          const startTime = schedule.start_time.slice(0,5);
          const endTime = schedule.end_time.slice(0,5);
          const startMin = timeToMinutes(startTime);
          const endMin = timeToMinutes(endTime);
          const lunchStartMin = timeToMinutes(lunchStart);
          const lunchEndMin = timeToMinutes(lunchEnd);
          const durMin = parseInt(duration) * 60;

          // Show slots every hour, but only if the full duration fits
          for (let min = startMin; min + durMin <= endMin; min += 60) {
            let slotStart = min;
            let slotEnd = min + durMin;
            // Skip if overlaps lunch
            if (isOverlap(slotStart, slotEnd, lunchStartMin, lunchEndMin)) continue;
            const formattedStart = toAmPm(Math.floor(slotStart/60), slotStart%60);
            const formattedEnd = toAmPm(Math.floor(slotEnd/60), slotEnd%60);
            const slotLabel = `${formattedStart} - ${formattedEnd}`;
            availableSlots += `<div class="time-slot available" onclick="selectTimeSlot('${formattedStart}','${formattedEnd}')">${slotLabel}</div>`;
          }
        }
      });
      $('#timeSlotsContainer').html(availableSlots || '<p>No available slots for this date and duration.</p>');
    },
    error: function() {
      $('#timeSlotsContainer').html('<p class="text-danger">Failed to load time slots. Please check your internet connection or try again later.</p>');
    }
  });
}
function selectTimeSlot(start, end) {
  $('#appointmentTime').val(start + '-' + end);
  $('.time-slot').removeClass('selected');
  $(`.time-slot:contains('${start} - ${end}')`).addClass('selected');
}

let availableDates = [];

function normalizeDate(dateStr) {
  // If already in YYYY-MM-DD, return as is
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
  // If in DD/MM/YYYY, convert to YYYY-MM-DD
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateStr)) {
    const [d, m, y] = dateStr.split('/');
    return `${y}-${m}-${d}`;
  }
  return dateStr;
}

function updateSlots() {
  let date = $('#appointmentDate').val();
  const doctor = $('#doctor').val();
  const duration = $('#duration').val();
  date = normalizeDate(date);
  if (date && doctor && duration) {
    fetchAvailableTimeSlots(date, doctor, duration);
  } else {
    $('#timeSlotsContainer').html('<p>Please select a date, doctor, and duration to view available time slots.</p>');
  }
}

function fetchDoctorAvailableDates(doctor) {
  if (!doctor) {
    availableDates = [];
    return;
  }
  $.get('doctor_schedule.php', {fetch_available_dates: 1, doctor: doctor}, function(data) {
    try {
      availableDates = JSON.parse(data);
    } catch (e) {
      availableDates = [];
    }
  });
}

$(function() {
  // Fetch available dates when doctor changes
  $("#doctor").on("change", function() {
    fetchDoctorAvailableDates($(this).val());
    $("#appointmentDate").val(""); // Clear date when doctor changes
    $('#timeSlotsContainer').html('<p>Please select a date, doctor, and duration to view available time slots.</p>');
  });

  // Initial fetch if doctor is pre-selected
  if ($("#doctor").val()) {
    fetchDoctorAvailableDates($("#doctor").val());
  }

  // Validate date on change
  $("#appointmentDate").on("change", function() {
    let date = $(this).val();
    date = normalizeDate(date);
    if (availableDates.length && !availableDates.includes(date)) {
      alert("Selected date is not available for this doctor.");
      $(this).val('');
      $('#timeSlotsContainer').html('<p>Please select a date, doctor, and duration to view available time slots.</p>');
    }
  });

  // Update slots when any relevant field changes
  $('#appointmentDate, #doctor, #duration').on('change', updateSlots);
});
  </script>
 </head>
<body>
<div class="main-content">
  <div class="schedule-card">
    <div class="doctor-schedule-btn">
      <a href="doctor_schedule.php" class="btn">
        <i class="fas fa-calendar-alt"></i> Doctor Schedule
      </a>
    </div>
    <h1 class="form-header">Scheduling</h1>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="alert alert-success">Appointment successfully scheduled!</div>
    <?php endif; ?>
    <form action="save_appointment.php" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name:</label>
          <input type="text" id="first_name" name="first_name" required/>
        </div>
        <div class="form-group">
          <label for="last_name">Last Name:</label>
          <input type="text" id="last_name" name="last_name" required/>
        </div>
      </div>
      <div class="form-row">
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
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="doctor">Doctor:</label>
          <select id="doctor" name="doctor" required>
            <option value="">-- Select Doctor --</option>
            <option value="Dr. Glenn Gavas">Dr. Glenn Gavas</option>
            <option value="Dr. Anna Patricia Gavas-Paña">Dr. Anna Patricia Gavas-Paña</option>
          </select>
        </div>
        <div class="form-group">
          <label for="duration">Duration (hours):</label>
          <select id="duration" name="duration" required>
            <option value="1">1 hour</option>
            <option value="2">2 hours</option>
            <option value="3">3 hours</option>
            <option value="4">4 hours</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:2;">
          <label for="appointmentTime">Appointment Time:</label>
          <input type="hidden" id="appointmentTime" name="appointmentTime" required/>
          <div id="timeSlotsContainer">
            <p>Please select a date, doctor, and duration to view available time slots.</p>
          </div>
        </div>
        <div class="form-group">
          <label for="contactNumber">Contact Number:</label>
          <input type="text" id="contactNumber" name="contactNumber" placeholder="e.g. 09123456789" required/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:1;">
          <label for="service_id">Service:</label>
          <select id="service_id" name="service_id">
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
      </div>
      <button type="submit" class="btn">SUBMIT</button>
    </form>
  </div>
</div>
</body>
</html>