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
    
    // Fetch health questionnaire data
    $healthStmt = $pdo->prepare("SELECT * FROM health_questionnaire WHERE patient_id = ? LIMIT 1");
    $healthStmt->execute([$patient_id]);
    $healthQuestionnaire = $healthStmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch health conditions if questionnaire exists
    $conditions = [];
    if ($healthQuestionnaire) {
        $conditionStmt = $pdo->prepare("SELECT condition_name FROM health_conditions WHERE health_questionnaire_id = ?");
        $conditionStmt->execute([$healthQuestionnaire['health_questionnaire_id']]);
        $conditions = $conditionStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
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
        
        /* Styles for health questionnaire section */
        .health-section {
            grid-column: span 2;
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f9ff;
            border-radius: 8px;
            border: 1px solid #d0e3ff;
        }
        
        .health-section h2 {
            color: #2c5282;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #d0e3ff;
        }
        
        .conditions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .condition-tag {
            background-color: #ebf8ff;
            color: #2b6cb0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
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
                    <span class="detail-label">Service Availed:</span>
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
                
                <!-- Health Questionnaire Section -->
                <?php if ($healthQuestionnaire): ?>
                <div class="health-section">
                    <h2>HEALTH QUESTIONNAIRE</h2>
                    
                    <div class="detail-group">
                        <span class="detail-label">Good Health:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['good_health']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Under Medical Condition:</span>
                        <div class="detail-value">
                            <?= htmlspecialchars($healthQuestionnaire['medical_condition']) ?>
                            <?php if ($healthQuestionnaire['medical_condition'] === 'Yes' && !empty($healthQuestionnaire['medical_condition_details'])): ?>
                                <br><small><?= htmlspecialchars($healthQuestionnaire['medical_condition_details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Serious Illness/Surgery:</span>
                        <div class="detail-value">
                            <?= htmlspecialchars($healthQuestionnaire['serious_illness']) ?>
                            <?php if ($healthQuestionnaire['serious_illness'] === 'Yes' && !empty($healthQuestionnaire['serious_illness_details'])): ?>
                                <br><small><?= htmlspecialchars($healthQuestionnaire['serious_illness_details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Hospitalized:</span>
                        <div class="detail-value">
                            <?= htmlspecialchars($healthQuestionnaire['hospitalized']) ?>
                            <?php if ($healthQuestionnaire['hospitalized'] === 'Yes' && !empty($healthQuestionnaire['hospitalized_details'])): ?>
                                <br><small><?= htmlspecialchars($healthQuestionnaire['hospitalized_details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Taking Medication:</span>
                        <div class="detail-value">
                            <?= htmlspecialchars($healthQuestionnaire['medication']) ?>
                            <?php if ($healthQuestionnaire['medication'] === 'Yes' && !empty($healthQuestionnaire['medication_details'])): ?>
                                <br><small><?= htmlspecialchars($healthQuestionnaire['medication_details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Smokes:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['smoke']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Uses Alcohol:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['alcohol']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Uses Drugs:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['drugs']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Allergies:</span>
                        <div class="detail-value">
                            <?= htmlspecialchars($healthQuestionnaire['allergy']) ?>
                            <?php if ($healthQuestionnaire['allergy'] === 'Yes' && !empty($healthQuestionnaire['allergy_details'])): ?>
                                <br><small><?= htmlspecialchars($healthQuestionnaire['allergy_details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($patient['sex'] === 'Female'): ?>
                    <div class="detail-group">
                        <span class="detail-label">Pregnant:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['pregnant']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Nursing:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['nursing']) ?></div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Birth Control Pills:</span>
                        <div class="detail-value"><?= htmlspecialchars($healthQuestionnaire['birth_control']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($conditions)): ?>
                    <div class="detail-group full-width">
                        <span class="detail-label">Existing Medical Conditions:</span>
                        <div class="detail-value">
                            <div class="conditions-list">
                                <?php foreach ($conditions as $condition): ?>
                                    <span class="condition-tag"><?= htmlspecialchars($condition) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="health-section">
                    <h2>HEALTH QUESTIONNAIRE</h2>
                    <div class="detail-value" style="text-align: center; padding: 20px;">
                        No health questionnaire submitted yet.
                        <br>
                        <a href="medicalhistory.php?patient_id=<?= $patient_id ?>" class="back-btn" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> Add Health Questionnaire
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <!-- End of Health Questionnaire Section -->
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