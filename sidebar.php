<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDEBAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
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
                    <li><a href="appointmentlist.php">Appointments</a></li>
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
                  <li><a href="cashiering.php">Cashiering</a></li>
                  <li><a href="billing.php">Billing</a></li>
              </ul>
          </li>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Dropdown functionality
            document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    const parent = this.closest(".dropdown-container");
                    const arrow = this.querySelector(".arrow");
                    
                    // Close other dropdowns
                    document.querySelectorAll(".dropdown-container").forEach(item => {
                        if (item !== parent) {
                            item.classList.remove("active");
                        }
                    });
                    
                    // Toggle current dropdown
                    parent.classList.toggle("active");
                    
                    // Rotate arrow
                    arrow.style.transform = parent.classList.contains("active") 
                        ? "rotate(180deg)" 
                        : "rotate(0deg)";
                });
            });
            
            // Highlight current page
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.dropdown a').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                    const container = link.closest('.dropdown-container');
                    container.classList.add('active');
                    container.querySelector('.arrow').style.transform = "rotate(180deg)";
                }
            });
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('.service-table tbody tr');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    let matches = false;
                    
                    // Check each cell (except the action cell)
                    for (let i = 0; i < cells.length - 1; i++) {
                        if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
                            matches = true;
                            break;
                        }
                    }
                    
                    row.style.display = matches ? '' : 'none';
                });
            });
        });
    </script>
    
</body>
</html>