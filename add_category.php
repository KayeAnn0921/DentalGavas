<?php
include 'sidebar.php';
include 'config.php';

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new category
    if (isset($_POST['add'])) {
        $category_name = trim($_POST['category_name']);
        if (!empty($category_name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO category (name) VALUES (:name)");
                $stmt->bindParam(':name', $category_name);
                $stmt->execute();
                $message = "Category added successfully!";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Error adding category: " . htmlspecialchars($e->getMessage());
                $message_type = "error";
            }
        } else {
            $message = "Category name cannot be empty.";
            $message_type = "error";
        }
    }

    // Edit category
    if (isset($_POST['edit']) && isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $new_name = trim($_POST['new_name']);
        if (!empty($new_name)) {
            try {
                $stmt = $pdo->prepare("UPDATE category SET name = :name WHERE category_id = :id");
                $stmt->bindParam(':name', $new_name);
                $stmt->bindParam(':id', $edit_id);
                $stmt->execute();
                $message = "Category updated successfully!";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Error updating category: " . htmlspecialchars($e->getMessage());
                $message_type = "error";
            }
        }
    }

    // Delete category
    if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM category WHERE category_id = :id");
            $stmt->bindParam(':id', $delete_id);
            $stmt->execute();
            $message = "Category deleted successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error deleting category: " . htmlspecialchars($e->getMessage());
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAVAS DENTAL CLINIC - Category Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/services.css"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="form-container">
        <h2>Add New Service Category</h2>
        <?php if ($message): ?>
            <div class="message <?= $message_type; ?>"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" required>
            <button type="submit" name="add">Add Category</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Existing Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM category ORDER BY name ASC");

                    if ($stmt->rowCount() > 0) {
                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>";
                            if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $category['category_id']) {
                                echo "<form method='POST' action=''>";
                                echo "<input type='text' name='new_name' value='" . htmlspecialchars($category['name']) . "' required>";
                                echo "<input type='hidden' name='edit_id' value='" . $category['category_id'] . "'>";
                                echo "<button type='submit' name='edit'>Save</button>";
                                echo "</form>";
                            } else {
                                echo htmlspecialchars($category['name']);
                            }
                            echo "</td>";
                            echo "<td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='edit_mode' value='{$category['category_id']}'>
                                    <button type='submit'><i class='fas fa-edit'></i></button>
                                </form>
                                <form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this category?');\">
                                    <input type='hidden' name='delete_id' value='{$category['category_id']}'>
                                    <button type='submit' name='delete'><i class='fas fa-trash-alt'></i></button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="2" class="no-records">No categories found. Add your first category above.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="2" class="no-records">Error loading categories: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
