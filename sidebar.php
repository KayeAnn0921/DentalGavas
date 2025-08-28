<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SIDEBAR</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link rel="stylesheet" href="css/sidebar.css" />
</head>
<body>
  <div class="sidebar">
    <ul>
      <div class="logo-container">
        <img src="./img/logo.ico" alt="Gavas Dental Clinic Logo">
        <span class="logo-text">GAVAS DENTAL CLINIC</span>
      </div>

      <!-- ✅ Working Dashboard Link -->
      <li>
        <a href="dashboard.php" class="dropdown-toggle">
          <i class="fas fa-chart-line"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- Records -->
      <li class="dropdown-container">
        <a href="#" class="dropdown-toggle">
          <i class="fas fa-book"></i>
          <span>Records</span>
          <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="dropdown">
          <li><a href="add_category.php">Category</a></li>
          <li><a href="services.php">Services</a></li>
          <li><a href="user.php">User</a></li>
          <li><a href="patient.php">Patient</a></li>
          <li><a href="medication.php">Medication</a></li>
        </ul>
      </li>

      <!-- Appointments -->
      <li class="dropdown-container">
        <a href="#" class="dropdown-toggle">
          <i class="fas fa-calendar-check"></i>
          <span>Appointments</span>
          <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="dropdown">
          <li><a href="schedule.php">Schedule</a></li>
          <li><a href="appointmentlist.php">Appointments</a></li>
        </ul>
      </li>

      <!-- Medical Records -->
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

      <!-- Cashiering and Billing -->
      <li class="dropdown-container">
        <a href="#" class="dropdown-toggle">
          <i class="fas fa-cash-register"></i>
          <span>Cashiering and Billing</span>
          <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="dropdown">
          <li><a href="cashiering.php">Cashiering</a></li>
          <li><a href="billing.php">Billing</a></li>
          <li><a href="manage_discount_rates.php">Manage Rates</a></li>
        </ul>
      </li>

      <!-- Reports -->
      <li class="dropdown-container">
        <a href="#" class="dropdown-toggle">
          <i class="fas fa-cash-register"></i>
          <span>Reports</span>
          <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="dropdown">
          <li><a href="patient_report.php">Patient Info List</a></li>
          <li><a href="appointment_report.php">Appointment List</a></li>
          <li><a href="collection_report.php">Collection</a></li>
        </ul>
      </li>

      <!-- Logout -->
      <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </ul>
  </div>

  <!-- ✅ Script with Dashboard Fix -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Dropdown toggle logic
      document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
        toggle.addEventListener("click", function (e) {
          const parent = this.closest(".dropdown-container");
          const arrow = this.querySelector(".arrow");

          // Only prevent default if it has a dropdown
          if (this.nextElementSibling?.classList.contains("dropdown")) {
            e.preventDefault();

            // Close other dropdowns
            document.querySelectorAll(".dropdown-container").forEach(item => {
              if (item !== parent) {
                item.classList.remove("active");
                const itemArrow = item.querySelector(".arrow");
                if (itemArrow) itemArrow.style.transform = "rotate(0deg)";
              }
            });

            // Toggle this dropdown
            parent.classList.toggle("active");
            arrow.style.transform = parent.classList.contains("active") ? "rotate(180deg)" : "rotate(0deg)";
          }
        });
      });

      // Highlight active page in dropdown
      const currentPage = window.location.pathname.split("/").pop();
      document.querySelectorAll(".dropdown a").forEach(link => {
        if (link.getAttribute("href") === currentPage) {
          link.classList.add("active");
          const container = link.closest(".dropdown-container");
          container?.classList.add("active");
          const arrow = container?.querySelector(".arrow");
          if (arrow) arrow.style.transform = "rotate(180deg)";
        }
      });

      // Highlight active page in non-dropdown links
      document.querySelectorAll(".sidebar > ul > li > a").forEach(link => {
        if (link.getAttribute("href") === currentPage) {
          link.classList.add("active");
        }
      });
    });
  </script>
</body>
</html>
