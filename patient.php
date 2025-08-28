<?php
include 'config.php';


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$edit_mode = false;
$patient_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];
    try {
        $pdo->beginTransaction();


        $stmt = $pdo->prepare("SELECT chart_id FROM dental_charts WHERE patient_id = ?");
        $stmt->execute([$id]);
        $chart_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($chart_ids)) {
            $in = str_repeat('?,', count($chart_ids) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM tooth_condition WHERE chart_id IN ($in)");
            $stmt->execute($chart_ids);
        }
        $stmt = $pdo->prepare("DELETE FROM health_questionnaire WHERE patient_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM prescription WHERE patient_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM patient_services WHERE patient_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM dental_charts WHERE patient_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->execute([$id]);
        $pdo->commit();

        echo "<script>alert('Patient deleted successfully!'); window.location.href='patient.php';</script>";
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting patient: " . addslashes($e->getMessage()) . "');</script>";
    }
}




// Handle appointment data prefill
$prefill_data = [];
if (isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        $prefill_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<script>alert('Error loading appointment data: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle edit action
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->execute([$id]);
        $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($patient_data) {
            $edit_mode = true;
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error loading patient data: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle form submission
if (isset($_POST['submit'])) {
    $fields = [
        'visit_date', 'visit_type', 'last_name', 'first_name', 'middle_name', 
        'birthdate', 'age', 'sex', 'civil_status', 'mobile_number', 'email_address', 
        'fb_account', 'home_address', 'work_address', 'occupation', 
        'office_contact_number', 'parent_guardian_name', 'physician_name', 
        'physician_address', 'previous_dentists', 'treatment_done', 
        'referred_by', 'last_dental_visit', 'reason_for_visit'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? '';
    }

    // Handle selected services
    $selectedServices = isset($_POST['services']) ? $_POST['services'] : [];

    if ($edit_mode && isset($_POST['patient_id'])) {
        try {
            // Update patient data
            $sql = "UPDATE patients SET " . implode(" = ?, ", $fields) . " = ? WHERE patient_id = ?";
            $stmt = $pdo->prepare($sql);
            $params = array_values($data);
            $params[] = $_POST['patient_id'];
            $stmt->execute($params);

            // Clear existing services for the patient
            $stmt = $pdo->prepare("DELETE FROM patient_services WHERE patient_id = ?");
            $stmt->execute([$_POST['patient_id']]);

            // Insert selected services
            $stmt = $pdo->prepare("INSERT INTO patient_services (patient_id, service_id) VALUES (?, ?)");
            foreach ($selectedServices as $service_id) {
                $stmt->execute([$_POST['patient_id'], $service_id]);
            }

            echo "<script>alert('Patient data updated successfully!'); window.location.href='patient.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error updating record: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        try {
            // Insert new patient data
            $sql = "INSERT INTO patients (" . implode(", ", $fields) . ") VALUES (" . str_repeat("?, ", count($fields) - 1) . "?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            $patient_id = $pdo->lastInsertId();

            // Insert selected services
            $stmt = $pdo->prepare("INSERT INTO patient_services (patient_id, service_id) VALUES (?, ?)");
            foreach ($selectedServices as $service_id) {
                $stmt->execute([$patient_id, $service_id]);
            }

            echo "<script>alert('Patient data submitted successfully!'); window.location.href='patient.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error submitting data: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Fetch patient data for list
$itemsPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;
$searchTerm = isset($_GET['searchInput']) ? htmlspecialchars($_GET['searchInput']) : '';
$results = [];

try {
    if (!empty($searchTerm)) {
        $sql = "SELECT * FROM patients WHERE 
                first_name LIKE :searchTerm OR 
                middle_name LIKE :searchTerm OR 
                last_name LIKE :searchTerm
                ORDER BY last_name, first_name
                LIMIT :offset, :itemsPerPage";
        $stmt = $pdo->prepare($sql);
        $likeTerm = "%" . $searchTerm . "%";
        $stmt->bindValue(':searchTerm', $likeTerm, PDO::PARAM_STR);
    } else {
        $sql = "SELECT * FROM patients 
                ORDER BY last_name, first_name
                LIMIT :offset, :itemsPerPage";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pagination logic
    $countSql = "SELECT COUNT(*) FROM patients";
    if (!empty($searchTerm)) {
        $countSql .= " WHERE first_name LIKE :search OR 
                       middle_name LIKE :search OR 
                       last_name LIKE :search";
    }
    $countStmt = $pdo->prepare($countSql);
    if (!empty($searchTerm)) {
        $countStmt->bindValue(':search', $likeTerm, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $itemsPerPage);
} catch (PDOException $e) {
    echo "Error fetching records: " . $e->getMessage();
    $results = [];
}

$categories = $pdo->query("SELECT * FROM category ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all services and organize by category and parent
$servicesByCategory = [];
$services = $pdo->query("SELECT * FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($categories as $cat) {
    $servicesByCategory[$cat['category_id']] = [
        'category' => $cat['name'],
        'services' => []
    ];
}
foreach ($services as $service) {
    if ($service['parent_id']) {
        // Find parent and add as subservice
        foreach ($servicesByCategory as &$catArr) {
            foreach ($catArr['services'] as &$parent) {
                if ($parent['service_id'] == $service['parent_id']) {
                    $parent['subservices'][] = $service;
                }
            }
        }
    } else {
        // Add as main service under category
        $servicesByCategory[$service['category_id']]['services'][] = [
            'service_id' => $service['service_id'],
            'name' => $service['name'],
            'price' => $service['price'],
            'subservices' => []
        ];
    }
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAVAS DENTAL CLINIC - Patient Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #f6f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
.main-content {
    max-width: 1200px;
    margin-top: 40px;
    margin-left: 350px; /* Match your sidebar width */
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    padding: 32px 36px 36px 36px;
}
        h1 {
            color: #2563eb;
            font-size: 2.2rem;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .patient-form-section {
            margin-bottom: 40px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 32px;
        }
        .patient-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 32px;
        }
        .patient-form label {
            font-weight: 500;
            color: #222;
            margin-bottom: 4px;
            display: block;
        }
        .patient-form input,
        .patient-form select,
        .patient-form textarea {
            width: 100%;
            padding: 9px 12px;
            border-radius: 7px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
            background: #f9fafb;
            margin-bottom: 8px;
            transition: border 0.2s;
        }
        .patient-form input:focus,
        .patient-form select:focus,
        .patient-form textarea:focus {
            border: 1.5px solid #2563eb;
            outline: none;
        }
        .patient-form textarea {
            min-height: 38px;
            resize: vertical;
        }
        .patient-form .full-width {
            grid-column: 1 / 3;
        }
        .patient-form .minor-fields {
            grid-column: 1 / 3;
            background: #f1f5f9;
            border-radius: 8px;
            padding: 18px 18px 0 18px;
            margin-bottom: 12px;
        }
        .dropdown-multiselect {
            position: relative;
            width: 100%;
            font-size: 1rem;
        }
        .dropdown-btn {
            width: 100%;
            padding: 10px 14px;
            border-radius: 7px;
            border: 1.5px solid #d1d5db;
            background: #f9fafb;
            font-size: 1rem;
            text-align: left;
            cursor: pointer;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            position: relative;
        }
        .dropdown-btn::after {
            content: '';
            position: absolute;
            right: 18px;
            top: 50%;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 7px solid #888;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .dropdown-multiselect.open .dropdown-btn,
        .dropdown-btn:focus {
            border: 1.5px solid #2563eb;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
            background: #f3f6fd;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: #fff;
            min-width: 100%;
            max-height: 260px;
            overflow-y: auto;
            border: 1.5px solid #2563eb;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.10);
            z-index: 20;
            margin-top: 4px;
            padding: 6px 0;
            animation: dropdownFadeIn 0.18s;
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-8px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .dropdown-multiselect.open .dropdown-content {
            display: block;
        }
        .dropdown-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.18s;
            border: none;
            background: none;
            color: #222;
            user-select: none;
        }
        .dropdown-option input[type="checkbox"] {
            accent-color: #2563eb;
            width: 18px;
            height: 18px;
            margin-right: 6px;
            cursor: pointer;
        }
        .dropdown-option:hover {
            background: #f1f5f9;
            color: #2563eb;
        }
        .dropdown-content::-webkit-scrollbar {
            width: 7px;
            background: #f3f6fd;
            border-radius: 8px;
        }
        .dropdown-content::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 8px;
        }
        .btn-row {
            grid-column: 1 / 3;
            display: flex;
            gap: 14px;
            margin-top: 10px;
        }
        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 7px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #1746a2;
            transform: translateY(-2px);
        }
        .btn.cancel {
            background: #e5e7eb;
            color: #222;
        }
        .btn.cancel:hover {
            background: #cbd5e1;
        }
        .btn.health {
            background: #22c55e;
        }
        .btn.health:hover {
            background: #15803d;
        }
        .patient-table-section {
            margin-top: 30px;
        }
        .table-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .table-header-row h3 {
            margin: 0;
            color: #2563eb;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .table-header-row form {
            display: flex;
            gap: 8px;
        }
        .table-header-row input[type="text"] {
            padding: 7px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
        }
        .table-header-row button,
        .table-header-row .clear-search {
            padding: 7px 16px;
            border-radius: 6px;
            border: none;
            background: #2563eb;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .table-header-row .clear-search {
            background: #e5e7eb;
            color: #222;
            text-decoration: none;
        }
        .table-header-row button:hover {
            background: #1746a2;
        }
        .table-header-row .clear-search:hover {
            background: #cbd5e1;
        }
        .patient-table-container {
            overflow-x: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        table.patient-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            background: #fff;
        }
        table.patient-table th, table.patient-table td {
            padding: 13px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 1rem;
        }
        table.patient-table th {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
        }
        table.patient-table tr:last-child td {
            border-bottom: none;
        }
        .action-icons a {
            margin-right: 8px;
            color: #2563eb;
            font-size: 1.1rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .action-icons a:last-child {
            margin-right: 0;
        }
        .action-icons a:hover {
            color: #e53935;
        }
        .action-icons button {
            background: none;
            border: none;
            padding: 0;
            margin-right: 8px;
            color: #2563eb;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .action-icons button:hover {
            color: #e53935;
        }
        .pagination {
            margin: 18px 0 0 0;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pagination a {
            padding: 7px 14px;
            border-radius: 6px;
            background: #e5e7eb;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
        }
        .pagination a.active, .pagination a:hover {
            background: #2563eb;
            color: #fff;
        }
        @media (max-width: 900px) {
            .main-content {
                 margin-left: 300px; /* Reduced margin for slightly smaller screens */
                padding: 12px 2vw;
            }
            .patient-form {
                grid-template-columns: 1fr;
            }
            .patient-form .full-width,
            .patient-form .minor-fields,
            .btn-row {
                grid-column: 1 / 2;
            }
        }
        @media (max-width: 600px) {
            .main-content {
                padding: 2px;
                   margin-left: 120px; /* Smallest margin for mobile */
            }
            .patient-form input,
            .patient-form select,
            .patient-form textarea {
                font-size: 0.98rem;
            }
            table.patient-table th, table.patient-table td {
                font-size: 0.97rem;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <h1>Patient Information Record</h1>
    <section class="patient-form-section">
        <form class="patient-form" action="" method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="patient_id" value="<?= $patient_data['patient_id'] ?>">
            <?php endif; ?>

            <div>
                <label for="visit_date">Visit Date</label>
                <input type="date" name="visit_date" id="visit_date" required
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['visit_date'] : date('Y-m-d')) ?>">
            </div>
            <div>
                <label for="visit_type">Visit Type</label>
                <select name="visit_type" id="visit_type" required>
                    <option value="">Select</option>
                    <option value="Walk-in" <?= ($edit_mode && $patient_data['visit_type'] == 'Walk-in') ? 'selected' : 
                                              ((!$edit_mode && isset($prefill_data['type_of_visit']) && $prefill_data['type_of_visit'] == 'Walk-in') ? 'selected' : '') ?>>Walk-in</option>
                    <option value="Appointment" <?= ($edit_mode && $patient_data['visit_type'] == 'Appointment') ? 'selected' : 
                                                ((!$edit_mode && isset($prefill_data['type_of_visit']) && $prefill_data['type_of_visit'] == 'Appointment') ? 'selected' : '') ?>>Appointment</option>
                </select>
            </div>
            <div class="full-width">
                <label for="services">Services</label>
                <div class="dropdown-multiselect">
                    <button type="button" class="dropdown-btn" onclick="toggleDropdown()">Select Services</button>
                    <div class="dropdown-content" id="dropdownContent">
                        <?php
                        $selectedServices = [];
                        if ($edit_mode) {
                            $stmt = $pdo->prepare("SELECT service_id FROM patient_services WHERE patient_id = ?");
                            $stmt->execute([$patient_data['patient_id']]);
                            $selectedServices = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        }
                        foreach ($servicesByCategory as $cat) {
                            echo "<div style='font-weight:bold;color:#2563eb;padding:6px 18px 2px 18px;'>{$cat['category']}</div>";
                            foreach ($cat['services'] as $service) {
                                $isChecked = in_array($service['service_id'], $selectedServices) ? 'checked' : '';
                                $priceDisplay = $service['price'] > 0 ? " - ₱" . number_format($service['price'], 2) : '';
                                echo "<label class='dropdown-option' style='padding-left:28px;'><input type='checkbox' name='services[]' value='{$service['service_id']}' $isChecked> {$service['name']}{$priceDisplay}</label>";
                                // Subservices
                                if (!empty($service['subservices'])) {
                                    foreach ($service['subservices'] as $sub) {
                                        $isCheckedSub = in_array($sub['service_id'], $selectedServices) ? 'checked' : '';
                                        $priceDisplaySub = $sub['price'] > 0 ? " - ₱" . number_format($sub['price'], 2) : '';
                                        echo "<label class='dropdown-option' style='padding-left:48px;'><input type='checkbox' name='services[]' value='{$sub['service_id']}' $isCheckedSub> {$sub['name']}{$priceDisplaySub}</label>";
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" required 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['last_name'] : 
                                                ((!$edit_mode && isset($prefill_data['last_name'])) ? $prefill_data['last_name'] : '')) ?>">
            </div>
            <div>
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" required 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['first_name'] : 
                                                ((!$edit_mode && isset($prefill_data['first_name'])) ? $prefill_data['first_name'] : '')) ?>">
            </div>
            <div>
                <label for="middle_name">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['middle_name'] : '') ?>">
            </div>
            <div>
                <label for="birthdate">Birthdate</label>
                <input type="date" name="birthdate" id="birthdate" required 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['birthdate'] : '') ?>" onchange="calculateAge()">
            </div>
            <div>
                <label for="age">Age</label>
                <input type="number" name="age" id="age" required readonly 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['age'] : '') ?>">
            </div>
            <div>
                <label for="sex">Sex</label>
                <select name="sex" id="sex" required>
                    <option value="">Select</option>
                    <option value="Male" <?= ($edit_mode && $patient_data['sex'] == 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($edit_mode && $patient_data['sex'] == 'Female') ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div>
                <label for="civil_status">Civil Status</label>
                <select name="civil_status" id="civil_status" required>
                    <option value="">Select</option>
                    <option value="Single" <?= ($edit_mode && $patient_data['civil_status'] == 'Single') ? 'selected' : '' ?>>Single</option>
                    <option value="Married" <?= ($edit_mode && $patient_data['civil_status'] == 'Married') ? 'selected' : '' ?>>Married</option>
                    <option value="Divorced" <?= ($edit_mode && $patient_data['civil_status'] == 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                    <option value="Widowed" <?= ($edit_mode && $patient_data['civil_status'] == 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                </select>
            </div>
            <div>
                <label for="mobile_number">Mobile Number</label>
                <input type="text" name="mobile_number" id="mobile_number" required 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['mobile_number'] : 
                                                ((!$edit_mode && isset($prefill_data['contact_number'])) ? $prefill_data['contact_number'] : '')) ?>">
            </div>
            <div>
                <label for="email_address">Email Address</label>
                <input type="email" name="email_address" id="email_address" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['email_address'] : 
                                                ((!$edit_mode && isset($prefill_data['email'])) ? $prefill_data['email'] : '')) ?>">
            </div>
            <div>
                <label for="fb_account">Facebook Account</label>
                <input type="text" name="fb_account" id="fb_account" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['fb_account'] : '') ?>">
            </div>
            <div class="full-width">
                <label for="home_address">Home Address</label>
                <textarea name="home_address" id="home_address" required><?= htmlspecialchars($edit_mode ? $patient_data['home_address'] : 
                                                                        ((!$edit_mode && isset($prefill_data['address'])) ? $prefill_data['address'] : '')) ?></textarea>
            </div>
            <div class="full-width">
                <label for="work_address">Work Address</label>
                <textarea name="work_address" id="work_address"><?= htmlspecialchars($edit_mode ? $patient_data['work_address'] : '') ?></textarea>
            </div>
            <div>
                <label for="occupation">Occupation</label>
                <input type="text" name="occupation" id="occupation" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['occupation'] : '') ?>">
            </div>
            <div>
                <label for="office_contact_number">Office Contact Number</label>
                <input type="text" name="office_contact_number" id="office_contact_number" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['office_contact_number'] : '') ?>">
            </div>
            <div class="minor-fields" id="minor-fields" style="<?= ($edit_mode && $patient_data['age'] < 18) ? 'display:block' : 'display:none' ?>">
                <label for="parent_guardian_name">Parent/Guardian Name</label>
                <input type="text" name="parent_guardian_name" id="parent_guardian_name" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['parent_guardian_name'] : '') ?>">

                <label for="physician_name">Physician Name</label>
                <input type="text" name="physician_name" id="physician_name" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['physician_name'] : '') ?>">

                <label for="physician_address">Physician Address</label>
                <textarea name="physician_address" id="physician_address"><?= htmlspecialchars($edit_mode ? $patient_data['physician_address'] : '') ?></textarea>
            </div>
            <div>
                <label for="previous_dentists">Previous Dentists</label>
                <input type="text" name="previous_dentists" id="previous_dentists" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['previous_dentists'] : '') ?>">
            </div>
            <div>
                <label for="treatment_done">Treatment Done</label>
                <input type="text" name="treatment_done" id="treatment_done" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['treatment_done'] : '') ?>">
            </div>
            <div>
                <label for="referred_by">Referred By</label>
                <input type="text" name="referred_by" id="referred_by" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['referred_by'] : '') ?>">
            </div>
            <div>
                <label for="last_dental_visit">Last Dental Visit</label>
                <input type="date" name="last_dental_visit" id="last_dental_visit" 
                       value="<?= htmlspecialchars($edit_mode ? $patient_data['last_dental_visit'] : '') ?>">
            </div>
            <div class="full-width">
                <label for="reason_for_visit">Reason for Visit</label>
                <textarea name="reason_for_visit" id="reason_for_visit"><?= htmlspecialchars($edit_mode ? $patient_data['reason_for_visit'] : 
                                                                      ((!$edit_mode && isset($prefill_data['reason'])) ? $prefill_data['reason'] : '')) ?></textarea>
            </div>
            <div class="btn-row">
                <button type="submit" name="submit" class="btn"><?= $edit_mode ? 'Update Patient' : 'Save Patient' ?></button>
                <?php if ($edit_mode): ?>
                    <a href="patient.php" class="btn cancel">Cancel</a>
                    <a href="medicalhistory.php?patient_id=<?= $patient_data['patient_id'] ?>" class="btn health">Health Questionnaire</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="patient-table-section">
        <div class="table-header-row">
            <h3>Patient List</h3>
            <form method="GET" action="patient.php">
                <input type="text" class="search-box" name="searchInput" placeholder="Search..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit">Search</button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="patient.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="patient-table-container">
            <table class="patient-table">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Patient Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $row): 
                    // Fetch associated services
                    $stmt = $pdo->prepare("SELECT s.name FROM patient_services ps JOIN services s ON ps.service_id = s.service_id WHERE ps.patient_id = ?");
                    $stmt->execute([$row['patient_id']]);
                    $services = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $serviceNames = implode(', ', $services);
                ?>
                <tr>
                    <td><?= $row['patient_id'] ?></td>
                    <td><?= $row['last_name'] ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?></td>
                    <td><?= $row['home_address'] ?></td>
                    <td><?= $row['mobile_number'] ?></td>
                    <td><?= htmlspecialchars($serviceNames) ?></td>
                    <td class="action-icons">
                        <a href="view_patient.php?id=<?= $row['patient_id'] ?>" title="View"><i class="fas fa-eye"></i></a>
                        <a href="patient.php?edit=<?= $row['patient_id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
                     <form method="POST" action="patient.php" style="display:inline;">
                        <input type="hidden" name="delete" value="<?= $row['patient_id'] ?>">
                        <button type="submit" onclick="return confirmDelete();" class="delete-btn" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                                    <a href="medicalhistory.php?patient_id=<?= $row['patient_id'] ?>" title="Health Questionnaire"><i class="fas fa-file-medical"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?><?= !empty($searchTerm) ? '&searchInput='.urlencode($searchTerm) : '' ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($searchTerm) ? '&searchInput='.urlencode($searchTerm) : '' ?>" 
                       class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?><?= !empty($searchTerm) ? '&searchInput='.urlencode($searchTerm) : '' ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this patient and all related records? This action cannot be undone.');
}
function calculateAge() {
    const birthdate = document.getElementById("birthdate").value;
    if (birthdate) {
        const today = new Date();
        const birth = new Date(birthdate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        document.getElementById("age").value = age;
        document.getElementById("minor-fields").style.display = age < 18 ? "block" : "none";
    }
}
function toggleDropdown() {
    var dropdown = document.querySelector('.dropdown-multiselect');
    dropdown.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var dropdown = document.querySelector('.dropdown-multiselect');
    if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('input[name="patient_id"]')) {
        document.getElementById('visit_date').value = new Date().toISOString().split('T')[0];
    }
    if (document.getElementById('birthdate').value) {
        calculateAge();
    }
});
</script>
</body>
</html>