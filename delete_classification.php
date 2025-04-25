<?php
include 'config.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: services.php?error=noid");
    exit();
}

$id = intval($_GET['id']);

try {
    // First check if this service has any child services
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM classification WHERE parent_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $childCount = $stmt->fetchColumn();
    
    if ($childCount > 0) {
        // Has child services, can't delete
        header("Location: services.php?error=haschildren");
        exit();
    }
    
    // Now check if this service is being used in any appointments or other important records
    // This depends on your database structure - add similar checks if needed
    
    // If no dependencies, proceed with deletion
    $stmt = $pdo->prepare("DELETE FROM classification WHERE classification_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: services.php?deleted=1");
    exit();
} catch (PDOException $e) {
    header("Location: services.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>