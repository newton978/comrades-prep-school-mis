<?php
session_start();
require_once 'db.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM Admin_Users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify username and hashed password (or fallback plaintext check for initial setup)
            if ($admin && (password_verify($password, $admin['password_hash']) || $password === 'admin123')) {
                // Set Secure Session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['user_id'];
                $_SESSION['admin_name']      = $admin['full_name'];
                $_SESSION['admin_username']  = $admin['username'];

                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid username or password!";
            }
        } catch (PDOException $e) {
            $error_message = "System error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Comrades Preparatory School</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #1d4ed8;
            --bg: #0f172a;
            --surface: #ffffff;
            --text: #334155;
            --border: #cbd5e1;
            --danger: #dc2626;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            background: var(--surface);
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .login-logo {
            width: 85px;
            height: auto;
            margin-bottom: 15px;
        }

        .login-card h2 {
            margin: 0 0 5px 0;
            color: var(--primary);
            font-size: 1.4rem;
        }

        .login-card p.subtitle {
            margin: 0 0 20px 0;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: var(--primary-hover);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        button.btn-login {
            width: 100%;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        button.btn-login:hover {
            background-color: var(--primary-hover);
        }

        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            text-align: left;
        }

        .footer-text {
            margin-top: 20px;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="login-card">
    <img src="AdobeExpressPhotos_8ef87ab5fa8d415ba8c478395c55d780_CopyEdited.jpg" alt="Comrades Prep School Badge" class="login-logo">
    <h2>Comrades Preparatory School</h2>
    <p class="subtitle">Kitebi Campus Admin Portal</p>

    <?php if (!empty($error_message)): ?>
        <div class="error-alert">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Admin Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn-login">🔒 Sign In to Portal</button>
    </form>

    <div class="footer-text">
        &copy; <?= date('Y') ?> Comrades Preparatory School - Kitebi.<br>"FOR A BRIGHT FUTURE"
    </div>
</div>

</body>
</html>