<?php
include 'config.php';

// Handle "Mark as Arrived" action
if (isset($_GET['arrive'])) {
    $arrive_id = intval($_GET['arrive']);
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'arrived' WHERE appointment_id = ?");
    $stmt->execute([$arrive_id]);
    // Redirect back to the list to show updated status and button
    header("Location: appointmentlist.php");
    exit;
}

if (isset($_GET['confirm'])) {
    $confirm_id = intval($_GET['confirm']);
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
    $stmt->execute([$confirm_id]);
    // Redirect back to the list to avoid resubmission
    header("Location: appointmentlist.php");
    exit;
}

$today = date('Y-m-d');
$autoCancel = $pdo->prepare(
    "UPDATE appointments 
     SET status = 'cancelled' 
     WHERE appointment_date < :today 
       AND (status = 'pending' OR status = 'confirmed')"
);
$autoCancel->execute(['today' => $today]);
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
    body {
      background: #f4f7fa;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .main-content {
      width: 1200px;
      margin: 40px auto 30px auto;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 24px rgba(25,118,210,0.10);
      padding: 36px 36px 24px 36px;
      margin-left: 350px;
    }
    .form-header {
      color: #1976d2;
      font-size: 2.1em;
      margin-bottom: 18px;
      letter-spacing: 1px;
      font-weight: 700;
      text-align: center;
    }
    .status-message.error {
      background: #ffebee;
      color: #b71c1c;
      padding: 12px 18px;
      border-radius: 7px;
      margin-bottom: 18px;
      font-weight: 500;
      text-align: center;
    }
    .search-container {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 18px;
    }
    .search-container form {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .search-container input[type="text"] {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #b0bec5;
      font-size: 1em;
      background: #f7fbff;
      transition: border 0.2s;
    }
    .search-container input[type="text"]:focus {
      border: 1.5px solid #1976d2;
      outline: none;
      background: #e3f2fd;
    }
    .search-container button {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .search-container button:hover {
      background: #1256a3;
    }
    .modern-table-responsive {
      width: 100%;
      overflow-x: auto;
      background: #f8fbfd;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(25,118,210,0.06);
      margin-bottom: 24px;
    }
    .modern-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 900px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
    }
    .modern-table th, .modern-table td {
      padding: 14px 12px;
      text-align: left;
      border-bottom: 1px solid #e3e7ea;
      background: #fff;
      font-size: 1.04em;
    }
    .modern-table th {
      background: #e3f0fc;
      color: #1976d2;
      font-weight: 700;
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .modern-table tr:hover:not(.cancelled-row) {
      background: #f1f8ff;
      transition: background 0.2s;
    }
    .modern-table tr.cancelled-row {
      background: #fff5f5 !important;
      color: #b71c1c !important;
    }
    .modern-table .modern-action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }
    .modern-btn {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 5px;
      font-size: 1em;
      color: #fff;
      background: #1976d2;
      text-decoration: none;
      transition: background 0.2s;
      border: none;
      cursor: pointer;
    }
    .modern-btn.edit { background: #1976d2; }
    .modern-btn.delete { background: #e53935; }
    .modern-btn.fill-form { background: #43a047; }
    .modern-btn.arrived { background: #22c55e; }
    .modern-btn.confirm { background: #fbc02d; color: #333; }
    .modern-btn.edit:hover { background: #1256a3; }
    .modern-btn.delete:hover { background: #b71c1c; }
    .modern-btn.fill-form:hover { background: #2e7031; }
    .modern-btn.arrived:hover { background: #15803d; }
    .modern-btn.confirm:hover { background: #b28704; color: #fff; }
    /* Cancelled row indicator */
    .cancelled-row td, .cancelled-row th {
      position: relative;
      background: #fff5f5 !important;
      color: #b71c1c !important;
      font-weight: 500;
    }
    .cancelled-row td:first-child::before {
      content: "";
      display: block;
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 5px;
      background: #e53935;
      border-radius: 3px;
    }
    @media (max-width: 1300px) {
      .main-content { width: 98vw; padding: 10px 2vw; }
      .modern-table { min-width: 700px; }
    }
    @media (max-width: 900px) {
      .main-content { width: 100vw; padding: 2vw; }
      .modern-table { min-width: 600px; font-size: 0.97em; }
      .modern-table th, .modern-table td { padding: 10px 6px; }
    }
    @media (max-width: 700px) {
      .modern-table { min-width: 500px; font-size: 0.93em; }
      .modern-table th, .modern-table td { padding: 8px 4px; }
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <h1 class="form-header"><i class="fa fa-calendar-check" style="color:#1976d2"></i> Appointment List</h1>

  <?php if (isset($statusUpdateError)): ?>
    <div class="status-message error">
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

  <div class="modern-table-responsive">
    <table class="modern-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Patient</th>
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
      SELECT a.*, s.name AS service_name, s.price
      FROM appointments a
      LEFT JOIN services s ON a.service_id = s.service_id
      WHERE a.first_name LIKE :search 
          OR a.last_name LIKE :search
          OR a.appointment_id LIKE :search 
          OR a.type_of_visit LIKE :search 
          OR a.appointment_date LIKE :search 
          OR a.appointment_time LIKE :search 
          OR a.contact_number LIKE :search 
          OR a.status LIKE :search
      ORDER BY a.appointment_date DESC, a.appointment_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($appointments) {
        foreach ($appointments as $appt) {
            $currentStatus = strtolower($appt['status'] ?: 'pending');
            $rowClass = ($currentStatus === 'cancelled') ? 'cancelled-row' : '';
            echo "<tr class='$rowClass'>";
            echo "<td>" . htmlspecialchars($appt['appointment_id']) . "</td>";
            echo "<td>
                    <div class='patient-info'>
                        <div class='patient-name'>" . htmlspecialchars($appt['first_name']) . " " . htmlspecialchars($appt['last_name']) . "</div>
                    </div>
                  </td>";
            echo "<td><span class='type-badge type-" . strtolower($appt['type_of_visit']) . "'>" . htmlspecialchars($appt['type_of_visit']) . "</span></td>";
            echo "<td>" . htmlspecialchars($appt['appointment_date']) . "</td>";

            // Display time as range if possible, else convert to AM/PM
            echo "<td>";
            if (
                strpos($appt['appointment_time'], 'AM') !== false ||
                strpos($appt['appointment_time'], 'PM') !== false ||
                strpos($appt['appointment_time'], '-') !== false
            ) {
                echo htmlspecialchars($appt['appointment_time']);
            } else {
                echo date("g:i A", strtotime($appt['appointment_time']));
            }
            echo "</td>";

            echo "<td>" . htmlspecialchars($appt['contact_number']) . "</td>";
            echo "<td>" . htmlspecialchars($appt['service_name'] ?? 'Not specified') . "</td>";
            echo "<td>₱" . number_format($appt['price'] ?? 0, 2) . "</td>";

            // Show only the status badge (no dropdown)
            echo "<td><span class='status-badge {$currentStatus}'>" . ucfirst($currentStatus) . "</span></td>";

            echo "<td class='modern-action-buttons'>";
            echo "<a href='edit_appointment.php?id=" . htmlspecialchars($appt['appointment_id']) . "' class='modern-btn edit'><i class='fas fa-edit'></i></a> ";
            echo "<a href='delete_appointment.php?id=" . htmlspecialchars($appt['appointment_id']) . "' class='modern-btn delete' onclick='return confirm(\"Are you sure you want to delete this appointment?\")'><i class='fas fa-trash'></i></a> ";
            if ($currentStatus == 'pending') {
                echo "<a href='appointmentlist.php?confirm=" . htmlspecialchars($appt['appointment_id']) . "' class='modern-btn confirm'>Confirm</a> ";
            }
            if ($currentStatus == 'confirmed') {
                echo "<a href='appointmentlist.php?arrive=" . htmlspecialchars($appt['appointment_id']) . "' class='modern-btn arrived'>Mark as Arrived</a> ";
            } elseif ($currentStatus == 'arrived') {
                echo "<a href='patient.php?appointment_id=" . htmlspecialchars($appt['appointment_id']) . "' class='modern-btn fill-form'>Fill Patient Info</a> ";
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
</body>
</html>