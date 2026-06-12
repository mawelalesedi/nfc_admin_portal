<?php
require_once 'auth.php';

if (isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $db = new Database();
        $userModel = new User($db);

        // Auto-initialize database if tables are missing
        if (!$db->tableExists('admin_users')) {
            $sqlPath = __DIR__ . '/nfc_patrol_admin.sql';
            try {
                $db->importSqlFile($sqlPath);
                // After import, the script continues naturally
            } catch (Exception $e) {
                $message = 'Critical Error: Database initialization failed. ' . $e->getMessage();
                goto render_login;
            }
        }

        $user = $userModel->getByUsername($username);

        // Hardcoded recovery for default admin credentials
        if ($username === 'admin' && $password === 'admin123') {
            if (!$user) {
                $userModel->create([
                    'username' => 'admin',
                    'email' => 'admin@nfcpatrol.local',
                    'password' => 'admin123',
                    'role' => 'admin',
                    'is_active' => 1,
                ]);
                $user = $userModel->getByUsername('admin');
            } elseif (!$userModel->verifyPassword($password, $user['password_hash'])) {
                // If the credentials match the default recovery but the hash check fails,
                // force update the password hash to a fresh one.
                $userModel->changePassword($user['id'], 'admin123');
                $user = $userModel->getByUsername('admin');
            }
        }

        if ($user) {
            if (!$user['is_active']) {
                $message = 'Your account has been deactivated. Please contact an administrator.';
            } elseif ($userModel->verifyPassword($password, $user['password_hash'])) {
                loginUser($user);
                header('Location: dashboard.php');
                exit;
            } else {
                $message = 'Invalid username or password.';
            }
        } else {
            $message = 'Invalid username or password.';
        }
    }
}

render_login:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            height: 100vh;
            margin: 0;
            padding: 2rem;
            background: radial-gradient(circle at top, rgba(255, 158, 31, 0.18), transparent 28%),
                        linear-gradient(135deg, #0b4d8c 0%, #061b30 100%);
        }
        .login-panel {
            width: 100%;
            max-width: 480px;
            padding: 2.25rem;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 158, 31, 0.5);
            border-radius: 26px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin: 0 auto;
            color: white;
        }
        .login-logo {
            display: block;
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin: 0 auto 1rem auto;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.5rem;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .login-panel h1 {
            margin-bottom: 1rem;
            font-size: 2rem;
            color: #ffffff;
        }
        .login-panel .form-group {
            margin-bottom: 1rem;
        }
        .login-panel label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 600;
        }
        .login-panel input {
            width: 100%;
            padding: 0.95rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 14px;
            font-size: 1rem;
            color: #0f172a;
            background: #ffffff;
        }
        .login-panel input::placeholder {
            color: #64748b;
        }
        .login-panel input:focus {
            border-color: rgba(14, 165, 233, 0.8);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.28);
            outline: none;
        }
        .login-panel button {
            width: 100%;
            padding: 1rem 1rem;
            border: none;
            border-radius: 14px;
            background: #e67e22;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .login-panel button:hover {
            background: #d35400;
            transform: translateY(-2px);
        }
        .alert {
            padding: 0.9rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .alert-danger {
            background: rgba(254, 226, 226, 0.9);
            color: #991b1b;
            border-color: #fecaca;
        }
        .login-footer {
            margin-top: 1rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-panel">
        <img src="<?php echo APP_URL; ?>/izitech.jpg" alt="IziTech logo" class="login-logo">
        <h1>iZi GP Admin</h1>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <div class="login-footer">
            <p>Enter your credentials to access the admin dashboard.</p>
        </div>
    </div>
</body>
</html>
