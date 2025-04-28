<?php
include 'config.php';

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: patient.php");
    exit();
}

$patient_id = $_GET['id'];

try {
    // Fetch patient data
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        header("Location: patient.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching patient data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient - GAVAS DENTAL CLINIC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/patient.css">
    <style>
        .view-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 1000px;
        }
        
        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .view-header h1 {
            font-size: 24px;
            color: #333;
        }
        
        .back-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .view-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
            min-height: 20px;
        }
        
        .full-width {
            grid-column: span 2;
        }
        
        .minor-section {
            grid-column: span 2;
            padding: 15px;
            border: 1px dashed #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <section class="main">
        <div class="view-container">
            <div class="view-header">
                <h1>PATIENT DETAILS</h1>
                <a href="patient.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Patients
                </a>
            </div>
            
            <div class="view-details">
                <div class="detail-group">
                    <span class="detail-label">Patient ID:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['patient_id']) ?></div>
                </div>

                 <div class="detail-group">
                    <span class="detail-label">Visit Date:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['visit_date']) ?></div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Visit Type:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['visit_type']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Last Name:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['last_name']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">First Name:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['first_name']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Middle Name:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['middle_name']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Birthdate:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['birthdate']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Age:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['age']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Sex:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['sex']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Civil Status:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['civil_status']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Mobile Number:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['mobile_number']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Email Address:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['email_address']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Facebook Account:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['fb_account']) ?></div>
                </div>
                
                <div class="detail-group full-width">
                    <span class="detail-label">Home Address:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['home_address']) ?></div>
                </div>
                
                <div class="detail-group full-width">
                    <span class="detail-label">Work Address:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['work_address']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Occupation:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['occupation']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Office Contact Number:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['office_contact_number']) ?></div>
                </div>
                
                <?php if ($patient['age'] < 18): ?>
                <div class="minor-section">
                    <div class="detail-group">
                        <span class="detail-label">Parent/Guardian Name:</span>
                        <div class="detail-value"><?= htmlspecialchars($patient['parent_guardian_name']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Physician Name:</span>
                        <div class="detail-value"><?= htmlspecialchars($patient['physician_name']) ?></div>
                    </div>
                    
                    <div class="detail-group full-width">
                        <span class="detail-label">Physician Address:</span>
                        <div class="detail-value"><?= htmlspecialchars($patient['physician_address']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-group">
                    <span class="detail-label">Previous Dentists:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['previous_dentists']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Treatment Done:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['treatment_done']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Referred By:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['referred_by']) ?></div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Last Dental Visit:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['last_dental_visit']) ?></div>
                </div>
                
                <div class="detail-group full-width">
                    <span class="detail-label">Reason for Visit:</span>
                    <div class="detail-value"><?= htmlspecialchars($patient['reason_for_visit']) ?></div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="patient.php?edit=<?= $patient['patient_id'] ?>" class="back-btn" style="background-color: #4CAF50;">
                    <i class="fas fa-edit"></i> Edit Patient
                </a>
                <a href="patient.php?delete=<?= $patient['patient_id'] ?>" class="back-btn" style="background-color: #f44336;" onclick="return confirm('Are you sure you want to delete this patient?')">
                    <i class="fas fa-trash"></i> Delete Patient
                </a>
            </div>
        </div>
    </section>
</body>
</html>