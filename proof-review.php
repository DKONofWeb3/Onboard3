<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle approval or rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';

    // Fetch user ID before updating
    $stmt = $conn->prepare("SELECT user_id FROM submissions WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($status === 'approved' && $user) {
        $add = $conn->prepare("UPDATE users SET balance = balance + 0.15 WHERE id = ?");
        $add->execute([$user['user_id']]);

        $mark = $conn->prepare("UPDATE submissions SET reward_paid = 1 WHERE id = ?");
        $mark->execute([$id]);
    }

    $stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: proof-review.php");
    exit;
}

// Fetch all submissions
$stmt = $conn->query("SELECT s.id, s.proof_link, s.status, s.submitted_at, u.username, p.name AS project_name 
                      FROM submissions s 
                      JOIN users u ON s.user_id = u.id 
                      JOIN projects p ON s.task_id = p.id 
                      ORDER BY s.id DESC");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin – Proof Submissions</title>
  <style>
    body { font-family: sans-serif; padding: 40px; background: #f4f4f4; }
    h2 { text-align: center; margin-bottom: 30px; }
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
    th { background: #202836; color: white; }
    .btn {
      padding: 6px 12px;
      font-size: 13px;
      text-decoration: none;
      border-radius: 5px;
      color: white;
    }
    .approve { background: #28a745; }
    .reject { background: #dc3545; }
    .pending { color: orange; }
    .approved { color: green; }
    .rejected { color: red; }
  </style>
</head>
<body>
  <h2>Proof Submissions</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Project</th>
        <th>Proof</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($submissions as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['username']) ?></td>
          <td><?= htmlspecialchars($s['project_name']) ?></td>
          <td>
            <a href="<?= htmlspecialchars($s['proof_link']) ?>" target="_blank">
              View Proof
            </a>
          </td>
          <td class="<?= $s['status'] ?>"><?= strtoupper($s['status']) ?></td>
          <td><?= $s['submitted_at'] ?? '—' ?></td>
          <td>
            <?php if ($s['status'] === 'pending'): ?>
              <a href="?action=approve&id=<?= $s['id'] ?>" class="btn approve">Approve</a>
              <a href="?action=reject&id=<?= $s['id'] ?>" class="btn reject">Reject</a>
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