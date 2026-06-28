<?php
// login.php
require_once 'includes/db.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Find user in DB
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];

                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred during authentication: ' . $e->getMessage();
        }
    }
}

// Get theme from cookie or default to light
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo htmlspecialchars($theme); ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinnacle Accounting & Taxation</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bs-light-subtle);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            max-width: 440px;
            width: 100%;
            padding: 2.5rem;
            background-color: var(--bs-body-bg);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        [data-bs-theme="dark"] .login-card {
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
            color: var(--bs-secondary-color);
        }

        .form-control {
            border-left: none;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--bs-border-color);
            box-shadow: none;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #2563eb;
        }

        [data-bs-theme="dark"] .input-group:focus-within .input-group-text,
        [data-bs-theme="dark"] .input-group:focus-within .form-control {
            border-color: #93c5fd;
        }

        .btn-login {
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            padding: 0.7rem;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            border: none;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        [data-bs-theme="dark"] .btn-login {
            background-color: #3b82f6;
        }

        [data-bs-theme="dark"] .btn-login:hover {
            background-color: #2563eb;
        }

        .brand-logo {
            height: 52px;
            width: 52px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Brand Header -->
    <div class="text-center mb-4">
        <img src="assets/logo.png" alt="Logo" class="brand-logo mb-3 rounded shadow-sm">
        <h1 class="h4 fw-bold mb-1 text-foreground">Pinnacle Accounting<br>& Taxation</h1>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 small py-2 px-3 border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="login.php" method="POST" autocomplete="off">
        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold text-muted mb-1">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label small fw-semibold text-muted mb-1">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-login w-100 mb-2">Sign In</button>
    </form>

    <div class="text-center mt-4">
        <small class="text-muted" style="font-size: 11px;">Pinnacle Office Management Portal</small>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
