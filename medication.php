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

// Fetch all medications
try {
    $stmt = $pdo->query("SELECT * FROM medications ORDER BY name");
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
            
            <h3>+Add New Medication</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Medication Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Enter medication name" required>
                </div>
                
                <div class="form-group">
                    <label for="dosage">Dosage <span class="required">*</span></label>
                    <input type="text" id="dosage" name="dosage" placeholder="Enter dosage" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" placeholder="Enter description">
                </div>
                
                <button type="submit" name="add_medication">Add Medication</button>
            </form>
            
            <!-- Search Bar -->
            <div class="search-bar">
                <h3>Medication List</h3>
                <input type="text" id="searchInput" placeholder="Search medications..." onkeyup="searchMedications()">
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
    <tbody>
      <tr>
        <td>addada</td>
        <td>adda</td>
        <td>adfaf</td>
        <td>5</td>
        <td class="actions-cell">
          <div class="action-buttons">
            <button class="action-btn view"><i class="fa fa-eye"></i></button>
            <button class="action-btn edit"><i class="fa fa-pencil"></i></button>
            <button class="action-btn delete"><i class="fa fa-trash"></i></button>
          </div>
        </td>
      </tr>
      <tr>
        <td>kaye</td>
        <td>adah</td>
        <td>ajdadj</td>
        <td>2</td>
        <td class="actions-cell">
          <div class="action-buttons">
            <button class="action-btn view"><i class="fa fa-eye"></i></button>
            <button class="action-btn edit"><i class="fa fa-pencil"></i></button>
            <button class="action-btn delete"><i class="fa fa-trash"></i></button>
          </div>
        </td>
      </tr>
      <tr>
        <td>lal</td>
        <td>DADK</td>
        <td>ADKDKA</td>
        <td>4</td>
        <td class="actions-cell">
          <div class="action-buttons">
            <button class="action-btn view"><i class="fa fa-eye"></i></button>
            <button class="action-btn edit"><i class="fa fa-pencil"></i></button>
            <button class="action-btn delete"><i class="fa fa-trash"></i></button>
          </div>
        </td>
      </tr>
      <tr>
        <td>lolo</td>
        <td>AJDAJ</td>
        <td>ADKKDA</td>
        <td>6</td>
        <td class="actions-cell">
          <div class="action-buttons">
            <button class="action-btn view"><i class="fa fa-eye"></i></button>
            <button class="action-btn edit"><i class="fa fa-pencil"></i></button>
            <button class="action-btn delete"><i class="fa fa-trash"></i></button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

        </section>
    </div>

    <script>
    function searchMedications() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toUpperCase();
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
    }
    </script>
</body>
</html>