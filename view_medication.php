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

    $stmt = $pdo->prepare("SELECT m.*, c.name AS category_name FROM medications m LEFT JOIN medicine_category c ON m.category_id = c.id WHERE m.id = ?");
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
<style>
    
 .medication-details-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(30,136,229,0.10);
            padding: 38px 48px 32px 48px;
            max-width: 600px;
            margin: 40px auto 0 auto;
        }
        .medication-details-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 28px;
            letter-spacing: 1px;
            text-align: center;
        }
        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 10px 0;
            font-size: 1.08rem;
        }
        .details-label {
            color: #2d3a4b;
            font-weight: 600;
            width: 160px;
            vertical-align: top;
        }
        .details-value {
            color: #333;
            font-weight: 400;
        }
        .details-value i {
            color: #1976d2;
            margin-right: 6px;
        }
        .details-value .category-pill {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            padding: 2px 12px;
            font-size: 13px;
            margin-left: 6px;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 18px;
        }
        .back-btn, .edit-btn {
            display: inline-block;
            padding: 10px 28px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            border: none;
            outline: none;
        }
        .back-btn {
            background: #f3f5f7;
            color: #1976d2;
            border: 1.5px solid #e3f2fd;
        }
        .back-btn:hover {
            background: #e3f2fd;
            color: #1256a3;
        }
        .edit-btn {
            background: #1976d2;
            color: #fff;
            border: 1.5px solid #1976d2;
        }
        .edit-btn:hover {
            background: #1256a3;
            color: #fff;
        }
        @media (max-width: 700px) {
            .medication-details-card {
                padding: 18px 8px 18px 8px;
            }
            .details-table td {
                font-size: 0.98rem;
            }
        }
</style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="medication-details-card">
            <div class="medication-details-title">
                <i class="fa fa-capsules"></i> Medication Details
            </div>
            <table class="details-table">
                <tr>
                    <td class="details-label">Category:</td>
                    <td class="details-value">
                        <i class="fa fa-folder"></i>
                        <?= htmlspecialchars($medication['category_name']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="details-label">Medication Name:</td>
                    <td class="details-value"><?= htmlspecialchars($medication['name']) ?></td>
                </tr>
                <tr>
                    <td class="details-label">Dosage:</td>
                    <td class="details-value"><?= htmlspecialchars($medication['dosage']) ?></td>
                </tr>
                <tr>
                    <td class="details-label">Description:</td>
                    <td class="details-value"><?= htmlspecialchars($medication['description']) ?: 'N/A' ?></td>
                </tr>
            </table>
            <div class="action-buttons">
                <a href="medication.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back to List</a>
                <a href="medication.php?action=edit&id=<?= $medication['id'] ?>" class="edit-btn"><i class="fa fa-pencil"></i> Edit</a>
            </div>
        </div>
    </div>
</body>
</html>