<?php

// save_chart.php - Script to save tooth chart data
include 'config.php'; // Include database connection
session_start(); // Start the session

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'An error occurred'
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate database connection
        if (!$pdo) {
            throw new Exception('Database connection is not initialized.');
        }

        // Get patient ID from POST data
        $patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;
        if (!$patient_id) {
            throw new Exception('Patient ID is missing.');
        }

        // Get tooth chart data from POST
        $tooth_chart_data = isset($_POST['tooth_chart_data']) ? $_POST['tooth_chart_data'] : null;
        if (!$tooth_chart_data || !is_array($tooth_chart_data)) {
            throw new Exception('Tooth chart data is missing or invalid.');
        }

        // Get the current date for chart_date
        $chart_date = date('Y-m-d');

        // Start transaction
        $pdo->beginTransaction();

        // Insert each tooth chart entry into the database
        $stmt = $pdo->prepare("INSERT INTO dental_chart (patient_id, chart_date, tooth_number, condition, created_at, updated_at) VALUES (:patient_id, :chart_date, :tooth_number, :condition, NOW(), NOW())");

        foreach ($tooth_chart_data as $tooth_entry) {
            $tooth_number = isset($tooth_entry['tooth_number']) ? $tooth_entry['tooth_number'] : null;
            $condition = isset($tooth_entry['condition']) ? $tooth_entry['condition'] : null;

            if (!$tooth_number || !$condition) {
                throw new Exception('Invalid tooth chart entry.');
            }

            $stmt->execute([
                ':patient_id' => $patient_id,
                ':chart_date' => $chart_date,
                ':tooth_number' => $tooth_number,
                ':condition' => $condition
            ]);
        }

        // Commit transaction
        $pdo->commit();

        $response = [
            'status' => 'success',
            'message' => 'Dental chart saved successfully.'
        ];
    } catch (Exception $e) {
        // Rollback the transaction on error, if active
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Log the error for debugging
        error_log('Error in save_chart.php: ' . $e->getMessage());

        // Update the response with the specific error message
        $response = [
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>