<?php
include 'config.php';

$editMode = false;
$editUser = [
    'user_id' => '',
    'username' => '',
    'password' => '',
    'type' => ''
];

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$deleteId]);
    header("Location: user.php");
    exit();
}

// Handle update (edit save)
if (isset($_POST['edit_id'])) {
    $editId = $_POST['edit_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, type=? WHERE user_id=?");
    $stmt->execute([$username, $password, $type, $editId]);
    header("Location: user.php");
    exit();
}

// Load user data for editing
if (isset($_GET['edit'])) {
    $editMode = true;
    $editId = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle add new user
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['edit_id'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, type) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $type]);
    header("Location: user.php");
    exit();
}

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ?");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users");
}
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <?php
    include 'sidebar.php';
    ?>
    <div class="main-content">
        <h1 class="page-title">User</h1>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".dropdown-toggle").forEach((toggle) => {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    let parent = this.closest(".dropdown-container");
                    let arrow = this.querySelector(".arrow");

                    // Close all other dropdowns
                    document.querySelectorAll(".dropdown-container").forEach((item) => {
                        if (item !== parent) {
                            item.classList.remove("active");
                            item.querySelector(".dropdown").style.display = "none";
                            item.querySelector(".arrow").style.transform = "rotate(0deg)";
                        }
                    });

                    // Toggle current dropdown
                    parent.classList.toggle("active");
                    let dropdown = parent.querySelector(".dropdown");

                    if (parent.classList.contains("active")) {
                        dropdown.style.display = "block";
                        arrow.style.transform = "rotate(180deg)";
                    } else {
                        dropdown.style.display = "none";
                        arrow.style.transform = "rotate(0deg)";
                    }
                });
            });
        });
    </script>


    <div class="user-section">
    <div class="user-form">
    <h2><i class="fas fa-plus"></i> <?= $editMode ? 'Edit User' : 'Add User' ?></h2>
<form method="POST" action="">
    <?php if ($editMode): ?>
        <input type="hidden" name="edit_id" value="<?= $editUser['user_id'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="username">User Name</label>
        <input type="text" id="username" name="username" placeholder="Enter username"
               value="<?= htmlspecialchars($editUser['username']) ?>" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password"
               value="<?= htmlspecialchars($editUser['password']) ?>" required>
    </div>
    <div class="form-group">
        <label for="type">Type</label>
        <select name="type" id="type" required>
            <option value="">-- Select Type --</option>
            <option value="Doctor" <?= $editUser['type'] == 'Doctor' ? 'selected' : '' ?>>Doctor</option>
            <option value="Secretary" <?= $editUser['type'] == 'Secretary' ? 'selected' : '' ?>>Secretary</option>
        </select>
    </div>

    <button type="submit" class="submit-btn"><?= $editMode ? 'Update' : 'Submit' ?></button>
</form>


    </div>

    <div class="user-list">

    <div class="list-header">
    <h3>User List</h3>
    <form method="GET" action="user.php" style="display:flex; gap:10px;">
        <input type="text" class="search-box" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>

        </div>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>NAME</th>
                    <th>PASSWORD</th>
                    <th>TYPE</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($result as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['user_id']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['password']) ?></td>
        <td><?= htmlspecialchars($row['type']) ?></td>
        <td>
            <!-- Edit button -->
            <a href="user.php?edit=<?= $row['user_id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
            <!-- Delete button -->
            <a href="user.php?delete=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

        </table>
    </div>
</div>


    

</body>
</html>
