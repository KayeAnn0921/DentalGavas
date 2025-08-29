<?php
include 'config.php';

$billing_id = isset($_GET['billing_id']) ? (int)$_GET['billing_id'] : 0;
$message = "";

// Handle new payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_amount'])) {
    $payment_amount = (float)$_POST['payment_amount'];
    $remarks = $_POST['remarks'] ?? '';
    if ($payment_amount > 0 && $billing_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO ledger (billing_id, payment_amount, remarks) VALUES (?, ?, ?)");
        $stmt->execute([$billing_id, $payment_amount, $remarks]);

        // Update billing table
        $stmt = $pdo->prepare("SELECT balance, amount_paid FROM billing WHERE billing_id = ?");
        $stmt->execute([$billing_id]);
        $bill = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($bill) {
            $new_balance = $bill['balance'] - $payment_amount;
            if ($new_balance < 0) $new_balance = 0;
            
            $new_amount_paid = $bill['amount_paid'] + $payment_amount;

            $stmt = $pdo->prepare("UPDATE billing SET balance = ?, amount_paid = ? WHERE billing_id = ?");
            $stmt->execute([$new_balance, $new_amount_paid, $billing_id]);
        }

        $message = "Payment recorded in ledger!";
    }
}

// Fetch ledger entries
$stmt = $pdo->prepare("SELECT * FROM ledger WHERE billing_id = ? ORDER BY payment_date ASC");
$stmt->execute([$billing_id]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch billing info
$stmt = $pdo->prepare("SELECT b.*, CONCAT(p.last_name, ', ', p.first_name, ' ', p.middle_name) AS full_name FROM billing b JOIN patients p ON b.patient_id = p.patient_id WHERE b.billing_id = ?");
$stmt->execute([$billing_id]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
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
            background: #f6f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto 0 auto;
            background: #fff;
            padding: 32px 32px 28px 32px;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        h2 {
            color: #1976D2;
            font-size: 2rem;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 18px;
            font-size: 1.08rem;
        }
        .info-row div {
            background: #f3f6fd;
            border-radius: 6px;
            padding: 8px 16px;
            color: #1976D2;
            font-weight: 500;
        }
        .message {
            color: #388e3c;
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            padding: 10px 18px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 1rem;
        }
        .form-section {
            background: #f9fafb;
            border-radius: 10px;
            padding: 18px 18px 10px 18px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(25,118,210,0.07);
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            font-weight: 500;
            color: #222;
            margin-bottom: 5px;
            display: block;
        }
        input[type="number"], input[type="text"] {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1.2px solid #d1d5db;
            width: 100%;
            font-size: 1rem;
            background: #fff;
            margin-top: 2px;
        }
        input[type="number"]:focus, input[type="text"]:focus {
            border: 1.5px solid #1976D2;
            outline: none;
        }
        button {
            background: #1976D2;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 1.08rem;
            font-weight: 600;
            margin-top: 8px;
            transition: background 0.2s, transform 0.2s;
        }
        button:hover {
            background: #1746a2;
            transform: translateY(-2px);
        }
        h3 {
            color: #2563eb;
            margin-top: 32px;
            margin-bottom: 10px;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(25,118,210,0.07);
        }
        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #e3e3e3;
            text-align: left;
        }
        th {
            background: #1976D2;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background: #e3f2fd;
        }
        .back-link {
            display: inline-block;
            margin-top: 22px;
            color: #1976D2;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.05rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #1746a2;
            text-decoration: underline;
        }
        .recalc-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        @media (max-width: 700px) {
            .container { padding: 12px 2vw; }
            .info-row { flex-direction: column; gap: 7px; }
            h2 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    
<div class="container">
    <h2><i class="fa fa-book"></i> Ledger for Billing #<?= htmlspecialchars($billing_id) ?></h2>
     <div class="info-row">
        <div><strong>Patient:</strong> <?= htmlspecialchars($bill['full_name'] ?? '') ?></div>
        <div><strong>Total:</strong> ₱<?= number_format($bill['total'],2) ?></div>
        <div><strong>Paid:</strong> ₱<?= number_format($bill['amount_paid'],2) ?></div>
        <div><strong>Balance:</strong> ₱<?= number_format($bill['balance'],2) ?></div>
    </div>
    
<?php if (!empty($bill['months']) && $bill['months'] > 1): ?>
    <?php
        $totalMonths = (int)$bill['months'];
        $totalPaid = array_sum(array_column($entries, 'payment_amount'));
        $createdAt = new DateTime($bill['created_at']);
        
        // Calculate remaining balance
        $remainingBalance = $bill['balance'];
        
        // Always use the adjusted monthly amount (never go back to original)
        if ($remainingBalance > 0) {
            // If there's still balance, calculate adjusted monthly amount
            $paidMonths = floor($bill['amount_paid'] / ($bill['total'] / $totalMonths));
            $remainingMonths = $totalMonths - $paidMonths;
            $adjustedMonthly = $remainingBalance / max(1, $remainingMonths);
        } else {
            // If fully paid, use the last known adjusted amount
            // Calculate what the adjusted amount was before full payment
            $adjustedMonthly = $bill['total'] / $totalMonths; // Default to original
            if ($totalPaid > 0 && $bill['amount_paid'] > $bill['total']) {
                // If overpaid, use the average payment amount
                $adjustedMonthly = $bill['amount_paid'] / $totalMonths;
            } elseif ($totalPaid > 0) {
                // Use the actual average payment amount
                $adjustedMonthly = $bill['amount_paid'] / $totalMonths;
            }
        }
        
        // Recalculate paid months based on adjusted amount
        $paidMonths = floor($bill['amount_paid'] / $adjustedMonthly);
        $remainingMonths = $totalMonths - $paidMonths;
        $remainingPayment = $totalPaid;
        
        // Build the payment schedule
        $monthlySchedule = [];
        
        for ($i = 0; $i < $totalMonths; $i++) {
            $dueDate = clone $createdAt;
            $dueDate->modify('+' . $i . ' months');
            
            $paidForThisMonth = 0;
            $status = 'Unpaid';
            
            if ($i < $paidMonths) {
                // Past months - fully paid at adjusted rate
                $paidForThisMonth = $adjustedMonthly;
                $remainingPayment -= $adjustedMonthly;
                $status = 'Paid';
            } elseif ($i == $paidMonths && $remainingPayment > 0) {
                // Current month - partially paid
                $paidForThisMonth = min($remainingPayment, $adjustedMonthly);
                $remainingPayment -= $paidForThisMonth;
                $status = ($paidForThisMonth >= $adjustedMonthly) ? 'Paid' : 'Partial';
            }
            // Future months remain Unpaid with $paidForThisMonth = 0
            
            $monthlySchedule[$i] = [
                'month' => $i + 1,
                'due_date' => $dueDate,
                'monthly_due' => $adjustedMonthly, // Always the adjusted amount
                'paid' => $paidForThisMonth,
                'remaining' => $adjustedMonthly - $paidForThisMonth,
                'status' => $status
            ];
        }
    ?>
    
    <div style="background:#e3f2fd;padding:10px 18px;border-radius:8px;margin-bottom:18px;font-size:1.08rem;color:#1976D2;">
        <b>Monthly Payment Plan:</b>
        <span style="font-size:0.97em;color:#388e3c;">
            (<?= $totalMonths ?> months @ ₱<?= number_format($adjustedMonthly, 2) ?> per month)
        </span>
    </div>

    <table style="margin-bottom:18px;">
        <thead>
            <tr>
                <th>Month #</th>
                <th>Due Date</th>
                <th>Monthly Due</th>
                <th>Paid</th>
                <th>Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($monthlySchedule as $schedule): ?>
            <tr>
                <td><?= $schedule['month'] ?></td>
                <td><?= $schedule['due_date']->format('F Y') ?></td>
                <td>₱<?= number_format($schedule['monthly_due'], 2) ?></td>
                <td>₱<?= number_format($schedule['paid'], 2) ?></td>
                <td>₱<?= number_format($schedule['remaining'], 2) ?></td>
                <td>
                    <?php
                    $statusColor = '#d32f2f'; // red for unpaid
                    if ($schedule['status'] == 'Paid') $statusColor = '#388e3c'; // green
                    elseif ($schedule['status'] == 'Partial') $statusColor = '#ff9800'; // orange
                    ?>
                    <span style="color:<?= $statusColor ?>;font-weight:600;"><?= $schedule['status'] ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

    <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="form-section">
        <form method="post">
            <div class="form-group">
                <label for="payment_amount">Add Payment:</label>
                <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" max="<?= $bill['balance'] ?>" required>
            </div>
            <div class="form-group">
                <label for="remarks">Remarks (optional):</label>
                <input type="text" name="remarks" id="remarks">
            </div>
            <button type="submit"><i class="fa fa-plus"></i> Add Payment</button>
        </form>
    </div>

    <button onclick="printLedger()" style="padding: 8px 16px; background: var(--accent-blue); color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; float: right; margin-bottom: 15px;">
        🖨️ Print Ledger
    </button>

    <h3><i class="fa fa-history"></i> Payment History</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($entries): foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['payment_date']) ?></td>
                    <td>₱<?= number_format($entry['payment_amount'],2) ?></td>
                    <td><?= htmlspecialchars($entry['remarks']) ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" style="text-align:center;">No payments yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="billing.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Billing Records</a>
</div>
</body>
<script>
    
function printLedger() {
    const bill = <?= json_encode($bill) ?>;
    const ledger = <?= json_encode($entries) ?>;
    const monthlySchedule = <?= json_encode(isset($monthlySchedule) ? array_map(function($item) {
        $item['due_date'] = $item['due_date']->format('Y-m-d');
        return $item;
    }, $monthlySchedule) : []) ?>;
    
    const patientName = bill.full_name || '';
    const billingId = <?= (int)$billing_id ?>;
    const createdAt = new Date(bill.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });

    const formatCurrency = (num) => {
        return '₱' + Number(num).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    // Generate payment plan table if applicable
    let paymentPlanHTML = '';
    let remainingMonthsInfo = '';
    
    if (bill.months && bill.months > 1 && monthlySchedule.length > 0) {
        // Calculate remaining months and current monthly payment from the schedule
        // Calculate remaining months and current monthly payment from the schedule
const remainingMonths = monthlySchedule.filter(month => month.status !== 'Paid').length;
const currentMonthlyPayment = remainingMonths > 0 ? monthlySchedule[monthlySchedule.length - remainingMonths].monthly_due : 0;

// Safe display of remaining months info
remainingMonthsInfo = remainingMonths > 0 ? 
    `${remainingMonths} months remaining @ ${formatCurrency(currentMonthlyPayment)} per month` : 
    'Fully Paid';
        
        paymentPlanHTML = `
            <div style="margin-top: 20px;">
                <h3 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 5px;">Monthly Payment Plan</h3>
                <p style="margin-bottom: 15px; color: #388e3c;">${remainingMonthsInfo}</p>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Month</th>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Due Date</th>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Monthly Due</th>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Paid</th>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Remaining</th>
                            <th style="border: 1px solid #ccc; padding: 8px; background: #f0f0f0;">Status</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        monthlySchedule.forEach(schedule => {
            const dueDate = new Date(schedule.due_date);
            const formattedDueDate = dueDate.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
            
            let statusStyle = 'color: #F44336; font-weight: bold;';
            if (schedule.status === 'Paid') {
                statusStyle = 'color: green; font-weight: bold;';
            } else if (schedule.status === 'Partial') {
                statusStyle = 'color: orange; font-weight: bold;';
            }
            
            paymentPlanHTML += `
                <tr>
                    <td style="border: 1px solid #ccc; padding: 8px;">${schedule.month}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">${formattedDueDate}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">${formatCurrency(schedule.monthly_due)}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">${formatCurrency(schedule.paid)}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">${formatCurrency(schedule.remaining)}</td>
                    <td style="border: 1px solid #ccc; padding: 8px; ${statusStyle}">${schedule.status}</td>
                </tr>`;
        });
        
        paymentPlanHTML += `</tbody></table></div>`;
    }

    let paymentsHTML = '';
    if (ledger.length) {
        paymentsHTML += `
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ccc;padding:8px;">Date</th>
                        <th style="border:1px solid #ccc;padding:8px;">Amount</th>
                        <th style="border:1px solid #ccc;padding:8px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>`;
        ledger.forEach(entry => {
            paymentsHTML += `
                <tr>
                    <td style="border:1px solid #ccc;padding:8px;">${entry.payment_date}</td>
                    <td style="border:1px solid #ccc;padding:8px;">${formatCurrency(entry.payment_amount)}</td>
                    <td style="border:1px solid #ccc;padding:8px;">${entry.remarks || ''}</td>
                </tr>`;
        });
        paymentsHTML += `</tbody></table>`;
    } else {
        paymentsHTML = '<p style="color:#b71c1c;">No payments recorded.</p>';
    }

    const printWindow = window.open('', '', 'width=900,height=700');
    printWindow.document.write(`
        <html>
<head>
    <title>Ledger Print View</title>
    <style>
        body {
            font-family: Georgia, serif;
            background: #fff;
            padding: 40px;
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
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .summary-box {
            margin-bottom: 25px;
            font-size: 1.1em;
        }
        .signature {
            text-align: right;
            margin-top: 60px;
            font-size: 1em;
        }

        /* Add to the print styles section */
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #1976d2;
            padding-bottom: 15px;
            margin-bottom: 20px;
            width: 100%;
        }
        .receipt-header h1 {
            font-size: 24px;
            margin: 0;
            color: #1976d2;
            text-align: center;
        }
        .receipt-header p {
            text-align: center;
            margin: 5px 0;
        }
        .receipt-details {
            margin: 15px 0;
        }
        .receipt-line {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }
        .amount-due {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
            text-align: center;
        }
        
        /* Center the patient details table */
        .patient-details {
            width: 100%;
            margin: 0 auto;
        }
        .patient-details td {
            border: none;
            padding: 5px;
        }
    </style>
</head>
<body onload="window.print(); window.close();">
    <div class="receipt-header">
        <h1>GAVAS DENTAL CLINIC</h1>
        <p>Door 1, Ferrer Building, Saging Street, General Santos City</p>
        <p>Phone: (083) 123-4567 | TIN: 123-456-789-0000</p>
    </div>
    
    <div class="report-title">Ledger Report</div>
   
    <div class="summary-box">
        <table class="patient-details">
            <tr>
                <td style="text-align: right; width: 25%;"><strong>Patient Name:</strong></td>
                <td style="width: 25%;">${patientName}</td>
                <td style="text-align: right; width: 25%;"><strong>Billing ID:</strong></td>
                <td style="width: 25%;">${billingId}</td>
            </tr>
            <tr>
                <td style="text-align: right;"><strong>Date Created:</strong></td>
                <td>${createdAt}</td>
                <td style="text-align: right;"><strong>Payment Plan:</strong></td>
                <td>${bill.months || 1} month(s) - Adjusted</td>
            </tr>
        </table>
        
        ${paymentPlanHTML}
        
        <table style="width:100%; border:1px solid #ccc; margin: 0 auto 20px auto;">
            <tr>
                <td style="border:1px solid #ccc; padding:8px; background:#f5f5f5;"><strong>Total Amount:</strong></td>
                <td style="border:1px solid #ccc; padding:8px; text-align:right;">${formatCurrency(bill.total)}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:8px; background:#f5f5f5;"><strong>Amount Paid:</strong></td>
                <td style="border:1px solid #ccc; padding:8px; text-align:right;">${formatCurrency(bill.amount_paid)}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:8px; background:#f5f5f5;">
                    <strong>Remaining Balance:</strong>
                </td>
                <td style="border:1px solid #ccc; padding:8px; text-align:right;">${formatCurrency(bill.balance)}</td>
            </tr>
        </table>
    </div>
    
    <h3 style="color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 5px; text-align: center;">Payment History</h3>
    ${paymentsHTML}

    <div class="receipt-line"></div>
    <div class="amount-due">
        <p>TOTAL AMOUNT DUE: ${formatCurrency(bill.balance)}</p>
    </div>
    <div class="receipt-line"></div>
    
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