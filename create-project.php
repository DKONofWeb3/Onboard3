<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'project_owner') {
    header("Location: ../auth/login.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $x_link = trim($_POST['x_link']);
    $telegram = trim($_POST['telegram']);
    $description = trim($_POST['description']);
    $x_target = intval($_POST['x_target']);
    $tg_target = intval($_POST['tg_target']);

    $total_tasks = $x_target + $tg_target;
    $total_spent = $total_tasks * 0.50;

    $owner_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO projects (name, x_link, telegram, description, spent, x_target, tg_target, owner_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $x_link, $telegram, $description, $total_spent, $x_target, $tg_target, $owner_id, 'pending']);
    $msg = "Project submited! Pending admin confirmation";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Post New Project</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f7f7f7;
      padding: 40px;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    input, textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
    }
    button {
      padding: 12px 20px;
      background: #4158d0;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
    button:disabled {
      background: #aaa;
      cursor: not-allowed;
    }
    .wallet-box {
      background: #f1f1f1;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .wallet-box p {
      margin: 6px 0;
    }
    .copy-btn {
      margin-left: 10px;
      padding: 4px 8px;
      font-size: 12px;
      cursor: pointer;
    }
    .msg {
      text-align: center;
      margin-top: 10px;
      color: green;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Post a New Project</h2>
    <form method="POST" id="projectForm">
      <input type="text" name="title" placeholder="Project Title" required>
      <input type="text" name="x_link" placeholder="X (Twitter) Link" required>
      <input type="text" name="telegram" placeholder="Telegram Group Link" required>
      <textarea name="description" rows="4" placeholder="Brief Description" required></textarea>
      <input type="number" name="x_target" id="x_target" placeholder="Target X Followers" min="0" required>
      <input type="number" name="tg_target" id="tg_target" placeholder="Target Telegram Joins" min="0" required>

      <p><strong>Total Cost:</strong> $<span id="total_cost">0.00</span></p>

      <div class="wallet-box">
        <p><strong>Pay to any of these wallet addresses:</strong></p>
        <p>Solana: <code id="sol">5A6i8zcYNxP1UmV3NEksaVu3jjgamCfdkDxDdTyw1UuT</code> <button type="button" class="copy-btn" onclick="copy('sol')">Copy</button></p>
        <p>BTC: <code id="btc">bc1qa0t9t8fec9a56v5225py2x4ge3hx7yhm0k9jjq</code> <button type="button" class="copy-btn" onclick="copy('btc')">Copy</button></p>
        <p>ETH: <code id="eth">0xA059565ac678ed50d07e5C8f69d8114c390f9076</code> <button type="button" class="copy-btn" onclick="copy('eth')">Copy</button></p>
        <p>USDT (TRC20): <code id="usdt_tron">TYKhqbWVwzoXV9Tjzvphnmzms78eUuZQjt</code> <button type="button" class="copy-btn" onclick="copy('usdt_tron')">Copy</button></p>
        <p>USDT (TON): <code id="usdt_ton">UQAl0vkLkcX5lofVg__nxbUy-VMDCn16D5DaI6UUFFauNDV8</code> <button type="button" class="copy-btn" onclick="copy('usdt_ton')">Copy</button></p>
        <p><button type="button" onclick="confirmPayment()">I’ve Paid</button></p>
      </div>

      <button type="submit" id="postBtn" disabled>Post Project</button>
    </form>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
  </div>

  <script>
    const xInput = document.getElementById("x_target");
    const tgInput = document.getElementById("tg_target");
    const costDisplay = document.getElementById("total_cost");
    const postBtn = document.getElementById("postBtn");

    function updateCost() {
      const x = parseInt(xInput.value) || 0;
      const tg = parseInt(tgInput.value) || 0;
      const total = (x + tg) * 0.5;
      costDisplay.textContent = total.toFixed(2);
    }

    function confirmPayment() {
      alert("Payment confirmed. You can now post the project.");
      postBtn.disabled = false;
    }

    function copy(id) {
      const text = document.getElementById(id).textContent;
      navigator.clipboard.writeText(text).then(() => {
        alert("Wallet address copied!");
      });
    }

    xInput.addEventListener("input", updateCost);
    tgInput.addEventListener("input", updateCost);
    updateCost();
  </script>
</body>
</html>