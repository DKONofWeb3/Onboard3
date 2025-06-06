<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $newStatus = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    header("Location: withdrawals.php");
    exit;
}

// Get all withdrawal requests
$stmt = $conn->query("SELECT w.*, u.username FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.id DESC");
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin – Withdrawals</title>
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
    .pending { color: orange; font-weight: bold; }
    .approved { color: green; font-weight: bold; }
    .rejected { color: red; font-weight: bold; }
  </style>
</head>
<body>
  <h2>Withdrawal Requests</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Amount</th>
        <th>Network</th>
        <th>Wallet</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($withdrawals as $w): ?>
        <tr>
          <td><?= $w['id'] ?></td>
          <td><?= htmlspecialchars($w['username']) ?></td>
          <td>$<?= number_format($w['amount'], 2) ?></td>
          <td><?= htmlspecialchars($w['network']) ?></td>
          <td><?= htmlspecialchars($w['wallet_address']) ?></td>
          <td class="<?= $w['status'] ?>"><?= strtoupper($w['status']) ?></td>
          <td><?= $w['created_at'] ?></td>
          <td>
            <?php if ($w['status'] === 'pending'): ?>
              <a href="?action=approve&id=<?= $w['id'] ?>" class="btn approve">Approve</a>
              <a href="?action=reject&id=<?= $w['id'] ?>" class="btn reject">Reject</a>
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