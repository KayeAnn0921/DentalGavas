<?php
include 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_medication'])) {
        $name = $_POST['name'];
        $dosage = $_POST['dosage'];
        $description = $_POST['description'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO medications (name, dosage, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $dosage, $description]);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $error = "Error adding medication: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_medication'])) {
        $id = $_POST['medication_id'];
        $name = $_POST['name'];
        $dosage = $_POST['dosage'];
        $description = $_POST['description'];
        
        try {
            $stmt = $pdo->prepare("UPDATE medications SET name = ?, dosage = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $dosage, $description, $id]);
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

// Fetch medication for view/edit
$medication_to_edit = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM medications WHERE id = ?");
        $stmt->execute([$id]);
        $medication_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error fetching medication: " . $e->getMessage();
    }
}

// Fetch all medications with search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $stmt = $pdo->prepare("SELECT * FROM medications 
                          WHERE name LIKE ? OR dosage LIKE ? OR description LIKE ? 
                          ORDER BY name");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
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
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <section id="medication">
            <h2>Medications</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <h3><?= $medication_to_edit ? 'Edit Medication' : '+Add New Medication' ?></h3>
            <form method="POST">
                <?php if ($medication_to_edit): ?>
                    <input type="hidden" name="medication_id" value="<?= htmlspecialchars($medication_to_edit['id']) ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Medication Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" 
                           value="<?= $medication_to_edit ? htmlspecialchars($medication_to_edit['name']) : '' ?>" 
                           placeholder="Enter medication name" required>
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
            
            <!-- Search Bar -->
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
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Description</th>
                            <th>ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicationTableBody">
                        <?php foreach ($medications as $medication): ?>
                            <tr>
                                <td><?= htmlspecialchars($medication['name']) ?></td>
                                <td><?= htmlspecialchars($medication['dosage']) ?></td>
                                <td><?= htmlspecialchars($medication['description']) ?></td>
                                <td><?= htmlspecialchars($medication['id']) ?></td>
                                <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="view_medication.php?id=<?= $medication['id'] ?>" class="action-btn view">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="?action=edit&id=<?= $medication['id'] ?>" class="action-btn edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <button type="button" class="action-btn delete" 
                                    onclick="deleteMedication(<?= $medication['id'] ?>)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>

                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($medications)): ?>
                            <tr>
                                <td colspan="5">No medications found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
    // Client-side search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        const table = document.getElementById('medicationTableBody');
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length - 1; j++) { // Skip the actions column
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
    </script>
</body>
</html>