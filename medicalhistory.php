<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Verify database connection
if (!$pdo) {
    die("Could not connect to database. Check your config.php file.");
}

// Get patient_id from URL if it exists
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;
$gender = '';

// Get patient's gender if patient_id is provided
if (!empty($patient_id)) {
    try {
        $stmt = $pdo->prepare("SELECT sex FROM patients WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        $gender = $patient['sex'] ?? '';
    } catch (PDOException $e) {
        error_log("Error fetching patient gender: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use the patient_id from POST if submitted, otherwise from GET
    $patient_id = $_POST['patient_id'] ?? $patient_id;  
    
    // Validate patient_id exists
    if (empty($patient_id)) {
        die("Patient ID is required");
    }

    $good_health = $_POST['good_health'] ?? '';
    $medical_condition = $_POST['medical_condition'] ?? '';
    $medical_condition_details = $_POST['medical_condition_details'] ?? '';
    $serious_illness = $_POST['serious_illness'] ?? '';
    $serious_illness_details = $_POST['serious_illness_details'] ?? '';
    $hospitalized = $_POST['hospitalized'] ?? '';
    $hospitalized_details = $_POST['hospitalized_details'] ?? '';
    $medication = $_POST['medication'] ?? '';
    $medication_details = $_POST['medication_details'] ?? '';
    $smoke = $_POST['smoke'] ?? '';
    $alcohol = $_POST['alcohol'] ?? '';
    $drugs = $_POST['drugs'] ?? '';
    $allergy = $_POST['allergy'] ?? '';
    $allergy_details = $_POST['allergy_details'] ?? '';
    $pregnant = $_POST['pregnant'] ?? '';
    $nursing = $_POST['nursing'] ?? '';
    $birth_control = $_POST['birth_control'] ?? '';
    $condition_list = isset($_POST['condition']) ? $_POST['condition'] : [];

    try {
        // First Insert: health_questionnaire
        $sql = "INSERT INTO health_questionnaire 
                (patient_id, good_health, medical_condition, medical_condition_details, serious_illness, 
                serious_illness_details, hospitalized, hospitalized_details, medication, medication_details, 
                smoke, alcohol, drugs, allergy, allergy_details, pregnant, nursing, birth_control)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $patient_id, $good_health, $medical_condition, $medical_condition_details,
            $serious_illness, $serious_illness_details, $hospitalized, $hospitalized_details,
            $medication, $medication_details, $smoke, $alcohol, $drugs,
            $allergy, $allergy_details, $pregnant, $nursing, $birth_control
        ]);

         // Get the last inserted ID from health_questionnaire
         $health_questionnaire_id = $pdo->lastInsertId();
        
         // Second Insert: health_conditions (only if conditions were selected)
         if (!empty($condition_list)) {
             $condition_sql = "INSERT INTO health_conditions (health_questionnaire_id, condition_name) VALUES (?, ?)";
             $condition_stmt = $pdo->prepare($condition_sql);
             
             foreach ($condition_list as $condition) {
                 $condition_stmt->execute([$health_questionnaire_id, $condition]);
             }
         }
 
         // Redirect on success
         header("Location: ".$_SERVER['PHP_SELF']."?success=1&patient_id=".$patient_id);
         exit();
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
        error_log($error_message);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Health Questionnaire</title>
  <link rel="stylesheet" href="css/medicalhistory.css"/>
  <style>
    .error { color: red; }
    .success { color: green; }
    .details-input { display: none; margin-top: 5px; }
    .women-only-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #ff69b4;
    }
    .women-only-section h2 {
        color: #d63384;
        margin-top: 0;
    }
  </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- Main content container -->
    <div class="main-content">
        <div class="form-container">
            <h1>Health Questionnaire</h1>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success">Health questionnaire submitted successfully!</div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">

                <div class="question-group">
                    <div class="question">
                        <label>Are you in good health?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="good_health" value="Yes" required> Yes
                            <input type="radio" name="good_health" value="No"> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Are you under medical condition right now?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="medical_condition" value="Yes" onclick="showInput('medical_condition_details')" required> Yes
                            <input type="radio" name="medical_condition" value="No" onclick="hideInput('medical_condition_details')"> No
                        </div>
                        <input type="text" id="medical_condition_details" name="medical_condition_details" class="details-input" placeholder="If yes, specify...">
                    </div>

                    <div class="question">
                        <label>Have you ever had serious illness or surgical operation?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="serious_illness" value="Yes" onclick="showInput('serious_illness_details')" required> Yes
                            <input type="radio" name="serious_illness" value="No" onclick="hideInput('serious_illness_details')"> No
                        </div>
                        <input type="text" id="serious_illness_details" name="serious_illness_details" class="details-input" placeholder="If yes, specify...">
                    </div>

                    <div class="question">
                        <label>Have you ever been hospitalized?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="hospitalized" value="Yes" onclick="showInput('hospitalized_details')" required> Yes
                            <input type="radio" name="hospitalized" value="No" onclick="hideInput('hospitalized_details')"> No
                        </div>
                        <input type="text" id="hospitalized_details" name="hospitalized_details" class="details-input" placeholder="If yes, specify...">
                    </div>

                    <div class="question">
                        <label>Are you taking any medication?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="medication" value="Yes" onclick="showInput('medication_details')" required> Yes
                            <input type="radio" name="medication" value="No" onclick="hideInput('medication_details')"> No
                        </div>
                        <input type="text" id="medication_details" name="medication_details" class="details-input" placeholder="If yes, specify...">
                    </div>

                    <div class="question">
                        <label>Do you smoke?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="smoke" value="Yes" required> Yes
                            <input type="radio" name="smoke" value="No"> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Do you use alcohol?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="alcohol" value="Yes" required> Yes
                            <input type="radio" name="alcohol" value="No"> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Do you use drugs?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="drugs" value="Yes" required> Yes
                            <input type="radio" name="drugs" value="No"> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Are you allergic to any of the following? (Local Anesthetics, Latex, Penicillin, Aspirin, Others)</label><br>
                        <div class="radio-group">
                            <input type="radio" name="allergy" value="Yes" onclick="showInput('allergy_details')" required> Yes
                            <input type="radio" name="allergy" value="No" onclick="hideInput('allergy_details')"> No
                        </div>
                        <input type="text" id="allergy_details" name="allergy_details" class="details-input" placeholder="If yes, specify...">
                    </div>
                </div>

                <?php if (strtolower($gender) === 'female'): ?>
                <div class="women-only-section">
                    <h2>For Women Only</h2>

                    <div class="question">
                        <label>Are you pregnant?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="pregnant" value="Yes"> Yes
                            <input type="radio" name="pregnant" value="No" checked> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Are you nursing?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="nursing" value="Yes"> Yes
                            <input type="radio" name="nursing" value="No" checked> No
                        </div>
                    </div>

                    <div class="question">
                        <label>Are you taking birth control pills?</label><br>
                        <div class="radio-group">
                            <input type="radio" name="birth_control" value="Yes"> Yes
                            <input type="radio" name="birth_control" value="No" checked> No
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <!-- Hidden inputs for non-female patients to ensure database consistency -->
                    <input type="hidden" name="pregnant" value="">
                    <input type="hidden" name="nursing" value="">
                    <input type="hidden" name="birth_control" value="">
                <?php endif; ?>

                <h2>Existing Medical Conditions</h2>
                <p><em>Check all that apply:</em></p>

                <div class="condition-list">
                    <?php
                    $conditions = [
                        "High Blood Pressure", "Low Blood Pressure", "Epilepsy/Convulsions", "AIDS or HIV Infection",
                        "Sexually Transmitted Disease", "Stomach Ulcers", "Fainting/Seizures", "Rapid Weight Loss",
                        "Joint Replacement", "Heart Surgery", "Heart Attack", "Thyroid Problem", "Heart Disease",
                        "Heart Murmur", "Hepatitis/Liver Disease", "Rheumatic Fever", "Hay Fever/Allergies",
                        "Respiratory Problems", "Hepatitis/Jaundice", "Tuberculosis", "Swollen Ankles",
                        "Kidney Disease", "Diabetes", "Chest Pain"
                    ];
                    foreach ($conditions as $condition) {
                        echo "<div class='condition-item'><input type='checkbox' name='condition[]' value=\"".htmlspecialchars($condition)."\"> ".htmlspecialchars($condition)."</div>";
                    }
                    ?>
                </div>

                <br><br>
                <div style="text-align:center;">
                    <button type="submit">Submit Health Questionnaire</button>
                    <a href="patient.php" style="margin-left: 20px;">Back to Patient List</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showInput(id) {
        document.getElementById(id).style.display = 'inline-block';
    }
    function hideInput(id) {
        const input = document.getElementById(id);
        input.style.display = 'none';
        input.value = '';
    }
    
    // Show relevant detail fields if "Yes" was previously selected
    document.addEventListener('DOMContentLoaded', function() {
        // Debug: Log the gender value
        console.log('Patient gender: <?php echo $gender; ?>');
        
        const yesRadios = document.querySelectorAll('input[type="radio"][value="Yes"]');
        yesRadios.forEach(radio => {
            if (radio.checked) {
                const detailsId = radio.name + '_details';
                const detailsInput = document.getElementById(detailsId);
                if (detailsInput) {
                    showInput(detailsId);
                }
            }
        });
    });
    </script>
</body>
</html>