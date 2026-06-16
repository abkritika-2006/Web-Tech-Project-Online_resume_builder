<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already registered
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);

            $userId = $pdo->lastInsertId();
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $name;
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account — ResumeForge</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-box">

    <div class="auth-logo">Resume<span>Forge</span></div>
    <p class="auth-sub">Create your free account and start building.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="card" novalidate>

      <div class="form-group">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               placeholder="Jane Doe" required autofocus />
      </div>

      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="you@example.com" required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="At least 8 characters" required />
        <p class="form-hint">Minimum 8 characters.</p>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm"
               placeholder="Repeat password" required />
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">
        Create account &rarr;
      </button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a>
    </p>
  </div>
</div>
<script src="js/auth.js"></script>
</body>
</html>
