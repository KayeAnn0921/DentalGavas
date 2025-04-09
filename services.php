<?php
// Database connection
$host = 'localhost';
$dbname = 'capstonegavasclinic';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$service = [
    'id' => '',
    'service_name' => '',
    'sub_service_name' => '',
    'amount' => '',
    'service_id' => ''
];
$errors = [];
$success_message = '';
$is_edit = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $service['service_name'] = trim($_POST['service_name']);
    $service['sub_service_name'] = trim($_POST['sub_service_name']);
    $service['amount'] = trim($_POST['amount']);
    $service['service_id'] = trim($_POST['service_id']);
    
  
    // Validate input
    if (empty($service['service_name'])) {
        $errors['service_name'] = 'Service name is required';
    }
    
    if (empty($service['amount']) || !is_numeric($service['amount'])) {
        $errors['amount'] = 'Valid amount is required';
    }
    
    if (empty($service['service_id'])) {
        $errors['service_id'] = 'Service ID is required';
    }
    
    // Check if we're editing or adding
    $is_edit = !empty($_POST['id']);
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Update existing service
                $stmt = $pdo->prepare("UPDATE services SET 
                    service_name = :service_name,
                    sub_service_name = :sub_service_name,
                    amount = :amount,
                    service_id = :service_id
                    WHERE service_id = :id"); // Changed 'id' to 'service_id'
                
                $stmt->bindParam(':id', $_POST['id']); // Bind the correct service_id
                $success_message = 'Service updated successfully!';
            } else {
                 // Insert new service
    $stmt = $pdo->prepare("INSERT INTO services 
    (service_name, amount, created_at, sub_service_name, service_id) 
    VALUES (:service_name, :amount, NOW(), :sub_service_name, :service_id)");
$success_message = 'Service added successfully!';
}
            
            $stmt->bindParam(':service_name', $service['service_name']);
            $stmt->bindParam(':sub_service_name', $service['sub_service_name']);
            $stmt->bindParam(':amount', $service['amount']);
            $stmt->bindParam(':service_id', $service['service_id']);
            
            
            $stmt->execute();
            
            // Redirect to prevent form resubmission
            header("Location: services.php?success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $errors['service_id'] = 'Service ID already exists';
            } else {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_GET['edit'])) {
    // Edit mode - load the service data
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
        $stmt->execute([$_GET['edit']]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$service) {
            header("Location: services.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error loading service: " . $e->getMessage());
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: services.php?success=Service+deleted+successfully");
        exit();
    } catch (PDOException $e) {
        die("Error deleting service: " . $e->getMessage());
    }
}

// Fetch all services for the table
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY service_name");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching services: " . $e->getMessage());
}

// Display success message if present
if (isset($_GET['success'])) {
    $success_message = urldecode($_GET['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="services.css">
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
        <h1 class="page-title">Services Management</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors['database'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['database']); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?php echo $is_edit ? 'Edit Service' : 'Add New Service'; ?></h2>
            
            <form class="service-form" method="POST" action="services.php">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($service['service_id']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="service-name">Service Name *</label>
                    <input type="text" id="service-name" name="service_name" 
                           value="<?php echo htmlspecialchars($service['service_name']); ?>" 
                           placeholder="Enter service name" required>
                    <?php if (!empty($errors['service_name'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['service_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="sub-service">Sub Service Name</label>
                    <input type="text" id="sub-service-name" name="sub_service_name" 
                           value="<?php echo htmlspecialchars($service['sub_service_name']); ?>" 
                           placeholder="Enter sub service name">
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount (₱) *</label>
                    <input type="number" id="amount" name="amount" 
                           value="<?php echo htmlspecialchars($service['amount']); ?>" 
                           placeholder="Enter amount" step="0.01" min="0" required>
                    <?php if (!empty($errors['amount'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['amount']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="service-id">Service ID *</label>
                    <input type="text" id="service-id" name="service_id" 
                           value="<?php echo htmlspecialchars($service['service_id']); ?>" 
                           placeholder="Enter service ID" required>
                    <?php if (!empty($errors['service_id'])): ?>
                        <span class="error"><?php echo htmlspecialchars($errors['service_id']); ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="submit-btn">
                    <?php echo $is_edit ? 'Update Service' : 'Add Service'; ?>
                </button>
                
                <?php if ($is_edit): ?>
                    <a href="services.php" class="cancel-btn">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="service-list-header">
            <h2>Service List</h2>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search services..." class="search-input" id="searchInput">
            </div>
        </div>
        
        <table class="service-table">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Sub Service</th>
                    <th>Amount (₱)</th>
                    <th>Service ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $svc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($svc['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($svc['sub_service_name']); ?></td>
                    <td><?php echo number_format($svc['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($svc['service_id']); ?></td>
                    <td class="action-cell">
                        <a href="services.php?edit=<?php echo $svc['service_id']; ?>" class="action-btn edit-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="services.php?delete=<?php echo $svc['service_id']; ?>" class="action-btn delete-btn" title="Delete" 
                           onclick="return confirm('Are you sure you want to delete this service?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($services)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No services found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
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