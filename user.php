<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="sidebar">
        <ul>
            <div class="logo-container">
                <img src="./img/logo.ico" alt="Gavas Dental Clinic Logo">
                <span class="logo-text">GAVAS DENTAL CLINIC</span>
            </div>
            <li class="dropdown-container">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-book"></i>
                    <span>Records</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="dropdown">
                    <li><a href="services.php">Services</a></li>
                    <li><a href="user.php">User</a></li>
                    <li><a href="patient.php">Patient</a></li>
                    <li><a href="medication.php">Medication</a></li>
                </ul>
            </li>
            <li class="dropdown-container">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="dropdown">
                    <li><a href="schedule.php">Schedule</a></li>
                    <li><a href="appointment.php">Appointments</a></li>
                </ul>
            </li>
            <li class="dropdown-container">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-notes-medical"></i>
                    <span>Medical Records</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="dropdown">
                    <li><a href="prescription.php">Prescriptions</a></li>
                    <li><a href="diagnosis.php">Patient Diagnosis</a></li>
                    <li><a href="toothchart.php">Tooth Chart</a></li>
                    <li><a href="medicalhistory.php">Medical History</a></li>
                </ul>
            </li>
            <li class="dropdown-container">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-cash-register"></i>
                    <span>Cashiering and Billing</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="dropdown">
                    <li><a href="cashier.php">Cashiering</a></li>
                    <li><a href="billing.php">Billing</a></li>
                </ul>
            </li>
            <li class="dropdown-container">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-cash-register"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
                <ul class="dropdown">
                    <li><a href="patientinfo.php">Patient Info List</a></li>
                    <li><a href="appointment.php">Appointment List</a></li>
                    <li><a href="collection.php">Collection</a></li>
                </ul>
            </li>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </ul>
    </div>

    <div class="main-content">
        <h1 class="page-title">User</h1>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".dropdown-toggle").forEach((toggle) => {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    let parent = this.closest(".dropdown-container");
                    let arrow = this.querySelector(".arrow");

                    // Close all other dropdowns
                    document.querySelectorAll(".dropdown-container").forEach((item) => {
                        if (item !== parent) {
                            item.classList.remove("active");
                            item.querySelector(".dropdown").style.display = "none";
                            item.querySelector(".arrow").style.transform = "rotate(0deg)";
                        }
                    });

                    // Toggle current dropdown
                    parent.classList.toggle("active");
                    let dropdown = parent.querySelector(".dropdown");

                    if (parent.classList.contains("active")) {
                        dropdown.style.display = "block";
                        arrow.style.transform = "rotate(180deg)";
                    } else {
                        dropdown.style.display = "none";
                        arrow.style.transform = "rotate(0deg)";
                    }
                });
            });
        });
    </script>


<div class="main-content">
        <h1 class="page-title">Records</h1>
        
        <div class="user-management-container">
            <div class="section-box">
                <h2><i class="fas fa-user-plus"></i> Add User</h2>
                <div class="form-group">
                    <strong>User Name</strong>
                    <input type="text" placeholder="Enter user name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <strong>Password</strong>
                        <input type="password" placeholder="Enter password">
                    </div>
                    <div class="form-group">
                        <strong>Type</strong>
                        <select>
                            <option value="">Select type</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <strong>User ID</strong>
                        <input type="text" placeholder="Enter user ID">
                    </div>
                </div>
                <button class="submit-btn">Submit</button>
            </div>

            <div class="section-box">
                <h2><i class="fas fa-users"></i> User List</h2>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Time Login</th>
                            <th>Time out</th>
                            <th>User ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mch</td>
                            <td>Crown</td>
                            <td>1500</td>
                            <td>213344</td>
                            <td class="action-icons">
                                <a href="#"><i class="fas fa-money-bill-wave" title="Payment"></i></a>
                                <a href="#"><i class="fas fa-pound-sign" title="Currency"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".dropdown-toggle").forEach((toggle) => {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    let parent = this.closest(".dropdown-container");
                    let arrow = this.querySelector(".arrow");

                    // Close all other dropdowns
                    document.querySelectorAll(".dropdown-container").forEach((item) => {
                        if (item !== parent) {
                            item.classList.remove("active");
                            item.querySelector(".dropdown").style.display = "none";
                            item.querySelector(".arrow").style.transform = "rotate(0deg)";
                        }
                    });

                    // Toggle current dropdown
                    parent.classList.toggle("active");
                    let dropdown = parent.querySelector(".dropdown");

                    if (parent.classList.contains("active")) {
                        dropdown.style.display = "block";
                        arrow.style.transform = "rotate(180deg)";
                    } else {
                        dropdown.style.display = "none";
                        arrow.style.transform = "rotate(0deg)";
                    }
                });
            });
        });
    </script>
</body>
</html>

</body>
</html>
