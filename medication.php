<?php
include 'config.php';

// --- AJAX handler for subcategory dropdown ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'subcategory' && isset($_GET['main_category_id'])) {
    $main_category_id = $_GET['main_category_id'];
    $selected_id = isset($_GET['selected_id']) ? $_GET['selected_id'] : '';
    $stmt = $pdo->prepare("SELECT * FROM medicine_category WHERE parent_id = ? ORDER BY name");
    $stmt->execute([$main_category_id]);
    $found = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($selected_id == $row['id']) ? 'selected' : '';
        echo "<option value=\"{$row['id']}\" $selected>" . htmlspecialchars($row['name']) . "</option>";
        $found = true;
    }
    if (!$found) {
        echo "<option value=\"\">No medications found</option>";
    }
    exit;
}

// --- Normal page logic below ---

function fetchMainCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM medicine_category WHERE parent_id IS NULL ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$mainCategories = fetchMainCategories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_medication'])) {
        $category_id = $_POST['category_id'];
        $medication_id = $_POST['medication_id'];
        $dosage = $_POST['dosage'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("SELECT name FROM medicine_category WHERE id = ?");
        $stmt->execute([$medication_id]);
        $medication_name = $stmt->fetchColumn();

        try {
            $stmt = $pdo->prepare("INSERT INTO medications (category_id, name, dosage, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$category_id, $medication_name, $dosage, $description]);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $error = "Error adding medication: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_medication'])) {
        $id = $_POST['medication_id'];
        $category_id = $_POST['category_id'];
        $medication_id = $_POST['medication_id_sub'];
        $dosage = $_POST['dosage'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("SELECT name FROM medicine_category WHERE id = ?");
        $stmt->execute([$medication_id]);
        $medication_name = $stmt->fetchColumn();

        try {
            $stmt = $pdo->prepare("UPDATE medications SET category_id = ?, name = ?, dosage = ?, description = ? WHERE id = ?");
            $stmt->execute([$category_id, $medication_name, $dosage, $description, $id]);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $error = "Error updating medication: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_medication'])) {
        $id = $_POST['medication_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $error = "Error deleting medication: " . $e->getMessage();
        }
    }
}

$medication_to_edit = null;
$edit_medication_sub_id = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM medications WHERE id = ?");
        $stmt->execute([$id]);
        $medication_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($medication_to_edit) {
            $stmt = $pdo->prepare("SELECT id FROM medicine_category WHERE name = ? AND parent_id = ?");
            $stmt->execute([$medication_to_edit['name'], $medication_to_edit['category_id']]);
            $edit_medication_sub_id = $stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        $error = "Error fetching medication: " . $e->getMessage();
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $stmt = $pdo->prepare("SELECT m.*, c.name AS category_name, 
        (SELECT mc2.name FROM medicine_category mc2 WHERE mc2.parent_id = m.category_id AND mc2.name = m.name LIMIT 1) AS medication_subcategory
        FROM medications m
        LEFT JOIN medicine_category c ON m.category_id = c.id
        WHERE m.name LIKE ? OR m.dosage LIKE ? OR m.description LIKE ? OR c.name LIKE ?
        ORDER BY m.name");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching medications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAVAS DENTAL CLINIC - Medication Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/medication.css">

    <script>
    function loadMedications(mainCategoryId, selectedId = '') {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'medication.php?ajax=subcategory&main_category_id=' + mainCategoryId + '&selected_id=' + selectedId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('medication_id').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    </script>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="medication-card">
            <h3><?= $medication_to_edit ? 'Edit Medication' : '+ Add New Medication' ?></h3>
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?php if ($medication_to_edit): ?>
                    <input type="hidden" name="medication_id" value="<?= htmlspecialchars($medication_to_edit['id']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="category_id">Medicine Category <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required onchange="loadMedications(this.value, '<?= $edit_medication_sub_id ?>')">
                        <option value="">Select Category</option>
                        <?php
                        $selected_main = $medication_to_edit ? $medication_to_edit['category_id'] : '';
                        foreach ($mainCategories as $cat) {
                            $selected = ($selected_main == $cat['id']) ? 'selected' : '';
                            echo "<option value=\"{$cat['id']}\" $selected>" . htmlspecialchars($cat['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="medication_id">Medication Name <span class="required">*</span></label>
                    <select id="medication_id" name="<?= $medication_to_edit ? 'medication_id_sub' : 'medication_id' ?>" required>
                        <option value="">Select Medication</option>
                        <?php
                        if ($medication_to_edit && $selected_main) {
                            $stmt = $pdo->prepare("SELECT * FROM medicine_category WHERE parent_id = ? ORDER BY name");
                            $stmt->execute([$selected_main]);
                            $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($subs as $sub) {
                                $selected = ($edit_medication_sub_id == $sub['id']) ? 'selected' : '';
                                echo "<option value=\"{$sub['id']}\" $selected>" . htmlspecialchars($sub['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dosage">Dosage <span class="required">*</span></label>
                    <input type="text" id="dosage" name="dosage"
                           value="<?= $medication_to_edit ? htmlspecialchars($medication_to_edit['dosage']) : '' ?>"
                           placeholder="Enter dosage" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description"
                           value="<?= $medication_to_edit ? htmlspecialchars($medication_to_edit['description']) : '' ?>"
                           placeholder="Enter description">
                </div>

                <button type="submit" name="<?= $medication_to_edit ? 'update_medication' : 'add_medication' ?>">
                    <?= $medication_to_edit ? 'Update Medication' : 'Add Medication' ?>
                </button>

                <?php if ($medication_to_edit): ?>
                    <a href="?" class="cancel-btn">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <div class="search-bar">
                <h3>Medication List</h3>
                <form method="GET" action="">
                    <input type="text" name="search" id="searchInput"
                           placeholder="Search medications..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="?" class="clear-search">Clear Search</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="service-table-container">
                <table class="service-list">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicationTableBody">
                        <?php foreach ($medications as $medication): ?>
                            <tr>
                                <td>
                                    <i class="fa fa-folder category-icon"></i>
                                    <?= htmlspecialchars($medication['category_name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($medication['name']) ?>
                                    <?php if ($medication['medication_subcategory']): ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($medication['dosage']) ?></td>
                                <td><?= htmlspecialchars($medication['description']) ?></td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <a href="view_medication.php?id=<?= $medication['id'] ?>" class="action-btn view" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="?action=edit&id=<?= $medication['id'] ?>" class="action-btn edit" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <button type="button" class="action-btn delete"
                                            onclick="deleteMedication(<?= $medication['id'] ?>)" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($medications)): ?>
                            <tr>
                                <td colspan="6">No medications found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    window.onload = function() {
        var mainCategory = document.getElementById('category_id');
        var editSubId = '<?= $edit_medication_sub_id ?>';
        if (mainCategory.value) {
            loadMedications(mainCategory.value, editSubId);
        }
    };

    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        const table = document.getElementById('medicationTableBody');
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length - 1; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            rows[i].style.display = found ? '' : 'none';
        }
    });

    function deleteMedication(id) {
        if (confirm('Are you sure you want to delete this medication?')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'medication_id';
            input.value = id;
            form.appendChild(input);
            var input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'delete_medication';
            input2.value = '1';
            form.appendChild(input2);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>