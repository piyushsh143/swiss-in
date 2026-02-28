<?php
/**
 * Admin login page - /admin or /admin/
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $pdo = getDb();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login - Travisa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #0d6efd; --dark: #1a1a2e; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); overflow: hidden; max-width: 400px; }
        .login-header { background: linear-gradient(135deg, var(--primary), #0a58ca); color: #fff; padding: 2rem; text-align: center; }
        .login-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .login-body { padding: 2rem; }
        .btn-login { background: var(--primary); border: none; padding: 12px; font-weight: 600; }
        .btn-login:hover { background: #0a58ca; color: #fff; }
    </style>
</head>
<body>
    <div class="login-card w-100 mx-3">
        <div class="login-header">
            <h1>Travisa Admin</h1>
            <p class="mb-0 opacity-90">Sign in to manage content</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">Login</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
