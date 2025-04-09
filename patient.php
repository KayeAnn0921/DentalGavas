<?php
include 'config.php';

if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $visit_type = $_POST['visit_type'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $date = $_POST['date'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $birthdate = $_POST['birthdate'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $civil_status = $_POST['civil_status'];
    $mobile_number = $_POST['mobile_number'];
    $email_address = $_POST['email_address'];
    $fb_account = $_POST['fb_account'];
    $home_address = $_POST['home_address'];
    $work_address = $_POST['work_address'];
    $occupation = $_POST['occupation'];
    $office_contact_number = $_POST['office_contact_number'];
    $parent_guardian_name = $_POST['parent_guardian_name'];
    $physician_name = $_POST['physician_name'];
    $physician_address = $_POST['physician_address'];
    $previous_dentists = $_POST['previous_dentists'];
    $treatment_done = $_POST['treatment_done'];
    $referred_by = $_POST['referred_by'];
    $last_dental_visit = $_POST['last_dental_visit'];
    $reason_for_visit = $_POST['reason_for_visit'];

    // Prepare the SQL query
    $sql = "INSERT INTO patients (visit_type, appointment_date, appointment_time, date, last_name, first_name, middle_name, birthdate, age, sex, civil_status, mobile_number, email_address, fb_account, home_address, work_address, occupation, office_contact_number, parent_guardian_name, physician_name, physician_address, previous_dentists, treatment_done, referred_by, last_dental_visit, reason_for_visit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $pdo->prepare($sql)) {
        // Bind parameters to the statement by their position
        $stmt->bindValue(1, $visit_type);
        $stmt->bindValue(2, $appointment_date);
        $stmt->bindValue(3, $appointment_time);
        $stmt->bindValue(4, $date);
        $stmt->bindValue(5, $last_name);
        $stmt->bindValue(6, $first_name);
        $stmt->bindValue(7, $middle_name);
        $stmt->bindValue(8, $birthdate);
        $stmt->bindValue(9, $age);
        $stmt->bindValue(10, $sex);
        $stmt->bindValue(11, $civil_status);
        $stmt->bindValue(12, $mobile_number);
        $stmt->bindValue(13, $email_address);
        $stmt->bindValue(14, $fb_account);
        $stmt->bindValue(15, $home_address);
        $stmt->bindValue(16, $work_address);
        $stmt->bindValue(17, $occupation);
        $stmt->bindValue(18, $office_contact_number);
        $stmt->bindValue(19, $parent_guardian_name);
        $stmt->bindValue(20, $physician_name);
        $stmt->bindValue(21, $physician_address);
        $stmt->bindValue(22, $previous_dentists);
        $stmt->bindValue(23, $treatment_done);
        $stmt->bindValue(24, $referred_by);
        $stmt->bindValue(25, $last_dental_visit);
        $stmt->bindValue(26, $reason_for_visit);

        // Execute the query
        if ($stmt->execute()) {
            echo "Patient data has been successfully submitted!";
        } else {
            echo "Error: " . $stmt->errorInfo()[2]; // Use errorInfo() instead of error property
        }

        // No need to close the statement - PDO handles this automatically
    } else {
        echo "Error preparing statement: " . $pdo->errorInfo()[2]; // Use errorInfo() instead of error property
    }

    // If you want to explicitly close the connection (optional)
    // $pdo = null;
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
    <?php
    include 'sidebar.php';
    ?>

    <section class="main">
        <section class="main-course">
            <h1>PATIENT INFORMATION RECORD</h1>
            <div class="course-box">
            <form action="" method="POST">
    <label for="visit_type">Visit Type:</label>
    <input type="text" name="visit_type" id="visit_type" required>

    <label for="appointment_date">Appointment Date:</label>
    <input type="date" name="appointment_date" id="appointment_date" required>

    <label for="appointment_time">Appointment Time:</label>
    <input type="time" name="appointment_time" id="appointment_time" required>

    <label for="date">Date:</label>
    <input type="date" name="date" id="date" required>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" id="last_name" required>

    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" id="first_name" required>

    <label for="middle_name">Middle Name:</label>
    <input type="text" name="middle_name" id="middle_name" required>

    <label for="birthdate">Birthdate:</label>
    <input type="date" name="birthdate" id="birthdate" required>

    <label for="age">Age:</label>
    <input type="number" name="age" id="age" required>

    <label for="sex">Sex:</label>
    <input type="text" name="sex" id="sex" required>

    <label for="civil_status">Civil Status:</label>
    <input type="text" name="civil_status" id="civil_status" required>

    <label for="mobile_number">Mobile Number:</label>
    <input type="text" name="mobile_number" id="mobile_number" required>

    <label for="email_address">Email Address:</label>
    <input type="email" name="email_address" id="email_address">

    <label for="fb_account">Facebook Account:</label>
    <input type="text" name="fb_account" id="fb_account">

    <label for="home_address">Home Address:</label>
    <input type="text" name="home_address" id="home_address" required>

    <label for="work_address">Work Address:</label>
    <input type="text" name="work_address" id="work_address">

    <label for="occupation">Occupation:</label>
    <input type="text" name="occupation" id="occupation">

    <label for="office_contact_number">Office Contact Number:</label>
    <input type="text" name="office_contact_number" id="office_contact_number">

    <label for="parent_guardian_name">Parent/Guardian Name:</label>
    <input type="text" name="parent_guardian_name" id="parent_guardian_name">

    <label for="physician_name">Physician Name:</label>
    <input type="text" name="physician_name" id="physician_name">

    <label for="physician_address">Physician Address:</label>
    <input type="text" name="physician_address" id="physician_address">

    <label for="previous_dentists">Previous Dentists:</label>
    <input type="text" name="previous_dentists" id="previous_dentists">

    <label for="treatment_done">Treatment Done:</label>
    <input type="text" name="treatment_done" id="treatment_done">

    <label for="referred_by">Referred By:</label>
    <input type="text" name="referred_by" id="referred_by">

    <label for="last_dental_visit">Last Dental Visit:</label>
    <input type="date" name="last_dental_visit" id="last_dental_visit">

    <label for="reason_for_visit">Reason for Visit:</label>
    <textarea name="reason_for_visit" id="reason_for_visit"></textarea>

    <button type="submit" name="submit">Submit</button>
</form>

            </div>
        </section>
    </section>

    <div class="list-header">
    <h3>User List</h3>
    <form method="GET" action="patient.php" style="display:flex; gap:10px;">
        <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>

        </div>
        <table>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($result as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['user_id']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['password']) ?></td>
        <td><?= htmlspecialchars($row['type']) ?></td>
        <td>
            <!-- Edit button -->
            <a href="patient.php?edit=<?= $row['user_id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
            <!-- Delete button -->
            <a href="patient.php?delete=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>
<script>
        function calculateAge(birthdate) {
            const today = new Date();
            const birthDate = new Date(birthdate);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDifference = today.getMonth() - birthDate.getMonth();
            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        function updateForm() {
            const birthdate = document.getElementById('birthdate').value;
            const age = calculateAge(birthdate);
            document.getElementById('age').value = age;

            const additionalInfoFields = [
                'parentGuardianRow', 
                'physicianRow', 
                'physicianAddressRow', 
                'previousDentistsRow', 
                'treatmentDoneRow', 
                'referredByRow', 
                'lastDentalVisitRow', 
                'reasonForVisitRow'
            ];

            if (age < 18) {
                additionalInfoFields.forEach(field => document.getElementById(field).style.display = 'table-row');
            } else {
                additionalInfoFields.forEach(field => document.getElementById(field).style.display = 'none');
            }
        }

        function validateForm() {
            const age = parseInt(document.getElementById('age').value, 10);
            const form = document.getElementById('patientForm');

            if (age < 18) {
                const parentGuardianName = document.getElementById('parent-guardian-name').value;
                const reasonForVisit = document.getElementById('reason-for-visit').value;

                if (!parentGuardianName || !reasonForVisit) {
                    alert('Please fill out all required fields for minors.');
                    return;
                }
            }

            form.submit();
        }
        document.getElementById('birthdate').addEventListener('change', updateForm);

        function toggleVisitType() {
            const visitType = document.getElementById("visit-type").value;
            const appointmentFields = document.getElementById("appointmentFields");
            const healthFields = document.getElementById("healthFields");
            const otherFields = document.querySelectorAll("[id^='otherfields3'], [id^='lastNameField'], [id^='firstNameField'], [id^='middleNameField'], [id^='birthdateField'], [id^='ageField'], [id^='sexField'], [id^='civilStatusField'], [id^='mobileNumberField'], [id^='emailAddressField'], [id^='fbAccountField'], [id^='homeAddressField'], [id^='workAddressField'], [id^='occupationField'], [id^='officeContactNumberField'], [id^='parentGuardianRow'], [id^='physicianRow'], [id^='physicianAddressRow'], [id^='previousDentistsRow'], [id^='treatmentDoneRow'], [id^='referredByRow'], [id^='lastDentalVisitRow'], [id^='reasonForVisitRow']");
            const appointmentTimeField = document.getElementById("appointmentTimeField");
            const contactNumberField =  document.getElementById("contactNumberField");
            const namefield = document.getElementById("namefield");

            if (visitType === "appointment") {
                appointmentFields.style.display = "table-row";
                namefield.style.display = "table-row";
                appointmentTimeField.style.display = "table-row";
                contactNumberField.style.display = "table-row";
                healthFields.style.display = "none";
                otherFields.forEach(field => field.style.display = "none");
            } else {
                namefield.style.display = "none";
                contactNumberField.style.display = "none";
                appointmentFields.style.display = "none";
                appointmentTimeField.style.display = "none";
                healthFields.style.display = "table-row";
                otherFields.forEach(field => field.style.display = "table-row");
            }
        }
        
        // Hide the appointment fields and other fields by default
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("appointmentFields").style.display = "none";
            document.getElementById("healthFields").style.display = "none";
            document.querySelectorAll("[id^='otherfields3'], [id^='lastNameField'], [id^='firstNameField'], [id^='middleNameField'], [id^='birthdateField'], [id^='ageField'], [id^='sexField'], [id^='civilStatusField'], [id^='mobileNumberField'], [id^='emailAddressField'], [id^='fbAccountField'], [id^='homeAddressField'], [id^='workAddressField'], [id^='occupationField'], [id^='officeContactNumberField'], [id^='parentGuardianRow'], [id^='physicianRow'], [id^='physicianAddressRow'], [id^='previousDentistsRow'], [id^='treatmentDoneRow'], [id^='referredByRow'], [id^='lastDentalVisitRow'], [id^='reasonForVisitRow']").forEach(field => field.style.display = "none");
        });
      
    </script>
</body>
</html>