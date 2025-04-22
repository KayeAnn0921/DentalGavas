<?php
include 'config.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
  $stmt->execute([$id]);
}

header("Location: appointmentlist.php");
exit;
