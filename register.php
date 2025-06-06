<?php
include '../config/db.php'; // fixed slash direction

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);

        if ($check->rowCount() > 0) {
            $msg = "Email or Username already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $role]);
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        select {
            width: 100%;
            height: 50px;
            border-radius: 25px;
            border: 1px solid lightgrey;
            padding-left: 20px;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title">Signup</div>
        <form method="POST">
            <div class="field">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="field">
                <input type="email" name="email" required>
                <label>Email Address</label>
            </div>
            <div class="field">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>
            <div class="field">
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="user">User (Earn from tasks)</option>
                    <option value="project_owner">Project Owner (Post tasks)</option>
                </select>
            </div>
            <div class="field">
                <input type="submit" value="Signup">
            </div>
            <div class="signup-link">
                Already have an account? <a href="login.php">Login now</a>
            </div>
            <p style="color:red; text-align:center;"><?= $msg ?></p>
        </form>
    </div>
</body>
</html>