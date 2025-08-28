<?php
include 'config.php';

$message = "";
$message_type = "";

// Medicine Category Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medicine'])) {
    $name = trim($_POST['name']);
    $parent_id = $_POST['parent_id'] !== '' ? $_POST['parent_id'] : null;
    if ($name !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO medicine_category (name, parent_id) VALUES (?, ?)");
            $stmt->execute([$name, $parent_id]);
            $message = "Medicine category added successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error adding medicine category: " . htmlspecialchars($e->getMessage());
            $message_type = "error";
        }
    } else {
        $message = "Category name cannot be empty.";
        $message_type = "error";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_medicine_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM medicine_category WHERE id = ?");
        $stmt->execute([$_POST['delete_medicine_id']]);
        $message = "Medicine category deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error deleting medicine category: " . htmlspecialchars($e->getMessage());
        $message_type = "error";
    }
}

// Fetch categories as a tree for better display
function fetchCategoryTree($pdo, $parent_id = null) {
    $stmt = $pdo->prepare("SELECT * FROM medicine_category WHERE parent_id " . (is_null($parent_id) ? "IS NULL" : "= ?") . " ORDER BY name");
    $stmt->execute(is_null($parent_id) ? [] : [$parent_id]);
    $categories = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['children'] = fetchCategoryTree($pdo, $row['id']);
        $categories[] = $row;
    }
    return $categories;
}
$categoryTree = fetchCategoryTree($pdo);

// For dropdown
function fetchCategoriesFlat($pdo, $parent_id = null, $prefix = '') {
    $stmt = $pdo->prepare("SELECT * FROM medicine_category WHERE parent_id " . (is_null($parent_id) ? "IS NULL" : "= ?") . " ORDER BY name");
    $stmt->execute(is_null($parent_id) ? [] : [$parent_id]);
    $categories = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['display_name'] = $prefix . $row['name'];
        $categories[] = $row;
        $categories = array_merge($categories, fetchCategoriesFlat($pdo, $row['id'], $prefix . '— '));
    }
    return $categories;
}
$allCategories = fetchCategoriesFlat($pdo);

// Service Category Logic (unchanged)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO category (name) VALUES (:name)");
            $stmt->bindParam(':name', $category_name);
            $stmt->execute();
            $message = "Service category added successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error adding service category: " . htmlspecialchars($e->getMessage());
            $message_type = "error";
        }
    } else {
        $message = "Category name cannot be empty.";
        $message_type = "error";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_service_id'])) {
    $delete_id = $_POST['delete_service_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM category WHERE category_id = :id");
        $stmt->bindParam(':id', $delete_id);
        $stmt->execute();
        $message = "Service category deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error deleting service category: " . htmlspecialchars($e->getMessage());
        $message_type = "error";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_service']) && isset($_POST['edit_service_id'])) {
    $edit_id = $_POST['edit_service_id'];
    $new_name = trim($_POST['new_service_name']);
    if (!empty($new_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE category SET name = :name WHERE category_id = :id");
            $stmt->bindParam(':name', $new_name);
            $stmt->bindParam(':id', $edit_id);
            $stmt->execute();
            $message = "Service category updated successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error updating service category: " . htmlspecialchars($e->getMessage());
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
</head>
<body>
<?php include 'sidebar.php'; ?>
<style>
   body {
  background: #f4f7fa;
  font-family: 'Segoe UI', Arial, sans-serif;
  margin: 0;
  padding: 0;
}
.main-content {
  max-width: 900px;
  margin: 40px auto 0 auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(25,118,210,0.10);
  padding: 36px 36px 32px 36px;
  margin-left: 500px; /* Adjust for sidebar width */
}
.section-title {
  font-size: 2em;
  color: #1976d2;
  font-weight: 700;
  margin-bottom: 18px;
  letter-spacing: 1px;
  text-align: center;
}
.form-container, .classification-container {
  background: #f8fbfd;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(25,118,210,0.06);
  padding: 24px 24px 18px 24px;
  margin-bottom: 28px;
}
.form-container h2, .classification-container h2 {
  color: #1976d2;
  font-size: 1.2em;
  margin-bottom: 14px;
  font-weight: 700;
}
.form-container label {
  font-weight: 500;
  color: #1976d2;
  margin-bottom: 5px;
  display: block;
}
.form-container input[type="text"], .form-container select {
  width: 100%;
  padding: 9px 12px;
  border-radius: 6px;
  border: 1px solid #b0bec5;
  font-size: 1em;
  background: #f7fbff;
  margin-bottom: 12px;
  transition: border 0.2s;
}
.form-container input[type="text"]:focus, .form-container select:focus {
  border: 1.5px solid #1976d2;
  outline: none;
  background: #e3f2fd;
}
.form-container button[type="submit"] {
  background: #1976d2;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px 18px;
  font-size: 1em;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: 4px;
}
.form-container button[type="submit"]:hover {
  background: #1565c0;
}
.message.success {
  background: #e3f9e5;
  color:rgb(0, 26, 255);
  border-radius: 7px;
  padding: 10px 16px;
  margin-bottom: 14px;
  font-weight: 600;
}
.message.error {
  background: #ffeaea;
  color: #d32f2f;
  border-radius: 7px;
  padding: 10px 16px;
  margin-bottom: 14px;
  font-weight: 600;
}
.classification-container table, .tree-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(25,118,210,0.07);
  margin-top: 10px;
}
.classification-container th, .tree-table th {
  background: #e3f0fc;
  color: #1976d2;
  font-weight: 700;
  padding: 12px 10px;
  text-align: left;
  font-size: 1.05em;
}
.classification-container td, .tree-table td {
  padding: 11px 10px;
  border-bottom: 1px solid #e3e7ea;
  font-size: 1em;
  background: #fff;
}
.classification-container tr:last-child td, .tree-table tr:last-child td {
  border-bottom: none;
}
.action-buttons, .tree-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}
/* Updated Button Styles - All Blue */
.edit-btn, .delete-btn, .form-container button[type="submit"] {
  background: #1976d2;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 6px 10px;
  font-size: 1em;
  cursor: pointer;
  transition: background 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.edit-btn:hover, .delete-btn:hover, .form-container button[type="submit"]:hover {
  background: #1565c0;
}
.no-records, .tree-no-records {
  text-align: center;
  color: #b0bec5;
  font-style: italic;
  padding: 18px 0;
}
.tree-table .tree-icon {
  margin-right: 7px;
  color: #1976d2;
}
.tree-table .tree-main {
  font-weight: 600;
  color: #1976d2;
}
.tree-table .tree-sub {
  color: #333;
}
.tree-indent {
  display: inline-block;
  width: 18px;
}
.subcategory-label {
  background: #e3f0fc;
  color: #1976d2;
  border-radius: 5px;
  padding: 3px 10px;
  font-size: 0.97em;
  font-weight: 500;
}
@media (max-width: 1300px) {
  .main-content { max-width: 98vw; padding: 18px 2vw 24px 2vw; margin-left: 200px; }
}
@media (max-width: 900px) {
  .main-content { max-width: 100vw; padding: 8px 1vw 16px 1vw; margin-left: 160px; }
  .form-container, .classification-container { padding: 10px 1vw 10px 1vw; }
  th, td { padding: 8px 4px; font-size: 0.96rem; }
  .section-title, .form-container h2, .classification-container h2 { font-size: 1.1rem; }
}
@media (max-width: 700px) {
  .main-content, .form-container, .classification-container { padding: 4px 2px; margin-left: 0; }
  th, td { padding: 6px 2px; font-size: 0.93rem; }
  .section-title, .form-container h2, .classification-container h2 { font-size: 1rem; }
  .form-container form button[type="submit"] { padding: 10px 10px; font-size: 0.97rem; }
  .action-buttons, .tree-actions { flex-direction: column; gap: 4px; }
  .classification-container, .tree-table, table { overflow-x: auto; display: block; }
  table, .tree-table { min-width: 600px; }
} 
</style>
<div class="main-content">
    <div class="section-title">Category Management</div>
    <div class="form-container">
        <h2>Add New Service Category</h2>
        <?php if ($message && isset($_POST['add_service'])): ?>
            <div class="message <?= $message_type; ?>"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" required placeholder="e.g. Cleaning, Extraction">
            <button type="submit" name="add_service"><i class="fas fa-plus"></i> Add Service Category</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Existing Service Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="width:120px;">Actions</th>
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
                                echo "<input type='text' name='new_service_name' value='" . htmlspecialchars($category['name']) . "' required>";
                                echo "<input type='hidden' name='edit_service_id' value='" . $category['category_id'] . "'>";
                                echo "<button type='submit' name='edit_service' class='edit-btn'><i class='fas fa-save'></i></button>";
                                echo "</form>";
                            } else {
                                echo htmlspecialchars($category['name']);
                            }
                            echo "</td>";
                            echo "<td class='action-buttons'>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='edit_mode' value='{$category['category_id']}'>
                                    <button type='submit' class='edit-btn'><i class='fas fa-edit'></i></button>
                                </form>
                                <form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this category?');\">
                                    <input type='hidden' name='delete_service_id' value='{$category['category_id']}'>
                                    <button type='submit' class='delete-btn'><i class='fas fa-trash-alt'></i></button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="2" class="no-records">No service categories found. Add your first category above.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="2" class="no-records">Error loading categories: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="form-container">
        <h2>Add Medicine Category / Subcategory</h2>
        <?php if ($message && isset($_POST['add_medicine'])): ?>
            <div class="message <?= $message_type; ?>"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" required placeholder="e.g. Antibiotics, Pain Reliever">

            <label for="parent_id">Parent Category (optional):</label>
            <select id="parent_id" name="parent_id">
                <option value="">None (Main Category)</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['display_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_medicine"><i class="fas fa-plus"></i> Add Medicine Category</button>
        </form>
    </div>

    <div class="classification-container">
        <h2>Medicine Categories</h2>
        <table class="tree-table">
            <thead>
                <tr>
                    <th style="width:50%;">Category Structure</th>
                    <th style="width:30%;">Type</th>
                    <th style="width:20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            function renderCategoryTree($tree, $level = 0) {
                foreach ($tree as $cat) {
                    echo "<tr>";
                    echo "<td>";
                    echo str_repeat('<span class="tree-indent"></span>', $level);
                    if ($level == 0) {
                        echo '<i class="fas fa-folder tree-icon"></i> <span class="tree-main">' . htmlspecialchars($cat['name']) . '</span>';
                    } else {
                        echo '<i class="fas fa-folder-open tree-icon"></i> <span class="tree-sub">' . htmlspecialchars($cat['name']) . '</span>';
                    }
                    echo "</td>";
                    echo "<td>";
                    echo $level == 0 ? '<span class="subcategory-label">Main Category</span>' : '<span class="subcategory-label">Subcategory</span>';
                    echo "</td>";
                    echo "<td class='tree-actions'>
                        <form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this category?');\">
                            <input type='hidden' name='delete_medicine_id' value='{$cat['id']}'>
                            <button type='submit' class='delete-btn'><i class='fas fa-trash-alt'></i></button>
                        </form>
                    </td>";
                    echo "</tr>";
                    if (!empty($cat['children'])) {
                        renderCategoryTree($cat['children'], $level + 1);
                    }
                }
            }
            if (count($categoryTree) > 0) {
                renderCategoryTree($categoryTree);
            } else {
                echo '<tr><td colspan="3" class="tree-no-records">No medicine categories found.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>