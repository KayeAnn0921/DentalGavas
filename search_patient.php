<?php
include 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
    echo json_encode([]);
    exit;
}

$searchTerm = trim($_GET['search']);

try {
    // Search patients by first name, last name, contact number, or email
    $stmt = $pdo->prepare("
        SELECT patient_id, first_name, last_name, contact_number, email 
        FROM patients 
        WHERE (first_name LIKE :search1 OR last_name LIKE :search2 
               OR CONCAT(first_name, ' ', last_name) LIKE :search3
               OR contact_number LIKE :search4 OR email LIKE :search5)
        ORDER BY first_name, last_name
        LIMIT 10
    ");
    
    $searchPattern = '%' . $searchTerm . '%';
    $stmt->bindParam(':search1', $searchPattern);
    $stmt->bindParam(':search2', $searchPattern);
    $stmt->bindParam(':search3', $searchPattern);
    $stmt->bindParam(':search4', $searchPattern);
    $stmt->bindParam(':search5', $searchPattern);
    
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($patients);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>