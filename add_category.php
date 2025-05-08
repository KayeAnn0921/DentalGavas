<?php
// Include database connection file
include 'sidebar.php'; 
include 'config.php'; // Ensure database connection is included

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']); // Corrected key

    if (!empty($category_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO category (name) VALUES (:name)");
            $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $message = "Category added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding category. Please try again.";
                $message_type = "error";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . htmlspecialchars($e->getMessage());
            $message_type = "error";
        }
    } else {
        $message = "Category name cannot be empty.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAVAS DENTAL CLINIC - Add Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/services.css"/> <!-- Reuse the same CSS -->
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
            <button type="submit">Add Category</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Existing Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM category");
                    if ($stmt->rowCount() > 0) {
                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($category['name']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td class="no-records">No categories found. Add your first category above.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td class="no-records">Error loading categories: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>