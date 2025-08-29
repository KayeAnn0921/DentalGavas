<?php
require_once 'config.php';
include 'sidebar.php';

$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name, first_name");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching patients: " . $e->getMessage());
}

$successMessage = '';
$errorMessage = '';
$patient_id = null;

if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    $patient_id = intval($_POST['patient_id']);
}

// Fetch availed services for the selected patient
$availedServices = [];
if ($patient_id) {
    $stmt = $pdo->prepare("SELECT s.name FROM patient_services ps JOIN services s ON ps.service_id = s.service_id WHERE ps.patient_id = ?");
    $stmt->execute([$patient_id]);
    $availedServices = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$allServices = $pdo->query("SELECT name FROM services ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

// Allowed condition codes (legend)
$allowedCodes = [
    "AM","RC","Red","COM","RF","X",
    "IMP","M","F","UN","RCT","OP",
    "LCF","JC","RPD","FB","MB","Fluoride"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null;
    $services = isset($_POST['services']) ? $_POST['services'] : [];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Collect teeth data with validation
    $teethData = [];
    $validationError = false;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tooth_') === 0 && !empty(trim($value))) {
            $toothNumber = substr($key, 6);
            $code = trim($value);

            if (in_array($code, $allowedCodes, true)) {
                $teethData[$toothNumber] = $code;
            } else {
                $errorMessage = "Invalid code '$code' entered for tooth $toothNumber. Please use only valid condition codes.";
                $validationError = true;
                break;
            }
        }
    }

    // Validate input
    if (!$patient_id) {
        $errorMessage = "Please select a patient.";
    } elseif (empty($teethData) && !$validationError) {
        $errorMessage = "Please enter at least one tooth condition.";
    } elseif (empty($services)) {
        $errorMessage = "Please select at least one service.";
    } elseif (!$validationError && !$errorMessage) {
        try {
            $pdo->beginTransaction();

            foreach ($services as $service) {
                $stmt = $pdo->prepare("INSERT INTO dental_charts (patient_id, service, description, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$patient_id, $service, $description]);
                $chart_id = $pdo->lastInsertId();

                $stmt2 = $pdo->prepare("INSERT INTO tooth_condition (chart_id, tooth_number, condition_code) VALUES (?, ?, ?)");
                foreach ($teethData as $toothNumber => $condition) {
                    $stmt2->execute([$chart_id, $toothNumber, $condition]);
                }
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
        body {background:#f6f8fa;font-family:'Segoe UI',Arial,sans-serif;margin:0;padding:0;}
        .main-content {max-width:1100px;margin:40px auto 0 auto;background:#fff;border-radius:18px;box-shadow:0 4px 24px rgba(0,0,0,0.07);padding:36px 40px 40px 40px;}
        h1 {color:#2563eb;font-size:2.1rem;margin-bottom:18px;letter-spacing:1px;}
        .section-title {color:#1976d2;font-size:1.2rem;margin-top:32px;margin-bottom:10px;font-weight:600;letter-spacing:.5px;}
        .alert {padding:15px;margin-bottom:20px;border-radius:6px;font-size:1rem;}
        .alert.success {background-color:#e6f7e6;color:#256029;border:1px solid #b7e1cd;}
        .alert.error {background-color:#fdeaea;color:#a94442;border:1px solid #f5c6cb;}
        .form-group {margin-bottom:18px;}
        .form-group label {display:block;margin-bottom:6px;font-weight:500;color:#222;}
        .form-group select,.form-group textarea {width:100%;padding:10px 12px;border:1.5px solid #d1d5db;border-radius:7px;font-size:1rem;background:#f9fafb;transition:border .2s;}
        .form-group select:focus,.form-group textarea:focus {border:1.5px solid #2563eb;outline:none;}
        button[type="submit"] {background-color:#2563eb;color:white;padding:11px 32px;border:none;border-radius:7px;cursor:pointer;font-size:1.1rem;font-weight:600;margin-top:18px;transition:background .2s,transform .2s;}
        button[type="submit"]:hover {background-color:#1746a2;transform:translateY(-2px);}
        .legend {margin:0 0 18px 0;background:#f3f6fd;border-radius:8px;padding:18px 18px 10px 18px;font-size:.98rem;}
        .legend table {width:100%;border-collapse:collapse;}
        .legend td {padding:4px 12px 4px 0;color:#333;}
        .tooth-chart {margin-top:18px;margin-bottom:10px;}
        .tooth-row {display:flex;flex-wrap:wrap;gap:12px;margin-bottom:18px;justify-content:center;}
        .tooth-container {display:flex;flex-direction:column;align-items:center;background:#f9fafb;border-radius:8px;box-shadow:0 1px 4px rgba(37,99,235,0.07);padding:10px 8px 8px 8px;min-width:70px;margin-bottom:2px;}
        .tooth-input {width:65px;text-align:center;padding:6px 4px;border-radius:5px;border:1.2px solid #d1d5db;margin-bottom:6px;font-size:.95rem;background:#fff;}
        .tooth-img {width:32px;height:32px;margin-bottom:4px;}
        .tooth-number {font-size:.95rem;color:#1976d2;font-weight:600;}
        #services-list {list-style-type:none;padding:0;display:flex;flex-wrap:wrap;gap:15px;}
        #services-list li {background:#f3f6fd;padding:8px 15px;border-radius:6px;border:1px solid #d1d5db;}
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Dental Charting System</h1>

        <?php if ($successMessage): ?>
            <div class="alert success"><?= $successMessage ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="alert error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="legend">
            <span class="section-title">Condition Codes Legend</span>
            <table>
                <tr><td><strong>AM</strong> - Amalgam</td><td><strong>RC</strong> - Recurrent Caries</td><td><strong>Red</strong> - Caries</td></tr>
                <tr><td><strong>COM</strong> - Composite</td><td><strong>RF</strong> - Root Fragment</td><td><strong>X</strong> - Extraction</td></tr>
                <tr><td><strong>IMP</strong> - Impacted</td><td><strong>M</strong> - Missing</td><td><strong>F</strong> - Filling</td></tr>
                <tr><td><strong>UN</strong> - Unerupted</td><td><strong>RCT</strong> - Root Canal</td><td><strong>OP</strong> - Prophylaxis</td></tr>
                <tr><td><strong>LCF</strong> - Lightcured Filled</td><td><strong>JC</strong> - Jacket Crown</td><td><strong>RPD</strong> - Removable Denture</td></tr>
                <tr><td><strong>FB</strong> - Fixedbridge</td><td><strong>MB</strong> - Maryland Bridge</td><td><strong>Fluoride</strong> - Fluoride</td></tr>
            </table>
        </div>

        <form method="post" autocomplete="off" id="toothChartForm">
            <span class="section-title">Patient & Service Information</span>
            <div class="form-group">
                <label for="patient_id">Select Patient:</label>
                <select name="patient_id" id="patient_id" required onchange="fetchAvailedServices(this.value)">
                    <option value="">Select a patient</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= htmlspecialchars($patient['patient_id']) ?>" <?= ($patient_id == $patient['patient_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa fa-tooth"></i> Services Availed:</label>
                <ul id="services-list">
                    <?php
                    $servicesToShow = ($patient_id && !empty($availedServices)) ? $availedServices : $allServices;
                    foreach ($servicesToShow as $service):
                        $checked = (isset($_POST['services']) && in_array($service, $_POST['services'])) ? 'checked' : '';
                    ?>
                        <li>
                            <label>
                                <input type="checkbox" name="services[]" value="<?= htmlspecialchars($service) ?>" <?= $checked ?>>
                                <?= htmlspecialchars($service) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($patient_id && empty($availedServices)): ?>
                        <li style="color:#c62828;font-size:0.97em;">
                            This patient has not availed any services yet.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="3" placeholder="Add any notes or description..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <span class="section-title">Tooth Chart</span>
            <div class="tooth-chart">
                <h3 style="margin-bottom:8px;color:#1976d2;">Upper Arch</h3>
                <div class="tooth-row" id="upper-arch"></div>
                <h3 style="margin-bottom:8px;color:#1976d2;">Lower Arch</h3>
                <div class="tooth-row" id="lower-arch"></div>
            </div>

            <button type="submit"><i class="fa fa-save"></i> Save Chart</button>
        </form>
    </div>

    <script>
    function fetchAvailedServices(patientId) {
        if (patientId) {
            window.location.href = 'toothchart.php?patient_id=' + patientId;
        } else {
            window.location.href = 'toothchart.php';
        }
    }

    const toothImages = {
        '18':'img/num18.png','17':'img/num17.png','16':'img/num16.png','15':'img/num15.png','14':'img/num14.png','13':'img/num13.png','12':'img/num12.png','11':'img/num11.png',
        '21':'img/num21.png','22':'img/num22.png','23':'img/num23.png','24':'img/num24.png','25':'img/num25.png','26':'img/num26.png','27':'img/num27.png','28':'img/num28.png',
        '38':'img/num38.png','37':'img/num37.png','36':'img/num36.png','35':'img/num35.png','34':'img/num34.png','33':'img/num33.png','32':'img/num32.png','31':'img/num31.png',
        '41':'img/num41.png','42':'img/num42.png','43':'img/num43.png','44':'img/num44.png','45':'img/num45.png','46':'img/num46.png','47':'img/num47.png','48':'img/num48.png',
        'default':'img/tooth.png'
    };

    const allowedCodes = ["","AM","RC","Red","COM","RF","X","IMP","M","F","UN","RCT","OP","LCF","JC","RPD","FB","MB","Fluoride"];

    function createTooth(containerId, number) {
        const container = document.getElementById(containerId);
        const toothDiv = document.createElement('div');
        let options = allowedCodes.map(c => `<option value="${c}">${c}</option>`).join('');
        toothDiv.className = 'tooth-container';
        toothDiv.innerHTML = `
            <select name="tooth_${number}" class="tooth-input" onchange="validateToothCode(this)">
                ${options}
            </select>
            <img src="${toothImages[number] || toothImages['default']}" alt="Tooth ${number}" class="tooth-img">
            <div class="tooth-number">${number}</div>
        `;
        container.appendChild(toothDiv);
    }

    function validateToothCode(selectElement) {
        const value = selectElement.value.trim();
        if (value && !allowedCodes.includes(value)) {
            alert(`"${value}" is not a valid condition code. Please select from the provided options.`);
            selectElement.value = "";
            selectElement.focus();
            return false;
        }
        return true;
    }

    document.getElementById('toothChartForm').addEventListener('submit', function(e) {
        let isValid = true;
        const toothSelects = document.querySelectorAll('select.tooth-input');
        let hasCondition = false;
        toothSelects.forEach(select => {
            if (select.value.trim() !== "") {
                hasCondition = true;
            }
            if (!validateToothCode(select)) {
                isValid = false;
            }
        });
        if (!hasCondition) {
            alert('Please enter at least one tooth condition.');
            isValid = false;
        }
        const serviceCheckboxes = document.querySelectorAll('input[name="services[]"]');
        let serviceSelected = false;
        serviceCheckboxes.forEach(checkbox => {if (checkbox.checked){serviceSelected=true;}});
        if (!serviceSelected) {
            alert('Please select at least one service.');
            isValid = false;
        }
        if (!isValid) {e.preventDefault();}
    });

    document.addEventListener('DOMContentLoaded', () => {
        const upperTeeth = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
        const lowerTeeth = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
        upperTeeth.forEach(num => createTooth('upper-arch', num));
        lowerTeeth.forEach(num => createTooth('lower-arch', num));
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'tooth_') === 0 && !empty(trim($value))) {
                    $toothNumber = substr($key, 6);
                    $code = trim($value);
                    echo "document.querySelector('select[name=\"tooth_{$toothNumber}\"]').value = '" . addslashes($code) . "';";
                }
            }
        }
        ?>
    });
    </script>
</body>
</html>
