<?php
ob_start();
include 'sidebar.php';
include 'config.php';

$message = "";
$message_type = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add subservices
    if (isset($_POST['add'])) {
        $category_id = intval($_POST['category_id']);
        $subservices = $_POST['subservices'];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO services (name, price, category_id, parent_id) VALUES (:name, :price, :category_id, :parent_id)");
            foreach ($subservices as $sub) {
                if (!empty($sub['name'])) {
                    $stmt->execute([
                        ':name' => $sub['name'],
                        ':price' => floatval($sub['price']),
                        ':category_id' => $category_id,
                        ':parent_id' => !empty($sub['parent_id']) ? $sub['parent_id'] : null
                    ]);
                }
            }
            $pdo->commit();
            $message = "Sub-services added successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
            $message_type = "error";
        }
    }

    // Edit subservice
    if (isset($_POST['edit'])) {
        $id = intval($_POST['edit_id']);
        $name = trim($_POST['edit_name']);
        $price = floatval($_POST['edit_price']);

        try {
            $stmt = $pdo->prepare("UPDATE services SET name = :name, price = :price WHERE service_id = :id");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':id' => $id
            ]);
            $message = "Sub-service updated successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error updating sub-service: " . $e->getMessage();
            $message_type = "error";
        }
    }

    // Delete subservice
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = :id");
            $stmt->execute([':id' => $id]);
            $message = "Sub-service deleted successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error deleting sub-service: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM category")->fetchAll(PDO::FETCH_ASSOC);

// Fetch services for dropdowns
$all_services = $pdo->query("SELECT service_id, name FROM services")->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing sub-services
$subservices = $pdo->query("SELECT s.*, c.name AS category_name, p.name AS parent_name 
                            FROM services s 
                            JOIN category c ON s.category_id = c.category_id
                            LEFT JOIN services p ON s.parent_id = p.service_id
                            ORDER BY c.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
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
            const timestamp = Date.now();
            row.innerHTML = `
                <input type="text" name="subservices[${timestamp}][name]" placeholder="Sub-Service Name" required>
                <input type="number" name="subservices[${timestamp}][price]" placeholder="Price" step="0.01" min="0" required>
                <select name="subservices[${timestamp}][parent_id]">
                    <option value="">None</option>
                    <?php foreach ($all_services as $s): ?>
                        <option value="<?= $s['service_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="this.parentElement.remove()">Remove</button>
            `;
            container.appendChild(row);
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="form-container">
        <h2>Services</h2>
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Category:</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Sub-Services:</label>
            <div id="subservices-container"></div>
            <button type="button" onclick="addSubServiceRow()">Add Sub-Service</button>
            <button type="submit" name="add">Save Services</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Sub-Services List</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Service</th>
                    <th>Sub Service</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subservices as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['category_name']) ?></td>
                    <td>
                        <?php if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $s['service_id']): ?>
                            <form method="POST">
                                <input type="text" name="edit_name" value="<?= htmlspecialchars($s['name']) ?>" required>
                    </td>
                    <td><?= htmlspecialchars($s['parent_name'] ?? 'None') ?></td>
                    <td>
                                <input type="number" name="edit_price" value="<?= htmlspecialchars($s['price']) ?>" required step="0.01" min="0">
                                <input type="hidden" name="edit_id" value="<?= $s['service_id'] ?>">
                                <button type="submit" name="edit">Save</button>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($s['name']) ?>
                    </td>
                    <td><?= htmlspecialchars($s['parent_name'] ?? 'None') ?></td>
                    <td><?= htmlspecialchars(number_format($s['price'], 2)) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="edit_mode" value="<?= $s['service_id'] ?>">
                            <button type="submit"><i class="fas fa-edit"></i></button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service?');">
                            <input type="hidden" name="delete_id" value="<?= $s['service_id'] ?>">
                            <button type="submit" name="delete"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
