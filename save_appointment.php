<?php
include 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Replace this with how you get the actual patient ID
    $patient_id = 1; // Example: from session or login
    $type_of_visit = $_POST['visitType'];
    $appointment_date = $_POST['appointmentDate'];
    $appointment_time = $_POST['appointmentTime'];
    $contact_number = $_POST['contactNumber'];
    $classification_id = $_POST['classification_id'];
    $status = 'pending'; // Default status
    
    // Debug: Check if the classification_id exists in the services table
    $check_stmt = $pdo->prepare("SELECT classification_id FROM classification WHERE classification_id = :classification_id");
    $check_stmt->execute([':classification_id' => $classification_id]);
    
    if ($check_stmt->rowCount() == 0) {
        echo "Error: The selected service (ID: $classification_id) does not exist in our system.";
        exit;
    }

    try {
        // Prepare the insert statement
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                patient_id, type_of_visit, appointment_date, appointment_time, 
                contact_number, classification_id, status
            ) VALUES (
                :patient_id, :type_of_visit, :appointment_date, :appointment_time, 
                :contact_number, :classification_id, :status
            )
        ");

        // Execute with bound parameters
        $stmt->execute([
            ':patient_id' => $patient_id,
            ':type_of_visit' => $type_of_visit,
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time,
            ':contact_number' => $contact_number,
            ':classification_id' => $classification_id,
            ':status' => $status
        ]);

        echo "<script>
            alert('Appointment successfully saved!');
            window.location.href = 'schedule.php';
        </script>";
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Invalid Request.";
}
?>