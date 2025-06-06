<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = "";

// Get all active projects
$stmt = $conn->query("SELECT id, name FROM projects WHERE status = 'active'");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $projectId = intval($_POST['project_id']);
    $proof = trim($_POST['proof']);

    $check = $conn->prepare("SELECT id FROM submissions WHERE user_id = ? AND task_id = ?");
    $check->execute([$userId, $projectId]);

    if ($check->rowCount() > 0) {
        $msg = "You've already submitted proof for this project.";
    } else {
        $insert = $conn->prepare("INSERT INTO submissions (user_id, task_id, proof_link) VALUES (?, ?, ?)");
        $insert->execute([$userId, $projectId, $proof]);
        $msg = "Proof submitted successfully. Awaiting review.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Submit Proof</title>
  <style>
    body { font-family: sans-serif; padding: 40px; background: #f9f9f9; }
    .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 10px; }
    textarea, select {
      width: 100%;
      margin-bottom: 15px;
      padding: 12px;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    button {
      background: #4158d0;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      width: 100%;
      cursor: pointer;
    }
    .msg { margin-top: 10px; text-align: center; color: green; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Submit Task Proof</h2>
    <form method="POST">
      <select name="project_id" required>
        <option value="">-- Select Project --</option>
        <?php foreach ($projects as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="proof" placeholder="Paste your proof (e.g. username, screenshot link, etc.)" required></textarea>
      <button type="submit">Submit Proof</button>
    </form>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
  </div>
</body>
</html>
       