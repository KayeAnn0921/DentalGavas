<?php
include 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $type_of_visit = $_POST['visitType'];
    $appointment_date = $_POST['appointmentDate'];
    $appointment_time = $_POST['appointmentTime'];
    $contact_number = $_POST['contactNumber'];
    $service_id = isset($_POST['service_id']) && $_POST['service_id'] !== '' ? $_POST['service_id'] : null;
    $doctor = $_POST['doctor'];
    $existing_patient_id = isset($_POST['existing_patient_id']) ? $_POST['existing_patient_id'] : null;
    $status = 'pending'; // Default status

    // Validate contact number format
    if (!preg_match('/^(09|\+639)\d{9}$/', $contact_number)) {
        echo "<script>
            alert('Please enter a valid Philippine mobile number (e.g., 09123456789 or +639123456789)');
            window.history.back();
        </script>";
        exit;
    }

    // Only check if service_id is provided
    if ($service_id !== null && $service_id !== '') {
        $check_stmt = $pdo->prepare("SELECT service_id FROM services WHERE service_id = :service_id");
        $check_stmt->execute([':service_id' => $service_id]);
        if ($check_stmt->rowCount() == 0) {
            echo "<script>
                alert('Error: The selected service (ID: $service_id) does not exist in our system.');
                window.history.back();
            </script>";
            exit;
        }
    }

    // Determine patient_id
    $patient_id = null;
    
    if ($existing_patient_id) {
        // Use existing patient ID
        $patient_id = $existing_patient_id;
    } else {
        // Check if patient already exists by name and contact number
        try {
            $check_patient_stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE 
                                               first_name = :first_name AND last_name = :last_name 
                                               AND mobile_number = :mobile_number LIMIT 1");
            $check_patient_stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':mobile_number' => $contact_number
            ]);
            $existing_patient = $check_patient_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_patient) {
                // Patient already exists, use existing ID
                $patient_id = $existing_patient['patient_id'];
            } else {
                // Create new minimal patient record
                $create_patient_stmt = $pdo->prepare("INSERT INTO patients (
                    first_name, last_name, mobile_number, visit_date, visit_type
                ) VALUES (:first_name, :last_name, :mobile_number, :visit_date, :visit_type)");
                
                $create_patient_stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':mobile_number' => $contact_number,
                    ':visit_date' => $appointment_date,
                    ':visit_type' => $type_of_visit
                ]);
                
                $patient_id = $pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            echo "<script>
                alert('Error processing patient information: " . addslashes($e->getMessage()) . "');
                window.history.back();
            </script>";
            exit;
        }
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

        // Extract start and end time from appointmentTime (e.g., "8:00 AM - 11:00 AM")
        if (strpos($appointment_time, '-') === false) {
            throw new Exception("Invalid appointment time format.");
        }
        list($slot_start, $slot_end) = explode('-', $appointment_time);
        $slot_start = trim($slot_start);
        $slot_end = trim($slot_end);

        // Convert AM/PM to 24-hour format
        function to24Hour($timeStr) {
            return date("H:i:s", strtotime($timeStr));
        }
        $slot_start_24 = to24Hour($slot_start);
        $slot_end_24 = to24Hour($slot_end);

        $start_time = $doctor_availability['start_time']; // e.g., "08:00:00"
        $end_time = $doctor_availability['end_time'];     // e.g., "16:00:00"

        if ($slot_start_24 < $start_time || $slot_end_24 > $end_time) {
            throw new Exception("The selected time is outside the doctor's working hours.");
        }

        // Check for existing appointments that overlap with the selected slot
        $conflict_stmt = $pdo->prepare("
            SELECT appointment_id 
            FROM appointments 
            WHERE doctor = :doctor 
            AND appointment_date = :appointment_date 
            AND (
                (STR_TO_DATE(SUBSTRING_INDEX(appointment_time, '-', 1), '%h:%i %p') < STR_TO_DATE(:slot_end, '%H:%i:%s')
                 AND STR_TO_DATE(SUBSTRING_INDEX(appointment_time, '-', -1), '%h:%i %p') > STR_TO_DATE(:slot_start, '%H:%i:%s'))
            )
        ");
        $conflict_stmt->execute([
            ':doctor' => $doctor,
            ':appointment_date' => $appointment_date,
            ':slot_start' => $slot_start_24,
            ':slot_end' => $slot_end_24
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
            ':service_id' => $service_id !== null ? $service_id : null,
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