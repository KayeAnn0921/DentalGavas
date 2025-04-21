<?php
include 'config.php';

// Initialize variables
$service = [
    'id' => '',
    'service_name' => '',
    'sub_service_name' => '',
    'sub_service_detailed' => '',
    'amount' => '',
   
];
$errors = [];
$success_message = '';
$is_edit = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $service['service_name'] = trim($_POST['service_name']);
    $service['sub_service_name'] = trim($_POST['sub_service_name']);
    $service['sub_service_detailed'] = trim($_POST['sub_service_detailed']);
    $service['amount'] = trim($_POST['amount']);
    
   

    // Validate input
    if (empty($service['service_name'])) {
        $errors['service_name'] = 'Service name is required';
    }
    if (empty($service['sub_service_name'])) {
        $errors['sub_service_name'] = 'The main service don\'t have a sub service';
    }
    if (empty($service['sub_service_detailed'])) {
        $errors['sub_service_detailed'] = 'The sub service don\'t have a sub service detailed';
    }
    
    if (empty($service['amount']) || !is_numeric($service['amount'])) {
        $errors['amount'] = 'Valid amount is required';
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
                    sub_service_detailed = :sub_service_detailed,
                    amount = :amount
                    WHERE service_id = :id");
    
                $stmt->bindParam(':service_name', $service['service_name']);
                $stmt->bindParam(':sub_service_name', $service['sub_service_name']);
                $stmt->bindParam(':sub_service_detailed', $service['sub_service_detailed']);
                $stmt->bindParam(':amount', $service['amount']);
                $stmt->bindParam(':id', $_POST['id']);
    
                $stmt->execute();
    
                $success_message = 'Service updated successfully!';
            } else {
                // Insert new service
                $stmt = $pdo->prepare("INSERT INTO services 
                (service_name, sub_service_name, sub_service_detailed, amount) 
                VALUES (:service_name, :sub_service_name, :sub_service_detailed, :amount)");
    
                $stmt->bindParam(':service_name', $service['service_name']);
                $stmt->bindParam(':sub_service_name', $service['sub_service_name']);
                $stmt->bindParam(':sub_service_detailed', $service['sub_service_detailed']);
                $stmt->bindParam(':amount', $service['amount']);
    
                $stmt->execute();
    
                $success_message = 'Service added successfully!';
            }
    
            // Redirect after successful add/update
            header("Location: services.php?success=" . urlencode($success_message));
            exit();
    
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
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
    <link rel="stylesheet" href="css/services.css">
</head>
<body>
    <?php
    include 'sidebar.php';
    ?>

    <div class="main-content">
        <h1 class="page-title">Services</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors['database'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['database']); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?php echo $is_edit ? 'Edit Service' : '+Add New Service'; ?></h2>
            
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
    <label for="sub_service_detailed">Service Detailed</label>
    <input type="text" id="sub_service_detailed" name="sub_service_detailed" 
           value="<?php echo isset($service['sub_service_detailed']) ? htmlspecialchars($service['sub_service_detailed']) : ''; ?>" 
           placeholder="Enter sub service detailed">
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
                    <th>Service ID</th>
                    <th>Service Name</th>
                    <th>Sub Service</th>
                    <th>Sub Service Detailed</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $svc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($svc['service_id']); ?></td>
                    <td><?php echo htmlspecialchars($svc['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($svc['sub_service_name']); ?></td>
                    <td><?php echo htmlspecialchars($svc['sub_service_detailed']); ?></td>
                    <td><?php echo number_format($svc['amount'], 2); ?></td>
                    
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

    
</body>
</html>