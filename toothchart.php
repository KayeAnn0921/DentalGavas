<?php
require_once 'config.php';
include 'sidebar.php'; // Include sidebar for consistent layout

// Fetch patients
$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name, first_name");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching patients: " . $e->getMessage());
}

// Handle form submission
$successMessage = '';
$errorMessage = '';
$patient_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null;
    $service = isset($_POST['service']) ? trim($_POST['service']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Collect teeth data
    $teethData = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tooth_') === 0 && !empty(trim($value))) {
            $toothNumber = substr($key, 6);
            $teethData[$toothNumber] = trim($value);
        }
    }

    // Validate input
    if (!$patient_id) {
        $errorMessage = "Please select a patient.";
    } elseif (empty($teethData)) {
        $errorMessage = "Please enter at least one tooth condition.";
    } elseif (!$service) {
        $errorMessage = "Please select a service.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into dental_charts with created_at timestamp
            $stmt = $pdo->prepare("INSERT INTO dental_charts (patient_id, service, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$patient_id, $service, $description]);
            $chart_id = $pdo->lastInsertId();

            // Insert into tooth_condition table
            $stmt = $pdo->prepare("INSERT INTO tooth_condition (chart_id, tooth_number, condition_code) VALUES (?, ?, ?)");
            foreach ($teethData as $toothNumber => $condition) {
                $stmt->execute([$chart_id, $toothNumber, $condition]);
            }

            $pdo->commit();
            $successMessage = "Tooth chart saved successfully! <a href='diagnosis.php?patient_id=$patient_id' style='color:#2b542c;text-decoration:underline;'>View Diagnosis</a>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errorMessage = "Error saving chart: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// If patient_id is passed via GET (from diagnosis.php)
if (isset($_GET['patient_id']) && !$patient_id) {
    $patient_id = intval($_GET['patient_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Charting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/toothchart.css"/>
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="chart-container">
            <h1>Dental Charting System</h1>

            <!-- Display success or error messages -->
            <?php if ($successMessage): ?>
                <div class="alert success"><?= $successMessage ?></div>
            <?php elseif ($errorMessage): ?>
                <div class="alert error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <!-- Legend Section -->
            <div class="legend">
                <h3>Condition Codes Legend</h3>
                <table class="legend">
                    <tr><td><strong>AM</strong> - Amalgam</td><td><strong>RC</strong> - Recurrent Caries</td><td><strong>Red</strong> - Caries</td></tr>
                    <tr><td><strong>COM</strong> - Composite</td><td><strong>RF</strong> - Root Fragment</td><td><strong>X</strong> - Extraction</td></tr>
                    <tr><td><strong>IMP</strong> - Impacted</td><td><strong>M</strong> - Missing</td><td><strong>F</strong> - Filling</td></tr>
                    <tr><td><strong>UN</strong> - Unerupted</td><td><strong>RCT</strong> - Root Canal</td><td><strong>OP</strong> - Prophylaxis</td></tr>
                    <tr><td><strong>LCF</strong> - Lightcured Filled</td><td><strong>JC</strong> - Jacket Crown</td><td><strong>RPD</strong> - Removable Denture</td></tr>
                    <tr><td><strong>FB</strong> - Fixedbridge</td><td><strong>MB</strong> - Maryland Bridge</td><td><strong>F</strong> - Fluoride</td></tr>
                </table>
            </div>

            <!-- Form Section -->
            <form method="post">
                <div class="form-group">
                    <label for="patient_id">Select Patient:</label>
                    <select name="patient_id" id="patient_id" required>
                        <option value="">Select a patient</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= htmlspecialchars($patient['patient_id']) ?>" <?= ($patient_id == $patient['patient_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service">Select Service:</label>
                    <select name="service" id="service" required>
                        <option value="">Select a service</option>
                        <option value="Oral Prophylaxis">Oral Prophylaxis</option>
                        <option value="Tooth Extraction">Tooth Extraction</option>
                        <option value="Tooth Filling">Tooth Filling</option>
                        <option value="Root Canal Treatment">Root Canal Treatment</option>
                        <option value="Tooth Cleaning">Tooth Cleaning</option>
                        <option value="Crown Placement">Crown Placement</option>
                        <option value="Braces Installation">Braces Installation</option>
                        <option value="Dentures">Dentures</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" rows="3"></textarea>
                </div>

                <!-- Tooth Chart Section -->
                <div class="tooth-chart">
                    <h3>Upper Arch</h3>
                    <div class="tooth-row" id="upper-arch"></div>
                    
                    <h3>Lower Arch</h3>
                    <div class="tooth-row" id="lower-arch"></div>
                </div>

                <button type="submit">Save Chart</button>
            </form>
        </div>
    </div>

    <script>
    // Tooth images mapping
    const toothImages = {
        '18': 'img/num18.png', '17': 'img/num17.png', '16': 'img/num16.png', 
        '15': 'img/num15.png', '14': 'img/num14.png', '13': 'img/num13.png', 
        '12': 'img/num12.png', '11': 'img/num11.png', '21': 'img/num21.png', 
        '22': 'img/num22.png', '23': 'img/num23.png', '24': 'img/num24.png', 
        '25': 'img/num25.png', '26': 'img/num26.png', '27': 'img/num27.png', 
        '28': 'img/num28.png', '38': 'img/num38.png', '37': 'img/num37.png', 
        '36': 'img/num36.png', '35': 'img/num35.png', '34': 'img/num34.png', 
        '33': 'img/num33.png', '32': 'img/num32.png', '31': 'img/num31.png', 
        '41': 'img/num41.png', '42': 'img/num42.png', '43': 'img/num43.png', 
        '44': 'img/num44.png', '45': 'img/num45.png', '46': 'img/num46.png', 
        '47': 'img/num47.png', '48': 'img/num48.png',
        'default': 'img/tooth.png'
    };

   function createTooth(containerId, number) {
    const container = document.getElementById(containerId);
    const toothDiv = document.createElement('div');
    toothDiv.className = 'tooth-container';
    
    toothDiv.innerHTML = `
        <input type="text" name="tooth_${number}"
               class="tooth-input" maxlength="5"> <!-- Changed from tooth-condition to tooth-input -->
        <img src="${toothImages[number] || toothImages['default']}" 
             alt="Tooth ${number}" class="tooth-img">
        <div class="tooth-number">${number}</div>
    `;
    
    container.appendChild(toothDiv);
}

    // Create all teeth when page loads
    document.addEventListener('DOMContentLoaded', () => {
        const upperTeeth = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
        const lowerTeeth = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
        
        upperTeeth.forEach(num => createTooth('upper-arch', num));
        lowerTeeth.forEach(num => createTooth('lower-arch', num));
    });
    </script>
</body>
</html>