<?php
session_start();
require_once 'config.php'; // Ensure this file exists and connects to ebook_db

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) { // We store 'role_name' as 'role' in session
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
            header("Location: index.php");
    }
    exit();
}

$error = '';

// 2. Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            // 3. Select user data matching your ebook_db schema
            $stmt = $pdo->prepare("
                SELECT user_id, full_name, role_name, password_hash, is_active 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Verify User and Password
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // 5. Check if account is active
                if ($user['is_active'] == 0) {
                    $error = "Your account has been deactivated. Please contact support.";
                } else {
                    // 6. Login Success: Set Session Variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role_name']; // Stores: 'Admin', 'Publisher', or 'Reader'

                    // 7. Redirect based on Role
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
                            header("Location: index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "System error. Please try again later.";
            error_log("Login Error: " . $e->getMessage());
        }
    }
}
?>