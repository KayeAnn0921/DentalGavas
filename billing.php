<?php
include 'config.php'; // Include the database configuration

$message = "";

// Handle payment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_id']) && isset($_POST['additional_payment'])) {
    $billing_id = $_POST['billing_id'];
    $additional_payment = $_POST['additional_payment'];

    try {
        // Fetch the current balance
        $stmt = $pdo->prepare("SELECT balance, amount_paid FROM billing WHERE billing_id = ?");
        $stmt->execute([$billing_id]);
        $billing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($billing) {
            $new_amount_paid = $billing['amount_paid'] + $additional_payment;
            $new_balance = $billing['balance'] - $additional_payment;

            // Update the billing record
            $stmt = $pdo->prepare("UPDATE billing SET amount_paid = ?, balance = ? WHERE billing_id = ?");
            $stmt->execute([$new_amount_paid, $new_balance, $billing_id]);

            $message = "Payment updated successfully!";
        } else {
            $message = "Billing record not found.";
        }
    } catch (PDOException $e) {
        $message = "Error updating payment: " . $e->getMessage();
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM billing WHERE billing_id = ?");
        $stmt->execute([$delete_id]);

        $message = "Billing record deleted successfully!";
    } catch (PDOException $e) {
        $message = "Error deleting billing record: " . $e->getMessage();
    }
}

// Fetch all billing records
try {
    $stmt = $pdo->query("SELECT b.billing_id, b.patient_id, b.service_id, b.total, b.discount, b.amount_paid, b.balance, b.created_at, 
                                CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS full_name 
                         FROM billing b
                         JOIN patients p ON b.patient_id = p.patient_id
                         ORDER BY b.billing_id DESC");
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching billing records: " . $e->getMessage());
}
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            color: green;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .form-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-inline input {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-inline button {
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-inline button:hover {
            background-color: #2980b9;
        }

        .status-paid {
            color: green;
            font-weight: bold;
        }

        .status-unpaid {
            color: red;
            font-weight: bold;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 5px 10px;
            color: white;
            border-radius: 4px;
            margin-right: 5px;
        }

        .action-buttons .print {
            background-color: #2ecc71;
        }

        .action-buttons .delete {
            background-color: #e74c3c;
        }

        .action-buttons .delete:hover {
            background-color: #c0392b;
        }

        .action-buttons .print:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Billing Records</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Billing ID</th>
            <th>Patient Name</th>
            <th>Service ID</th>
            <th>Total</th>
            <th>Discount</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($bills)): ?>
            <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><?= htmlspecialchars($bill['billing_id']) ?></td>
                    <td><?= htmlspecialchars($bill['full_name']) ?></td>
                    <td><?= htmlspecialchars($bill['service_id']) ?></td>
                    <td>₱<?= number_format($bill['total'], 2) ?></td>
                    <td>₱<?= number_format($bill['discount'], 2) ?></td>
                    <td>₱<?= number_format($bill['amount_paid'], 2) ?></td>
                    <td>₱<?= number_format($bill['balance'], 2) ?></td>
                    <td><?= htmlspecialchars($bill['created_at']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="print_billing.php?billing_id=<?= htmlspecialchars($bill['billing_id']) ?>" class="print">Print</a>
                            <a href="?delete_id=<?= htmlspecialchars($bill['billing_id']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </div>
                        <?php if ($bill['balance'] > 0): ?>
                            <form class="form-inline" method="POST" action="">
                                <input type="hidden" name="billing_id" value="<?= htmlspecialchars($bill['billing_id']) ?>">
                                <input type="number" name="additional_payment" step="0.01" min="0" placeholder="Enter Payment" required>
                                <button type="submit">Update</button>
                            </form>
                        <?php else: ?>
                            <span class="status-paid">Fully Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center;">No billing records found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>