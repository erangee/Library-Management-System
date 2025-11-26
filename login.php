<?php
session_start();
require_once 'root/config.php'; // Database connection file

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            header("Location: admin/dashboard.php");
            break;
        case 'Publisher':
            header("Location: publisher/dashboard.php");
            break;
        case 'Reader':
            header("Location: reader/dashboard.php");
            break;
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            // Prepare statement to fetch user
            // Note: matching columns from your register.php (role_name, password_hash)
            $stmt = $pdo->prepare("SELECT user_id, full_name, role_name, password_hash, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                
                if ($user['is_active'] == 0) {
                    $error = "Your account has been deactivated.";
                } else {
                    // Password correct, start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role_name'];

                    // Redirect based on role
                    switch ($user['role_name']) {
                        case 'Admin':
                            header("Location: admin/dashboard.php");
                            break;
                        case 'Publisher':
                            header("Location: publisher/dashboard.php");
                            break;
                        case 'Reader':
                            header("Location: reader/dashboard.php");
                            break;
                        default:
                            // Fallback
                            header("Location: index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again later.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Readify</title>
    <style>
        /* Exact styles from register.php */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --bg-primary: #0a0e27;
            --bg-secondary: #151937;
            --bg-card: #1a1f3a;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
            animation: drift 20s ease-in-out infinite alternate;
        }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, 50px); }
        }
        
        .login-container {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 450px; /* Slightly narrower than register for better aesthetics */
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: var(--shadow-glow);
            position: relative;
            z-index: 1;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .logo p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .required {
            color: var(--danger);
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        
        .register-link a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: var(--accent-secondary);
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-home a:hover {
            color: var(--text-primary);
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 24px;
            }
            .logo h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="login-container">
        <div class="logo">
            <h1>📚 Readify</h1>
            <p>Your Digital Bookshelf</p>
        </div>
        
        <h2>Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>

        <div class="back-home">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>