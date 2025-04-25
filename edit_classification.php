<?php
include 'config.php';

$message = '';
$classification = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: services.php?error=noid");
    exit();
}

$id = intval($_GET['id']);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== "" ? intval($_POST['parent_id']) : NULL;
    
    // Prevent service from being its own parent
    if ($parent_id == $id) {
        $message = "Error: A service cannot be its own parent!";
    } 
    // Validate name
    elseif (empty($name)) {
        $message = "Service name is required!";
    } 
    else {
        try {
            // Check if the selected parent would create a circular reference
            $circular = false;
            if ($parent_id !== NULL) {
                $current_parent = $parent_id;
                while ($current_parent !== NULL && !$circular) {
                    $stmt = $pdo->prepare("SELECT parent_id FROM classification WHERE classification_id = :id");
                    $stmt->bindParam(':id', $current_parent, PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result && $result['parent_id'] == $id) {
                        $circular = true;
                    }
                    $current_parent = $result['parent_id'];
                }
            }
            
            if ($circular) {
                $message = "Error: This would create a circular reference in the service hierarchy!";
            } else {
                $stmt = $pdo->prepare("UPDATE classification SET name = :name, price = :price, parent_id = :parent_id WHERE classification_id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                $message = "Service updated successfully!";
                header("Location: services.php?updated=1");
                exit();
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch the classification data
try {
    $stmt = $pdo->prepare("SELECT * FROM classification WHERE classification_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $classification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$classification) {
        header("Location: services.php?error=notfound");
        exit();
    }
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}

// Function to build the options for the parent dropdown
function buildOptions($pdo, $current_id, $parent_id = NULL, $prefix = '', $selected_id = NULL) {
    $stmt = $pdo->prepare($parent_id === NULL ? 
        "SELECT classification_id, name FROM classification WHERE parent_id IS NULL AND classification_id != :current_id ORDER BY name" :
        "SELECT classification_id, name FROM classification WHERE parent_id = :parent_id AND classification_id != :current_id ORDER BY name"
    );
    $stmt->bindParam(':current_id', $current_id, PDO::PARAM_INT);
    if ($parent_id !== NULL) $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($selected_id !== NULL && $selected_id == $row['classification_id']) ? 'selected' : '';
        echo "<option value='{$row['classification_id']}' {$selected}>{$prefix}{$row['name']}</option>";
        buildOptions($pdo, $current_id, $row['classification_id'], $prefix . "↳ ", $selected_id);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GAVAS DENTAL CLINIC - Edit Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/services.css"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>GAVAS DENTAL CLINIC</h1>
    <h2>Edit Service</h2>

    <div class="form-container">
        <?php if ($message): ?>
            <div class="message error"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($classification): ?>
            <form method="POST" action="">
                <label for="name">Service Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($classification['name']) ?>" required>
                
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= number_format($classification['price'], 2, '.', '') ?>">
                
                <label for="parent_id">Parent Service (for sub-services):</label>
                <select id="parent_id" name="parent_id">
                    <option value="">-- None (Main Service) --</option>
                    <?php buildOptions($pdo, $id, NULL, '', $classification['parent_id']); ?>
                </select>
                
                <div class="form-buttons">
                    <button type="submit">Update Service</button>
                    <a href="services.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p>Service not found.</p>
            <a href="services.php" class="btn">Back to Services</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>