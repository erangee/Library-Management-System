<?php
session_start();
require_once '../root/config.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $book_id = $_POST['book_id'];

    try {
        if ($action == 'delete') {
            // Delete Book
            $stmt = $pdo->prepare("DELETE FROM ebooks WHERE ebook_id = ?");
            $stmt->execute([$book_id]);
            $_SESSION['msg'] = "Book deleted successfully.";
        } 
        elseif ($action == 'update') {
            // Update Status (e.g., Block a book or Publish it)
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE ebooks SET status=? WHERE ebook_id=?");
            $stmt->execute([$status, $book_id]);
            $_SESSION['msg'] = "Book status updated successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit();
}
?>