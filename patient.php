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
        try {
            $sql = "UPDATE patients SET ";
            $sql .= implode(" = ?, ", $fields) . " = ? ";
            $sql .= "WHERE patient_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $params = array_values($data);
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
        try {
            $sql = "INSERT INTO patients (" . implode(", ", $fields) . ") ";
            $sql .= "VALUES (" . str_repeat("?, ", count($fields) - 1) . "?)";
            
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute(array_values($data))) {
                echo "<script>alert('Patient data submitted successfully!'); window.location.href='patient.php';</script>";
            } else {
                echo "<script>alert('Error submitting data: " . addslashes($stmt->errorInfo()[2]) . "');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error submitting data: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Fetch patient data
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE 
                              last_name LIKE :search OR 
                              first_name LIKE :search OR 
                              home_address LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
    } else {
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
                    <a href="patient.php" style="text-align:center; display:block; margin-top:10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

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
                    <?php foreach ($result as $row): ?>
                        <tr>
                            <td><?= $row['patient_id'] ?></td>
                            <td><?= $row['last_name'] ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?></td>
                            <td><?= $row['home_address'] ?></td>
                            <td><?= $row['mobile_number'] ?></td>
                            <td class="action-icons">
                                <a href="view_patient.php?id=<?= $row['patient_id'] ?>" title="View"><i class="fas fa-eye"></i></a>
                                <a href="patient.php?edit=<?= $row['patient_id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="patient.php?delete=<?= $row['patient_id'] ?>" onclick="return confirm('Are you sure you want to delete this patient?')" title="Delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

<script>
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
</script>
</body>
</html>
