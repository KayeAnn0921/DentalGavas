<?php
include 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Replace this with how you get the actual patient ID
    $patient_id = 1; // Example: from session or login

    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $type_of_visit = $_POST['visitType'];
    $appointment_date = $_POST['appointmentDate'];
    $appointment_time = $_POST['appointmentTime'];
    $contact_number = $_POST['contactNumber'];
    $service_id = $_POST['service_id'];
    $status = 'pending'; // Default status
    
    // Check if the classification_id exists
    $check_stmt = $pdo->prepare("SELECT service_id FROM services WHERE service_id = :service_id");
    $check_stmt->execute([':service_id' => $service_id]);
    
    if ($check_stmt->rowCount() == 0) {
        echo "Error: The selected service (ID: $service_id) does not exist in our system.";
        exit;
    }

    try {
        // Prepare the insert statement (NOW including first_name and last_name)
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                first_name, last_name, patient_id, type_of_visit, 
                appointment_date, appointment_time, contact_number, 
                service_id, status
            ) VALUES (
                :first_name, :last_name, :patient_id, :type_of_visit, 
                :appointment_date, :appointment_time, :contact_number, 
                :service_id, :status
            )
        ");

        // Execute with bound parameters
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':patient_id' => $patient_id,
            ':type_of_visit' => $type_of_visit,
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time,
            ':contact_number' => $contact_number,
            ':service_id' => $service_id,
            ':status' => $status
        ]);

        // Success message and redirect
        echo "<script>
            alert('Appointment successfully saved!');
            window.location.href = 'appointmentlist.php';
        </script>";
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Invalid Request.";
}
?>
