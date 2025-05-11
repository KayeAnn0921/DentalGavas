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
    $doctor = $_POST['doctor'];
    $status = 'pending'; // Default status
    
    // Validate contact number format
    if (!preg_match('/^(09|\+639)\d{9}$/', $contact_number)) {
        echo "<script>
            alert('Please enter a valid Philippine mobile number (e.g., 09123456789 or +639123456789)');
            window.history.back();
        </script>";
        exit;
    }
    
    // Check if the service exists
    $check_stmt = $pdo->prepare("SELECT service_id FROM services WHERE service_id = :service_id");
    $check_stmt->execute([':service_id' => $service_id]);
    
    if ($check_stmt->rowCount() == 0) {
        echo "Error: The selected service (ID: $service_id) does not exist in our system.";
        exit;
    }

    // Check doctor availability for the selected time
    try {
        // First check if doctor is available on this date
        $availability_stmt = $pdo->prepare("
            SELECT start_time, end_time 
            FROM doctor_schedule 
            WHERE doctor_name = :doctor 
            AND schedule_date = :appointment_date 
            AND status = 'Available'
        ");
        $availability_stmt->execute([
            ':doctor' => $doctor,
            ':appointment_date' => $appointment_date
        ]);
        $doctor_availability = $availability_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$doctor_availability) {
            throw new Exception("The selected doctor is not available on this date.");
        }
        
        // Check if the selected time is within doctor's working hours
        $selected_time = strtotime($appointment_time);
        $start_time = strtotime($doctor_availability['start_time']);
        $end_time = strtotime($doctor_availability['end_time']);
        
        if ($selected_time < $start_time || $selected_time > $end_time) {
            throw new Exception("The selected time is outside the doctor's working hours.");
        }
        
        // Check for existing appointments at the same time
        $conflict_stmt = $pdo->prepare("
            SELECT appointment_id 
            FROM appointments 
            WHERE doctor = :doctor 
            AND appointment_date = :appointment_date 
            AND appointment_time = :appointment_time
        ");
        $conflict_stmt->execute([
            ':doctor' => $doctor,
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time
        ]);
        
        if ($conflict_stmt->rowCount() > 0) {
            throw new Exception("This time slot is already booked. Please choose another time.");
        }

        // Prepare the insert statement (NOW including first_name, last_name, and doctor)
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                first_name, last_name, patient_id, type_of_visit, 
                appointment_date, appointment_time, contact_number, 
                service_id, status, doctor
            ) VALUES (
                :first_name, :last_name, :patient_id, :type_of_visit, 
                :appointment_date, :appointment_time, :contact_number, 
                :service_id, :status, :doctor
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
            ':status' => $status,
            ':doctor' => $doctor
        ]);

        // Success message and redirect
        echo "<script>
            alert('Appointment successfully saved!');
            window.location.href = 'appointmentlist.php';
        </script>";
        
    } catch (PDOException $e) {
        echo "<script>
            alert('Database Error: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    } catch (Exception $e) {
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid Request.');
        window.location.href = 'schedule.php';
    </script>";
}
?>