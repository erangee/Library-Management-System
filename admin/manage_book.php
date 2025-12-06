<?php
session_start();
require_once '../root/config.php'; // Adjust path if needed

// 1. Security Check: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $book_id = $_POST['book_id'];

    try {
        if ($action == 'delete') {
            // DELETE BOOK
            // First, delete the physical files (Optional but recommended)
            $fileStmt = $pdo->prepare("SELECT cover_image_url, file_link FROM ebooks WHERE ebook_id = ?");
            $fileStmt->execute([$book_id]);
            $files = $fileStmt->fetch(PDO::FETCH_ASSOC);

            if ($files) {
                // Delete cover image if it exists
                if (!empty($files['cover_image_url']) && file_exists("../" . $files['cover_image_url'])) {
                    unlink("../" . $files['cover_image_url']);
                }
                // Delete PDF/EPUB if it exists
                if (!empty($files['file_link']) && file_exists("../" . $files['file_link'])) {
                    unlink("../" . $files['file_link']);
                }
            }

            // Now delete from Database
            $stmt = $pdo->prepare("DELETE FROM ebooks WHERE ebook_id = ?");
            $stmt->execute([$book_id]);
            $_SESSION['msg'] = "Book deleted successfully.";
        } 
        elseif ($action == 'update') {
            // UPDATE BOOK STATUS
            $status = $_POST['status']; // Published, Draft, or Blocked
            
            $stmt = $pdo->prepare("UPDATE ebooks SET status=? WHERE ebook_id=?");
            $stmt->execute([$status, $book_id]);
            $_SESSION['msg'] = "Book status updated successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
    
    // Redirect back to Dashboard
    header("Location: dashboard.php");
    exit();
}
?>