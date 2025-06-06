<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Approve or Reject via GET action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] === 'approve' ? 'active' : 'rejected';
    $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id = ?");
    $stmt->execute([$action, $id]);
    header("Location: projects.php");
    exit;
}

// Fetch all projects
$stmt = $conn->query("SELECT p.*, u.username FROM projects p JOIN users u ON p.owner_id = u.id ORDER BY p.id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin – Manage Projects</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      padding: 40px;
      background: #f4f4f4;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #202836;
      color: white;
    }
    .btn {
      padding: 6px 12px;
      margin: 2px;
      text-decoration: none;
      border-radius: 5px;
      color: white;
      font-size: 13px;
    }
    .approve { background: #28a745; }
    .reject { background: #dc3545; }
    .status {
      font-weight: bold;
      text-transform: uppercase;
      font-size: 13px;
    }
    .pending { color: #f39c12; }
    .active { color: #28a745; }
    .rejected { color: #dc3545; }
  </style>
</head>
<body>
  <h2>Project Submissions</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Project Name</th>
        <th>Owner</th>
        <th>X Target</th>
        <th>TG Target</th>
        <th>Spent ($)</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['username']) ?></td>
          <td><?= $p['x_target'] ?></td>
          <td><?= $p['tg_target'] ?></td>
          <td><?= number_format($p['spent'], 2) ?></td>
          <td class="status <?= $p['status'] ?>"><?= $p['status'] ?></td>
          <td>
            <?php if ($p['status'] === 'pending'): ?>
              <a href="?action=approve&id=<?= $p['id'] ?>" class="btn approve">Approve</a>
              <a href="?action=reject&id=<?= $p['id'] ?>" class="btn reject">Reject</a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>