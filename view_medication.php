<?php
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: medication.php");
    exit();
}

$id = $_GET['id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE id = ?");
    $stmt->execute([$id]);
    $medication = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$medication) {
        header("Location: medication.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medication - GAVAS DENTAL CLINIC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/medication.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <section id="medication">
            <h2>Medication Details</h2>
            
            <div class="medication-details">
                <div class="detail-row">
                    <span class="detail-label">ID:</span>
                    <span class="detail-value"><?= htmlspecialchars($medication['id']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?= htmlspecialchars($medication['name']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dosage:</span>
                    <span class="detail-value"><?= htmlspecialchars($medication['dosage']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value"><?= htmlspecialchars($medication['description']) ?: 'N/A' ?></span>
                </div>
                
                <div class="action-buttons" style="margin-top: 20px;">
                    <a href="medication.php" class="back-btn">Back to List</a>
                    <a href="?action=edit&id=<?= $medication['id'] ?>" class="edit-btn">Edit</a>
                </div>
            </div>
        </section>
    </div>
</body>
</html>