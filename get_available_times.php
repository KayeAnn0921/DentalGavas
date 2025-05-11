<?php
include 'config.php';

header('Content-Type: text/html');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['date']) && isset($_GET['doctor'])) {
    $date = $_GET['date'];
    $doctor = $_GET['doctor'];
    
    try {
        // 1. Verify database connection
        $pdo->query("SELECT 1")->fetch();
        
        // 2. Check doctor availability
        $stmt = $pdo->prepare("SELECT start_time, end_time FROM doctor_schedule 
                              WHERE doctor_name = ? AND schedule_date = ? AND status = 'Available'");
        $stmt->execute([$doctor, $date]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            echo '<p>No schedule found for '.htmlspecialchars($doctor).' on '.htmlspecialchars($date).'</p>';
            exit;
        }
        
        // 3. Get booked appointments
        $stmt = $pdo->prepare("SELECT appointment_time FROM appointments 
                              WHERE doctor = ? AND appointment_date = ?");
        $stmt->execute([$doctor, $date]);
        $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // 4. Generate time slots
        $start = new DateTime($schedule['start_time']);
        $end = new DateTime($schedule['end_time']);
        $interval = new DateInterval('PT30M');
        
        $output = '<div class="time-slots">';
        $hasSlots = false;
        
        for ($current = clone $start; $current <= $end; $current->add($interval)) {
            $time = $current->format('H:i');
            $isBooked = in_array($time, $bookedTimes);
            
            if (!$isBooked) {
                $hasSlots = true;
                $output .= sprintf(
                    '<div id="slot-%s" class="time-slot available" onclick="selectTimeSlot(\'%s\')">%s</div>',
                    $time, $time, $time
                );
            } else {
                $output .= sprintf(
                    '<div id="slot-%s" class="time-slot booked">%s (Booked)</div>',
                    $time, $time
                );
            }
        }
        
        $output .= '</div>';
        
        echo $hasSlots ? $output : '<p>No available slots for this doctor/date</p>';
        
    } catch (PDOException $e) {
        error_log("DB Error: ".$e->getMessage());
        echo '<p>Database error. Please check logs.</p>';
    } catch (Exception $e) {
        error_log("Error: ".$e->getMessage());
        echo '<p>Error generating time slots</p>';
    }
} else {
    echo '<p>Missing date or doctor parameter</p>';
}
?>