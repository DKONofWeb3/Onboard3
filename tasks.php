<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $conn->query("SELECT * FROM projects WHERE status = 'active' ORDER BY id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Available Tasks</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f9f9f9;
      padding: 40px;
    }
    .container {
      max-width: 1000px;
      margin: auto;
    }
    .project {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .project h3 {
      margin-top: 0;
    }
    .project a {
      color: #4158d0;
      text-decoration: none;
    }
    .btn {
      margin-top: 10px;
      display: inline-block;
      padding: 10px 15px;
      background: #4158d0;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
    }
    .rewards {
      margin-top: 10px;
      font-size: 14px;
      color: green;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Available Tasks</h2>

    <?php if (count($projects) === 0): ?>
      <p>No active tasks at the moment.</p>
    <?php else: ?>
      <?php foreach ($projects as $p): ?>
        <div class="project">
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
          <p><a href="<?= $p['x_link'] ?>" target="_blank">Follow on X</a> | 
             <a href="<?= $p['telegram'] ?>" target="_blank">Join Telegram</a></p>
          <p class="rewards">Earn up to $0.30 for completing both tasks</p>
          <a href="submit-proof.php" class="btn" >Submit Proof</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>