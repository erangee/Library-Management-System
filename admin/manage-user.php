<?php
session_start();
require_once '../root/config.php'; // Matches your folder structure

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];

    try {
        if ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['msg'] = "User deleted successfully.";
        } elseif ($action == 'update') {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role_name=?, is_active=? WHERE user_id=?");
            $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['role'], $_POST['status'], $user_id]);
            $_SESSION['msg'] = "User updated successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: dashboard.php");
    exit();
}
?>