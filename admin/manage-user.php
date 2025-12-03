<?php
session_start();
require_once '../root/config.php'; // Adjust path if needed

// 1. Security Check: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];

    try {
        if ($action == 'delete') {
            // DELETE USER
            // Note: If you have foreign key constraints (like user has books), 
            // you might need to delete those first or use ON DELETE CASCADE in SQL.
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['msg'] = "User deleted successfully.";
        } 
        elseif ($action == 'update') {
            // UPDATE USER
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $status = $_POST['status']; // 1 = Active, 0 = Banned

            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role_name=?, is_active=? WHERE user_id=?");
            $stmt->execute([$full_name, $email, $role, $status, $user_id]);
            $_SESSION['msg'] = "User updated successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
    
    // Redirect back to Dashboard
    header("Location: dashboard.php");
    exit();
}
?>