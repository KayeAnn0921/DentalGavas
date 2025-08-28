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

    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->execute([$editId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // If password field is changed, hash it; else, keep old hash
    if (!empty($password) && !password_verify($password, $currentUser['password'])) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $currentUser['password'];
    }

    $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, type=? WHERE user_id=?");
    $stmt->execute([$username, $hashedPassword, $type, $editId]);
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
    // Don't show hash in password field
    $editUser['password'] = '';
}

// Handle add new user
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['edit_id'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, type) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $type]);
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
    <title>User Management | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="header-bar">
            <h1>User Management</h1>
        </div>
        <div class="user-container">
            <div class="card user-form-card">
                <h2><?= $editMode ? 'Edit User' : 'Add User' ?></h2>
                <form method="POST" action="">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="edit_id" value="<?= $editUser['user_id'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username"><i class="fa fa-user"></i> Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter username"
                               value="<?= htmlspecialchars($editUser['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fa fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" placeholder="<?= $editMode ? 'Leave blank to keep current password' : 'Enter password' ?>"
                               value="">
                    </div>
                    <div class="form-group">
                        <label for="type"><i class="fa fa-user-tag"></i> Type</label>
                        <select name="type" id="type" required>
                            <option value="">-- Select Type --</option>
                            <option value="Doctor" <?= $editUser['type'] == 'Doctor' ? 'selected' : '' ?>>Doctor</option>
                            <option value="Secretary" <?= $editUser['type'] == 'Secretary' ? 'selected' : '' ?>>Secretary</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary"><?= $editMode ? 'Update' : 'Add User' ?></button>
                    <?php if ($editMode): ?>
                        <a href="user.php" class="btn-secondary" style="margin-left:10px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card user-list-card">
                <div class="list-header">
                    <h2>User List</h2>
                    <form method="GET" action="user.php" class="search-form">
                        <input type="text" class="search-box" name="search" placeholder="Search username..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn-search"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Password</th>
                                <th>Type</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>••••••••</td>
                                <td><?= htmlspecialchars($row['type']) ?></td>
                                <td class="action-cell">
                                    <a href="user.php?edit=<?= $row['user_id'] ?>" title="Edit" class="action-btn edit"><i class="fas fa-edit"></i></a>
                                    <a href="user.php?delete=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure?')" title="Delete" class="action-btn delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>