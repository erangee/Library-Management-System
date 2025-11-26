<?php
session_start();
require_once 'root/config.php'; // 1. Connect to Database

// 2. Security: Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'backend/register-backend.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Readify</title>
    <style>
        /* Exact styles from your previous file */
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
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .bg-animation {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
        }
        .bg-animation::before {
            content: ''; position: absolute; width: 200%; height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
            animation: drift 20s ease-in-out infinite alternate;
        }
        @keyframes drift { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-50px, 50px); } }
        
        .register-container {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: var(--shadow-glow);
        }
        
        h2 { text-align: center; margin-bottom: 20px; font-size: 1.8rem; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { font-size: 2.2rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem; }
        .required { color: var(--danger); }
        
        input, select, textarea {
            width: 100%; padding: 12px;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit;
        }
        input:focus { outline: none; border-color: var(--accent-primary); }
        
        .btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 12px;
            font-weight: 600; cursor: pointer; margin-top: 10px;
        }
        .btn:hover { transform: translateY(-2px); }
        
        .alert { padding: 14px; border-radius: 12px; margin-bottom: 20px;text-align: center; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
        
        .login-link { text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 0.9rem; }
        .login-link a { color: var(--accent-primary); text-decoration: none; }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="register-container">
        <div class="logo">
            <h1>📚 Readify</h1>
            <p>Your Digital Bookshelf</p>
        </div>
        
        <h2>Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Register as <span class="required">*</span></label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Reader" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Reader') ? 'selected' : ''; ?>>Reader</option>
                    <option value="Publisher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Publisher') ? 'selected' : ''; ?>>Publisher</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Date of Birth <span class="required">*</span></label>
                <input type="date" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
                <small style="color: var(--text-muted); font-size: 0.8rem;">Used to personalize your book recommendations.</small>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" placeholder="Min. 8 characters" required>
            </div>
            
            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label>Bio (Optional)</label>
                <textarea name="bio"><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        <?php endif; ?>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>