<?php
include 'config.php';

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$result = [];
$edit_mode = false;
$patient_data = [];

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->execute([$id]);
        echo "<script>alert('Patient record deleted successfully!'); window.location.href='patient.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting record: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle edit action - load data
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

// Handle form submission (both add and update)
if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $fields = [
        'visit_type', 'appointment_date', 'appointment_time', 'date', 
        'last_name', 'first_name', 'middle_name', 'birthdate', 'age', 
        'sex', 'civil_status', 'mobile_number', 'email_address', 
        'fb_account', 'home_address', 'work_address', 'occupation', 
        'office_contact_number', 'parent_guardian_name', 'physician_name', 
        'physician_address', 'previous_dentists', 'treatment_done', 
        'referred_by', 'last_dental_visit', 'reason_for_visit'
    ];
    
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? '';
    }

    if ($edit_mode && isset($_POST['patient_id'])) {
        // Update existing record
        try {
            $sql = "UPDATE patients SET ";
            $sql .= implode(" = ?, ", $fields) . " = ? ";
            $sql .= "WHERE patient_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $params = [];
            foreach ($fields as $field) {
                $params[] = $data[$field];
            }
            $params[] = $_POST['patient_id'];
            
            if ($stmt->execute($params)) {
                echo "<script>alert('Patient data updated successfully!'); window.location.href='patient.php';</script>";
            } else {
                echo "<script>alert('Error updating record: " . addslashes($stmt->errorInfo()[2]) . "');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error updating record: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        // Insert new record
        try {
            $sql = "INSERT INTO patients (" . implode(", ", $fields) . ") ";
            $sql .= "VALUES (" . str_repeat("?, ", count($fields) - 1) . "?)";
            
            $stmt = $pdo->prepare($sql);
            $params = [];
            foreach ($fields as $field) {
                $params[] = $data[$field];
            }
            
            if ($stmt->execute($params)) {
                echo "<script>alert('Patient data submitted successfully!'); window.location.href='patient.php';</script>";
            } else {
                echo "<script>alert('Error submitting data: " . addslashes($stmt->errorInfo()[2]) . "');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error submitting data: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Fetch patient data for the table
try {
    if (!empty($search)) {
        // Search query
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE 
                              last_name LIKE :search OR 
                              first_name LIKE :search OR 
                              home_address LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
    } else {
        // Regular listing
        $stmt = $pdo->query("SELECT * FROM patients ORDER BY last_name, first_name");
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAVAS DENTAL CLINIC - Patient Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/patient.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <section class="main">
        <section class="main-course">
            <h1>PATIENT INFORMATION RECORD</h1>
            <div class="course-box">
                <form action="" method="POST">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="patient_id" value="<?= $patient_data['patient_id'] ?>">
                    <?php endif; ?>
                    
                    <label for="visit_type">Visit Type:</label>
                    <input type="text" name="visit_type" id="visit_type" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['visit_type'] : '') ?>">

                    <label for="appointment_date">Appointment Date:</label>
                    <input type="date" name="appointment_date" id="appointment_date" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['appointment_date'] : '') ?>">

                    <label for="appointment_time">Appointment Time:</label>
                    <input type="time" name="appointment_time" id="appointment_time" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['appointment_time'] : '') ?>">

                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['date'] : '') ?>">

                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['last_name'] : '') ?>">

                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['first_name'] : '') ?>">

                    <label for="middle_name">Middle Name:</label>
                    <input type="text" name="middle_name" id="middle_name" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['middle_name'] : '') ?>">

                    <label for="birthdate">Birthdate:</label>
                    <input type="date" name="birthdate" id="birthdate" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['birthdate'] : '') ?>" onchange="calculateAge()">

                    <label for="age">Age:</label>
                    <input type="number" name="age" id="age" required readonly 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['age'] : '') ?>">

                    <label for="sex">Sex:</label>
                    <select name="sex" id="sex" required>
                        <option value="">Select</option>
                        <option value="Male" <?= ($edit_mode && $patient_data['sex'] == 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($edit_mode && $patient_data['sex'] == 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>

                    <label for="civil_status">Civil Status:</label>
                    <select name="civil_status" id="civil_status" required>
                        <option value="">Select</option>
                        <option value="Single" <?= ($edit_mode && $patient_data['civil_status'] == 'Single') ? 'selected' : '' ?>>Single</option>
                        <option value="Married" <?= ($edit_mode && $patient_data['civil_status'] == 'Married') ? 'selected' : '' ?>>Married</option>
                        <option value="Divorced" <?= ($edit_mode && $patient_data['civil_status'] == 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                        <option value="Widowed" <?= ($edit_mode && $patient_data['civil_status'] == 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                    </select>

                    <label for="mobile_number">Mobile Number:</label>
                    <input type="text" name="mobile_number" id="mobile_number" required 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['mobile_number'] : '') ?>">

                    <label for="email_address">Email Address:</label>
                    <input type="email" name="email_address" id="email_address" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['email_address'] : '') ?>">

                    <label for="fb_account">Facebook Account:</label>
                    <input type="text" name="fb_account" id="fb_account" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['fb_account'] : '') ?>">

                    <label for="home_address">Home Address:</label>
                    <textarea name="home_address" id="home_address" required><?= htmlspecialchars($edit_mode ? $patient_data['home_address'] : '') ?></textarea>

                    <label for="work_address">Work Address:</label>
                    <textarea name="work_address" id="work_address"><?= htmlspecialchars($edit_mode ? $patient_data['work_address'] : '') ?></textarea>

                    <label for="occupation">Occupation:</label>
                    <input type="text" name="occupation" id="occupation" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['occupation'] : '') ?>">

                    <label for="office_contact_number">Office Contact Number:</label>
                    <input type="text" name="office_contact_number" id="office_contact_number" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['office_contact_number'] : '') ?>">

                    <div id="minor-fields" style="<?= ($edit_mode && $patient_data['age'] < 18) ? 'display:block' : 'display:none' ?>">
                        <label for="parent_guardian_name">Parent/Guardian Name:</label>
                        <input type="text" name="parent_guardian_name" id="parent_guardian_name" 
                               value="<?= htmlspecialchars($edit_mode ? $patient_data['parent_guardian_name'] : '') ?>">

                        <label for="physician_name">Physician Name:</label>
                        <input type="text" name="physician_name" id="physician_name" 
                               value="<?= htmlspecialchars($edit_mode ? $patient_data['physician_name'] : '') ?>">

                        <label for="physician_address">Physician Address:</label>
                        <textarea name="physician_address" id="physician_address"><?= htmlspecialchars($edit_mode ? $patient_data['physician_address'] : '') ?></textarea>
                    </div>

                    <label for="previous_dentists">Previous Dentists:</label>
                    <input type="text" name="previous_dentists" id="previous_dentists" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['previous_dentists'] : '') ?>">

                    <label for="treatment_done">Treatment Done:</label>
                    <input type="text" name="treatment_done" id="treatment_done" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['treatment_done'] : '') ?>">

                    <label for="referred_by">Referred By:</label>
                    <input type="text" name="referred_by" id="referred_by" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['referred_by'] : '') ?>">

                    <label for="last_dental_visit">Last Dental Visit:</label>
                    <input type="date" name="last_dental_visit" id="last_dental_visit" 
                           value="<?= htmlspecialchars($edit_mode ? $patient_data['last_dental_visit'] : '') ?>">

                    <label for="reason_for_visit">Reason for Visit:</label>
                    <textarea name="reason_for_visit" id="reason_for_visit"><?= htmlspecialchars($edit_mode ? $patient_data['reason_for_visit'] : '') ?></textarea>

                    <button type="submit" name="submit"><?= $edit_mode ? 'Update' : 'Submit' ?></button>
                    <?php if ($edit_mode): ?>
                        <a href="patient.php" style="text-align: center; display: block; margin-top: 10px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>
        
        <!-- Patient List Section -->
        <section class="patient-list-section">
            <div class="list-header">
                <h3>Patient List</h3>
                <form method="GET" action="patient.php" style="display:flex; gap:10px;">
                    <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="patient.php" class="clear-search">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($result)): ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['patient_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(($row['last_name'] ?? '') . ', ' . ($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($row['home_address'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['mobile_number'] ?? '') ?></td>
                                    <td class="action-icons">
                                        <a href="view_patient.php?id=<?= $row['patient_id'] ?? '' ?>" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="patient.php?edit=<?= $row['patient_id'] ?? '' ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="patient.php?delete=<?= $row['patient_id'] ?? '' ?>" onclick="return confirm('Are you sure you want to delete this patient?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No patients found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <script>
        function calculateAge() {
            const birthdate = new Date(document.getElementById('birthdate').value);
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const monthDiff = today.getMonth() - birthdate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }
            
            document.getElementById('age').value = age;
            
            // Show/hide minor fields based on age
            const minorFields = document.getElementById('minor-fields');
            if (age < 18) {
                minorFields.style.display = 'block';
            } else {
                minorFields.style.display = 'none';
            }
        }

        // Initialize minor fields visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateAge();
        });
    </script>
</body>
</html>