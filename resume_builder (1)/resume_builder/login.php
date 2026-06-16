<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — ResumeForge</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-box">

    <div class="auth-logo">Resume<span>Forge</span></div>
    <p class="auth-sub">Sign in to continue building your resume.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="card" novalidate>
      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="you@example.com" required autofocus />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" required />
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">
        Sign in &rarr;
      </button>
    </form>

    <p class="auth-footer">
      Don't have an account? <a href="register.php">Create one free</a>
    </p>
  </div>
</div>
</body>
</html>
