<?php
session_start();
include '../config/db.php'; // use forward slash for compatibility

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Route based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'project_owner') {
                header("Location: ../projects/index.php");
            } else {
                header("Location: ../dashboard/index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>




<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
   <meta charset="utf-8">
   <title>Login Form</title>
   <link rel="stylesheet" href="../css/style.css">
</head>
<body>
   <div class="wrapper">
      <div class="title">Login Form</div>
      <form action="" method="POST">
         <div class="field">
            <input type="email" name="email" required>
            <label>Email Address</label>
         </div>
         <div class="field">
            <input type="password" name="password" required>
            <label>Password</label>
         </div>
         <div class="content">
            <div class="checkbox">
               <input type="checkbox" id="remember-me">
               <label for="remember-me">Remember me</label>
            </div>
            <div class="pass-link">
               <a href="#">Forgot password?</a>
            </div>
         </div>
         <div class="field">
            <input type="submit" value="Login">
         </div>
         <div class="signup-link">
            Not a member? <a href="register.php">Signup now</a>
         </div>
         <?php if (!empty($error)): ?>
            <p style="color:red; text-align:center; margin-top:10px;"><?php echo $error; ?></p>
         <?php endif; ?>
      </form>
   </div>
</body>
</html>