<?php 
include 'config.php';

$message = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== "" ? intval($_POST['parent_id']) : NULL;

    if (empty($name)) {
        $message = "Service name is required!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO classification (name, price, parent_id) VALUES (:name, :price, :parent_id)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Service added successfully!";
}

function buildOptions($pdo, $parent_id = NULL, $prefix = '') {
    $stmt = $pdo->prepare($parent_id === NULL ? 
        "SELECT classification_id, name FROM classification WHERE parent_id IS NULL ORDER BY name" :
        "SELECT classification_id, name FROM classification WHERE parent_id = :parent_id ORDER BY name"
    );
    if ($parent_id !== NULL) $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='{$row['classification_id']}'>{$prefix}{$row['name']}</option>";
        buildOptions($pdo, $row['classification_id'], $prefix . "↳ ");
    }
}

function displayClassifications($pdo, $parent_id = null, $indent = 0) {
    global $search;
    
    $query = "SELECT * FROM classification WHERE ";
    $query .= $parent_id === null ? "parent_id IS NULL" : "parent_id = :parent_id";
    
    if (!empty($search)) {
        $query .= " AND name LIKE :search";
    }
    
    $query .= " ORDER BY name";
    
    $stmt = $pdo->prepare($query);
    if ($parent_id !== null) $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    if (!empty($search)) $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    
    $stmt->execute();
    
    while ($classification = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $padding = 10 + ($indent * 20);
        $indicator = str_repeat('↳ ', $indent);
        $rowClass = $indent === 0 ? 'main-service' : 'sub-service';
        
        echo "<tr class='{$rowClass}'>";
        echo "<td style='padding-left: {$padding}px;'>{$indicator}" . htmlspecialchars($classification['name']) . "</td>";
        
        // Modified line: Only display price if it's not zero
        if ($classification['price'] > 0) {
            echo "<td>₱" . number_format($classification['price'], 2) . "</td>";
        } else {
            echo "<td></td>"; // Empty cell when price is zero
        }
        
        echo "<td class='actions'>";
        echo "<a href='edit_classification.php?id={$classification['classification_id']}' class='edit-btn'><i class='fa fa-pencil'></i> </a>";
        echo "<a href='delete_classification.php?id={$classification['classification_id']}' class='delete-btn' onclick='return confirm(\"Are you sure?\")'><i class='fa fa-trash'></i> </a>";
        echo "</td>";
        echo "</tr>";
        
        displayClassifications($pdo, $classification['classification_id'], $indent + 1);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GAVAS DENTAL CLINIC - Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/services.css"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h1>GAVAS DENTAL CLINIC</h1>
    <h2>Services</h2>

    <div class="form-container">
        <h2>Add New Service</h2>
        <?php if ($message): ?>
            <div class="message <?php echo isset($_GET['success']) ? 'success' : 'error'; ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label for="name">Service Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="0.00">
            
            <label for="parent_id">Parent Service (for sub-services):</label>
            <select id="parent_id" name="parent_id">
                <option value="">-- None (Main Service) --</option>
                <?php buildOptions($pdo); ?>
            </select>
            
            <button type="submit">Add Service</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Services List</h2>
        
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search classification..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $hasRecords = $pdo->query("SELECT COUNT(*) FROM classification")->fetchColumn() > 0;
                    if ($hasRecords) {
                        displayClassifications($pdo);
                    } else {
                        echo '<tr><td colspan="3" class="no-records">No services found. Add your first service above.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="3" class="no-records">Error loading services: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>