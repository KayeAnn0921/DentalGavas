<?php
include 'config.php';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM billing WHERE billing_id = ?");
        $stmt->execute([$delete_id]);
        header("Location: billing.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        $delete_error = "Error deleting billing record: " . $e->getMessage();
    }
}

// Fetch all billing records (now includes discount_type)
try {
    $stmt = $pdo->query("SELECT b.billing_id, b.patient_id, b.service_id, b.total, b.discount, b.amount_paid, b.balance, b.created_at, b.months, b.discount_type,
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
        :root {
            --primary-blue: #1976D2;
            --dark-blue: #0D47A1;
            --light-blue: #BBDEFB;
            --accent-blue: #2196F3;
            --background: #E3F2FD;
            --card-bg: #FFFFFF;
            --text-dark: #212121;
            --text-light: #757575;
            --success: #4CAF50;
            --danger: #F44336;
            --warning: #FFC107;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        .container {
            max-width: 1400px;
            margin: 20px auto;
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--dark-blue);
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-blue);
        }
        .message {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
            background: #E8F5E9;
            color: var(--success);
            font-weight: 500;
        }
        .message.error {
            background: #FFEBEE;
            color: var(--danger);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9em;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        table th {
            background-color: var(--primary-blue);
            color: white;
            text-align: left;
            font-weight: 600;
            padding: 12px 15px;
        }
        table td {
            padding: 10px 15px;
            border-bottom: 1px solid #dddddd;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table tr:hover {
            background-color: var(--light-blue);
        }
        .form-inline {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-inline input {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100px;
            font-size: 0.9em;
        }
        .form-inline button {
            padding: 6px 12px;
            background-color: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s;
        }
        .form-inline button:hover {
            background-color: var(--dark-blue);
            transform: translateY(-1px);
        }
        .status-paid {
            color: var(--success);
            font-weight: 600;
            background: #E8F5E9;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .status-unpaid {
            color: var(--danger);
            font-weight: 600;
            background: #FFEBEE;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .status-overdue {
            color: #fff;
            background: #F44336;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 6px;
        }
        .discount-type {
            font-weight: 600;
            color: var(--primary-blue);
            background: #e3f2fd;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.95em;
            display: inline-block;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons a {
            text-decoration: none;
            padding: 5px 10px;
            color: white;
            border-radius: 4px;
            font-size: 0.8em;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .action-buttons .print {
            background-color: var(--success);
        }
        .action-buttons .delete {
            background-color: var(--danger);
        }
        .action-buttons .delete:hover {
            background-color: #D32F2F;
        }
        .action-buttons .print:hover {
            background-color: #388E3C;
        }
        .currency {
            font-weight: 600;
            color: var(--dark-blue);
        }
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container">
            <div style="text-align: right; margin-bottom: 15px;">
                <button onclick="printAllBillingRecords()" style="padding: 8px 16px; background: var(--accent-blue); color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    🖨️ Print
                </button>
            </div>

            <h2>Billing Records</h2>
            <?php if (isset($_GET['deleted'])): ?>
                <div class="message">Billing record deleted successfully!</div>
            <?php endif; ?>
            <?php if (!empty($delete_error)): ?>
                <div class="message error"><?= htmlspecialchars($delete_error) ?></div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Billing ID</th>
                        <th>Patient Name</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Discount Type</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Date</th>
                        <th>Months</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Discount type labels for display
                $discount_labels = [
                    'none' => 'None',
                    'pwd' => 'PWD',
                    'student' => 'Student',
                    'health_insurance' => 'Health Insurance',
                    'philhealth' => 'PhilHealth',
                    'senior' => 'Senior Citizen'
                ];
                ?>
                <?php if (!empty($bills)): ?>
                    <?php foreach ($bills as $bill): ?>
                        <tr>
                            <td><?= htmlspecialchars($bill['billing_id']) ?></td>
                            <td><?= htmlspecialchars($bill['full_name']) ?></td>
                            <td class="currency">₱<?= number_format($bill['total'], 2) ?></td>
                            <td class="currency">₱<?= number_format($bill['discount'], 2) ?></td>
                            <td>
                                <span class="discount-type">
                                    <?= isset($discount_labels[$bill['discount_type']]) ? $discount_labels[$bill['discount_type']] : ucfirst($bill['discount_type']) ?>
                                </span>
                            </td>
                            <td class="currency">₱<?= number_format($bill['amount_paid'], 2) ?></td>
                            <td class="currency">₱<?= number_format($bill['balance'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($bill['created_at'])) ?></td>
                            <td>
                                <?= $bill['months'] ? htmlspecialchars($bill['months']) : '-' ?>
                            </td>
                            <td>
                            <?php
                                $isUnpaid = $bill['balance'] > 0;
                                $months = $bill['months'] ? (int)$bill['months'] : 1;
                                $createdAt = new DateTime($bill['created_at']);
                                $dueDate = clone $createdAt;
                                $dueDate->modify("+$months months");
                                $now = new DateTime();
                               if (!$isUnpaid) {
                                    echo '<span class="status-paid">Fully Paid</span>';
                                } elseif ($now > $dueDate) {
                                    echo '<span class="status-overdue">Overdue</span>';
                                } else {
                                    if ($bill['months'] && $bill['months'] > 1) {
                                        $total = $bill['total'];
                                        $perMonth = $total / $bill['months'];
                                        $monthsPaid = floor($bill['amount_paid'] / $perMonth);
                                        echo '<span class="status-unpaid">Unpaid</span>';
                                        echo " <span style='font-size:0.85em;color:#1976D2;'>(" . $monthsPaid . " of " . $bill['months'] . " paid)</span>";
                                    } else {
                                        echo '<span class="status-unpaid">Unpaid</span>';
                                    }
                                }
                            ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="billing.php?delete_id=<?= htmlspecialchars($bill['billing_id']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                    <a href="ledger.php?billing_id=<?= htmlspecialchars($bill['billing_id']) ?>" class="print" style="background:#1976D2;">Ledger</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script>
function printAllBillingRecords() {
    const bills = <?= json_encode($bills) ?>;

    const discountLabels = {
        none: 'None',
        pwd: 'PWD',
        student: 'Student',
        health_insurance: 'Health Insurance',
        philhealth: 'PhilHealth',
        senior: 'Senior Citizen'
    };

    const formatCurrency = (num) => {
        return '₱' + Number(num).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    let rows = bills.map(bill => {
        const createdAt = new Date(bill.created_at);
        const formattedDate = createdAt.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });

        const months = bill.months ? parseInt(bill.months) : 1;
        const isUnpaid = bill.balance > 0;
        const dueDate = new Date(createdAt);
        dueDate.setMonth(dueDate.getMonth() + months);
        const now = new Date();

        let statusHTML = '';
        if (!isUnpaid) {
            statusHTML = `<span style="color:green;font-weight:bold;">Fully Paid</span>`;
        } else if (now > dueDate) {
            statusHTML = `<span style="color:white;background:#F44336;padding:2px 6px;border-radius:6px;">Overdue</span>`;
        } else {
            if (months > 1) {
                const perMonth = bill.total / months;
                const monthsPaid = Math.floor(bill.amount_paid / perMonth);
                statusHTML = `<span style="color:#F44336;font-weight:bold;">Unpaid</span> <span style="color:#1976D2;font-size:0.85em;">(${monthsPaid} of ${months} paid)</span>`;
            } else {
                statusHTML = `<span style="color:#F44336;font-weight:bold;">Unpaid</span>`;
            }
        }

        return `
            <tr>
                <td>${bill.billing_id}</td>
                <td>${bill.full_name}</td>
                <td>${formatCurrency(bill.total)}</td>
                <td>${formatCurrency(bill.discount)}</td>
                <td>${discountLabels[bill.discount_type] || bill.discount_type}</td>
                <td>${formatCurrency(bill.amount_paid)}</td>
                <td>${formatCurrency(bill.balance)}</td>
                <td>${formattedDate}</td>
                <td>${bill.months || '-'}</td>
                <td>${statusHTML}</td>
            </tr>
        `;
    }).join('');

    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>Billing Report</title>
            <style>
                body {
                    font-family: Georgia, serif;
                    background: #fff;
                    padding: 40px;
                    color: #000;
                }
                .clinic-header {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 1.1em;
                }
                .report-title {
                    text-align: center;
                    font-size: 2em;
                    font-weight: bold;
                    color: #1976d2;
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                th, td {
                    border: 1px solid #ccc;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #e3f2fd;
                    color: #0d47a1;
                }
                .signature {
                    text-align: right;
                    margin-top: 60px;
                    font-size: 1em;
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <div class="clinic-header">
                <strong>Dr. Glenn E. Gavas, DMD</strong><br>
                Gavas Dental Clinic<br>
                Door 1, Ferrer Building, Saging Street, General Santos City<br>
                Phone: (083) 123-4567
            </div>

            <div class="report-title">Billing Report</div>

            <table>
                <thead>
                    <tr>
                        <th>Billing ID</th>
                        <th>Patient Name</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Discount Type</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Date</th>
                        <th>Months</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>

            <div class="signature">
                <strong>Dr. Glenn E. Gavas, DMD</strong><br>
                DEA No. 1234563<br>
                State License No. 65432
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

</html>