<?php
ob_start(); // Start output buffering
include 'sidebar.php'; 
include 'config.php'; // Ensure database connection is included

$message = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $subservices = isset($_POST['subservices']) ? $_POST['subservices'] : [];

    if (empty($category_id)) {
        $message = "Category is required!";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert sub-services for the selected category
            $stmt = $pdo->prepare("INSERT INTO services (name, price, category_id, parent_id) VALUES (:name, :price, :category_id, :parent_id)");
            foreach ($subservices as $subservice) {
                if (!empty($subservice['name'])) {
                    $name = $subservice['name'];
                    $price = isset($subservice['price']) ? floatval($subservice['price']) : 0.00;
                    $parent_id = !empty($subservice['parent_id']) ? intval($subservice['parent_id']) : null;
            
                    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $stmt->bindValue(':price', $price, PDO::PARAM_STR); // Or PDO::PARAM_INT if integer only
                    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
                    $stmt->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
            
                    $stmt->execute();
                }
            }

            $pdo->commit();
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Sub-services added successfully!";
}

// Fetch categories for the dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching categories: " . $e->getMessage();
}

// Fetch all services for parent selection
$services = [];
try {
    $stmt = $pdo->query("SELECT service_id, name FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching services: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GAVAS DENTAL CLINIC - Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/services.css"/>
    <script>
        function addSubServiceRow() {
            const container = document.getElementById('subservices-container');
            const row = document.createElement('div');
            row.classList.add('subservice-row');
            row.innerHTML = `
    <input type="text" name="subservices[${Date.now()}][name]" placeholder="Sub-Service Name" required>
    <input type="number" name="subservices[${Date.now()}][price]" placeholder="Price" step="0.01" min="0" required>
    <select name="subservices[${Date.now()}][parent_id]">
        <option value="">No Parent</option>
        <?php foreach ($services as $service): ?>
            <option value="<?= htmlspecialchars($service['service_id']) ?>"><?= htmlspecialchars($service['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="button" onclick="removeSubServiceRow(this)">Remove</button>
`;
            container.appendChild(row);
        }

        function removeSubServiceRow(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="form-container">
        <h2>Services</h2>
        <?php if ($message): ?>
            <div class="message <?php echo isset($_GET['success']) ? 'success' : 'error'; ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['category_id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Sub-Services:</label>
            <div id="subservices-container"></div>
            <button type="button" onclick="addSubServiceRow()">Add Sub-Service</button>
            
            <button type="submit">Add Services</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Sub-Services List</h2>
        
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search sub-service..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Service Name</th>
                    <th>Sub Service</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT c.name AS category_name, s.name AS service_name, p.name AS parent_name, s.price 
                                         FROM services s 
                                         LEFT JOIN services p ON s.parent_id = p.service_id
                                         JOIN category c ON s.category_id = c.category_id");
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['service_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['parent_name'] ?? 'None') . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="4" class="no-records">No sub-services found. Add your first sub-service above.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="4" class="no-records">Error loading sub-services: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); // End output buffering ?>