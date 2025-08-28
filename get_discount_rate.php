<?php
include 'config.php';

$discount_type = $_GET['discount_type'] ?? '';
$today = date('Y-m-d');
$rate = 0;

if ($discount_type && $discount_type !== 'none') {
    $stmt = $pdo->prepare("SELECT rate FROM discount_rates WHERE discount_type = ? AND start_date <= ? AND (end_date >= ? OR end_date IS NULL) ORDER BY start_date DESC LIMIT 1");
    $stmt->execute([$discount_type, $today, $today]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $rate = (float)$row['rate'];
    }
}
echo $rate;