<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$msg = "";
$userId = $_SESSION['user_id'];

// Fetch user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$balance = $stmt->fetchColumn();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = floatval($_POST['amount']);
    $network = $_POST['network'];
    $wallet = trim($_POST['wallet']);

    if ($amount > $balance) {
        $msg = "Insufficient balance.";
    } elseif ($amount < 1) {
        $msg = "Minimum withdrawal is $1.";
    } else {
        // Save withdrawal request
        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, network, wallet_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $amount, $network, $wallet]);

        // Optional: Deduct balance temporarily
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);

        $msg = "Withdrawal request submitted.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Withdraw</title>
  <style>
    body { font-family: sans-serif; padding: 40px; background: #f7f7f7; }
    .container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 10px; }
    input, select {
      width: 100%;
      margin-bottom: 15px;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    button {
      background: #4158d0;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      width: 100%;
      font-size: 16px;
      cursor: pointer;
    }
    .msg { color: green; text-align: center; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Request Withdrawal</h2>
    <p><strong>Available Balance:</strong> $<?= number_format($balance, 2) ?></p>
    <form method="POST">
      <input type="number" step="0.01" name="amount" placeholder="Amount (Min $1)" required>
      <select name="network" required>
        <option value="">-- Select Network --</option>
        <option value="Solana">Solana</option>
        <option value="Bitcoin">Bitcoin</option>
        <option value="Ethereum">Ethereum</option>
        <option value="USDT (TRC20)">USDT (TRC20)</option>
        <option value="USDT (TON)">USDT (TON)</option>
      </select>
      <input type="text" name="wallet" placeholder="Your Wallet Address" required>
      <button type="submit">Submit Request</button>
    </form>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
  </div>
</body>
</html>