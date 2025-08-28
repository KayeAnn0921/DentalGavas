<?php
// filepath: c:\xampp\htdocs\DentalGavas\manage_discount_rates.php
include 'config.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM discount_rates WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = "Discount rate deleted!";
}

// Handle edit fetch
$edit_row = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM discount_rates WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_row = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discount_type = $_POST['discount_type'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $rate = (float)$_POST['rate'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if ($edit_id) {
        // Update
        $stmt = $pdo->prepare("UPDATE discount_rates SET discount_type=?, start_date=?, end_date=?, rate=? WHERE id=?");
        $stmt->execute([$discount_type, $start_date, $end_date, $rate, $edit_id]);
        $msg = "Discount rate updated!";
        $edit_row = null;
    } else {
        // Check for overlap (optional, can be improved)
        $query = "SELECT id FROM discount_rates WHERE discount_type = ? AND (
            (start_date <= ? AND (end_date >= ? OR end_date IS NULL)) OR
            (start_date <= ? AND (end_date >= ? OR end_date IS NULL))
        )";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$discount_type, $start_date, $start_date, $end_date ?? $start_date, $end_date ?? $start_date]);
        if ($row = $stmt->fetch()) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE discount_rates SET rate = ?, start_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([$rate, $start_date, $end_date, $row['id']]);
            $msg = "Discount rate updated!";
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO discount_rates (discount_type, start_date, end_date, rate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$discount_type, $start_date, $end_date, $rate]);
            $msg = "Discount rate added!";
        }
    }
}

// Fetch all rates
$stmt = $pdo->query("SELECT * FROM discount_rates ORDER BY start_date DESC, discount_type");
$rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

$discount_types = [
    'pwd' => 'PWD',
    'student' => 'Student',
    'health_insurance' => 'Health Insurance',
    'philhealth' => 'PhilHealth',
    'senior' => 'Senior Citizen'
];
include 'sidebar.php'; // If you have a sidebar/header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Discount Rates</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fa; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(25,118,210,0.10); padding: 32px; }
        h2 { color: #1976d2; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #e3e7ea; padding: 8px 10px; text-align: center; }
        th { background: #e3f0fc; color: #1976d2; }
        tr:nth-child(even) { background: #f8fbfd; }
        .form-row { display: flex; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; justify-content: center; }
        .form-row select, .form-row input { padding: 7px 10px; border-radius: 5px; border: 1px solid #b0bec5; }
        .btn { background: #1976d2; color: #fff; border: none; border-radius: 5px; padding: 8px 18px; font-weight: bold; cursor: pointer; }
        .btn-edit { background: #43a047; }
        .btn-delete { background: #e53935; }
        .btn-edit:hover { background: #2e7031; }
        .btn-delete:hover { background: #b71c1c; }
        .msg { background: #e8f5e9; color: #388e3c; padding: 10px 16px; border-radius: 7px; margin-bottom: 18px; text-align: center; }
        @media (max-width: 700px) {
            .form-row { flex-direction: column; gap: 8px; }
            .container { padding: 10px 2vw; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Discount Rates</h2>
    <?php if (!empty($msg)): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" class="form-row">
        <input type="hidden" name="edit_id" value="<?= $edit_row ? htmlspecialchars($edit_row['id']) : '' ?>">
        <select name="discount_type" required>
            <option value="">Select Type</option>
            <?php foreach ($discount_types as $key => $label): ?>
                <option value="<?= $key ?>" <?= $edit_row && $edit_row['discount_type'] == $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="start_date" required value="<?= $edit_row ? htmlspecialchars($edit_row['start_date']) : '' ?>">
        <input type="date" name="end_date" placeholder="Leave blank for ongoing" value="<?= $edit_row && $edit_row['end_date'] ? htmlspecialchars($edit_row['end_date']) : '' ?>">
        <input type="number" name="rate" min="0" max="100" step="0.01" placeholder="Rate (%)" required value="<?= $edit_row ? htmlspecialchars($edit_row['rate']) : '' ?>">
        <button type="submit" class="btn"><?= $edit_row ? 'Update' : 'Save' ?></button>
        <?php if ($edit_row): ?>
            <a href="manage_discount_rates.php" class="btn" style="background:#888;">Cancel</a>
        <?php endif; ?>
    </form>
    <table>
        <thead>
            <tr>
                <th>Discount Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Rate (%)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rates as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($discount_types[$row['discount_type']] ?? $row['discount_type']) ?></td>
                    <td><?= htmlspecialchars($row['start_date']) ?></td>
                    <td><?= $row['end_date'] ? htmlspecialchars($row['end_date']) : '<span style="color:#388e3c;font-weight:bold;">Ongoing</span>' ?></td>
                    <td><?= htmlspecialchars($row['rate']) ?>%</td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this discount rate?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>