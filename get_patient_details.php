<?php
include 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if patient_id is provided
if (!isset($_GET['patient_id']) || empty($_GET['patient_id'])) {
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

$patient_id = $_GET['patient_id'];

try {
    // Fetch patient details
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($patient) {
        // Return patient data
        echo json_encode($patient);
    } else {
        echo json_encode(['error' => 'Patient not found']);
    }
    
} catch (PDOException $e) {
    error_log("Error fetching patient details: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
?>