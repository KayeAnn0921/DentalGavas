<?php
// Include database connection
require_once 'config.php';

// Fetch patients from the database
$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, first_name FROM patients");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching patients: " . $e->getMessage());
}

// Handle form submission
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $tooth_chart_data = $_POST['tooth_chart_data'];
    $service = $_POST['service'];
    $description = $_POST['description'];

    try {
        $stmt = $pdo->prepare("INSERT INTO dental_charts (patient_id, tooth_chart_data, service, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$patient_id, $tooth_chart_data, $service, $description]);
        $successMessage = "Tooth chart saved successfully!";
    } catch (PDOException $e) {
        error_log("Insert error: " . $e->getMessage());
        $errorMessage = "Failed to save tooth chart.";
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
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="chart-container">
            <div class="remarks-header">Tooth Chart</div>

            <?php if ($successMessage): ?>
                <div style="color: green; font-weight: bold; margin-bottom: 15px;"><?= $successMessage ?></div>
            <?php elseif ($errorMessage): ?>
                <div style="color: red; font-weight: bold; margin-bottom: 15px;"><?= $errorMessage ?></div>
            <?php endif; ?>

            <div class="remarks">Legend</div>
            <table class="legend">
                <tr><td><strong>AM</strong> - Amalgam</td><td><strong>RC</strong> - Recurrent Caries</td><td><strong>Red</strong> - Caries</td></tr>
                <tr><td><strong>COM</strong> - Composite</td><td><strong>RF</strong> - Root Fragment</td><td><strong>X</strong> - Extraction</td></tr>
                <tr><td><strong>IMP</strong> - Impacted</td><td><strong>M</strong> - Missing</td><td><strong>F</strong> - Filling</td></tr>
                <tr><td><strong>UN</strong> - Unerupted</td><td><strong>RCT</strong> - Root Canal</td><td><strong>OP</strong> - Prophylaxis</td></tr>
                <tr><td><strong>LCF</strong> - Lightcured Filled</td><td><strong>JC</strong> - Jacket Crown</td><td><strong>RPD</strong> - Removable Denture</td></tr>
                <tr><td><strong>FB</strong> - Fixedbridge</td><td><strong>MB</strong> - Maryland Bridge</td><td><strong>F</strong> - Fluoride</td></tr>
            </table>

            <div class="arch-label">Upper Arch</div>
            <div class="tooth-row" id="upper-arch"></div>

            <div class="arch-label">Lower Arch</div>
            <div class="tooth-row" id="lower-arch"></div>

            <form method="post" style="margin-top: 30px;">
                <div class="patient-selection" style="margin-bottom: 20px;">
                    <label for="patient_id"><strong>Select Patient:</strong></label><br>
                    <select name="patient_id" id="patient_id" required style="margin-top: 10px; padding: 10px; width: 100%;">
                        <option value="" disabled selected>Select a patient</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= htmlspecialchars($patient['patient_id']) ?>">
                                <?= htmlspecialchars($patient['first_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="tooth_chart_data" id="tooth_chart_data">

                <div class="service-selection" style="margin-bottom: 20px;">
                    <label for="service"><strong>Select Service:</strong></label><br>
                    <select name="service" id="service" required style="margin-top: 10px; padding: 10px; width: 100%;">
                        <option value="" disabled selected>Select a service</option>
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

                <div class="description-container">
                    <label for="description"><strong>Description / Remarks:</strong></label><br>
                    <textarea id="description" name="description" rows="5" style="margin-top: 10px; padding: 10px; width: 100%;"></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" style="padding: 10px 20px; background-color: #007BFF; color: white;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getToothImage(toothNumber) {
            const toothImages = {
                '18': 'img/num18.png', '17': 'img/num17.png', '16': 'img/num16.png', '15': 'img/num15.png',
                '14': 'img/num14.png', '13': 'img/num13.png', '12': 'img/num12.png', '11': 'img/num11.png',
                '21': 'img/num21.png', '22': 'img/num22.png', '23': 'img/num23.png', '24': 'img/num24.png',
                '25': 'img/num25.png', '26': 'img/num26.png', '27': 'img/num27.png', '28': 'img/num28.png',
                '38': 'img/num38.png', '37': 'img/num37.png', '36': 'img/num36.png', '35': 'img/num35.png',
                '34': 'img/num34.png', '33': 'img/num33.png', '32': 'img/num32.png', '31': 'img/num31.png',
                '41': 'img/num41.png', '42': 'img/num42.png', '43': 'img/num43.png', '44': 'img/num44.png',
                '45': 'img/num45.png', '46': 'img/num46.png', '47': 'img/num47.png', '48': 'img/num48.png',
                'default': 'img/tooth.png'
            };
            return toothImages[toothNumber.toString()] || toothImages['default'];
        }

        function createTooth(containerId, number) {
            const container = document.getElementById(containerId);
            const toothContainer = document.createElement('div');
            toothContainer.className = 'tooth-container';

            const toothImage = getToothImage(number);

            toothContainer.innerHTML = `
                <input type="text" class="tooth-input" maxlength="3">
                <img src="${toothImage}" class="tooth-img" alt="Tooth ${number}">
                <div class="tooth-number">${number}</div>
            `;
            container.appendChild(toothContainer);
        }

        [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28].forEach(num => {
            createTooth('upper-arch', num);
        });

        [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38].forEach(num => {
            createTooth('lower-arch', num);
        });

        // Collect data on submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('.tooth-input');
            const toothData = [];

            inputs.forEach(input => {
                const toothNumber = input.parentElement.querySelector('.tooth-number').textContent;
                const value = input.value.trim();
                if (value) {
                    toothData.push({ tooth: toothNumber, value: value });
                }
            });

            document.getElementById('tooth_chart_data').value = JSON.stringify(toothData);
        });
    </script>
</body>
</html>
