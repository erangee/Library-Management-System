<?php
session_start();
require_once '../root/config.php'; 

// 1. Security Check (Publisher Role)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Publisher') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../login.php");
    exit();
}

$publisher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: upload_book.php"); 
    exit();
}

$error = '';

// =================================================================
// 2. ACTIVE STATUS CHECK for Publisher 
// (Inactive නම්, Upload කිරීමට ඉඩ නොදේ)
// =================================================================
try {
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
    $stmt->execute([$publisher_id]);
    $is_active = $stmt->fetchColumn();

    if ($is_active === '0') {
        $_SESSION['error'] = "Your Publisher account is currently **INACTIVE** and under review. You cannot upload books until an Admin approves your account.";
        header("Location: dashboard.php"); 
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "A system error occurred during your account status check.";
    header("Location: upload_book.php");
    exit();
}
// =================================================================

// 3. Sanitize and Validate Input
$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category_id = $_POST['category_id'] ?? null; // CATEGORY ID ලබා ගැනීම
$description = trim($_POST['description'] ?? '');
$price = $_POST['price'] ?? 0;
$status = $_POST['status'] ?? 'Draft';

// Basic Validation
if (empty($title) || empty($author) || empty($category_id) || empty($description)) {
    $error = "All required text fields must be filled.";
} elseif (!is_numeric($category_id) || $category_id <= 0) { // CATEGORY ID Validation
    $error = "Please select a valid category.";
} elseif (!is_numeric($price) || $price < 0) {
    $error = "Invalid price value.";
} elseif (empty($_FILES['cover_image']['name']) || empty($_FILES['book_file']['name'])) {
    $error = "Both cover image and book file are required.";
}

// දෝෂයක් ඇත්නම්, form data සහ error message එක සමඟ ආපසු යවන්න
if (!empty($error)) {
    $_SESSION['error'] = $error;
    $_SESSION['form_data'] = $_POST;
    header("Location: upload_book.php");
    exit();
}

// 4. File Upload Logic
$coverDir = "../uploads/covers/";
$fileDir = "../uploads/files/";

if (!file_exists($coverDir)) mkdir($coverDir, 0777, true);
if (!file_exists($fileDir)) mkdir($fileDir, 0777, true);

$cover_image_url = '';
$file_link = '';

try {
    // --- Cover Image Upload ---
    $cover_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($cover_ext), ['jpg', 'jpeg', 'png'])) {
         throw new Exception("Invalid cover image format. Only JPG/PNG allowed.");
    }
    $cover_file_name = uniqid('cover_') . '.' . $cover_ext;
    $cover_target = $coverDir . $cover_file_name;
    
    if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_target)) {
        throw new Exception("Failed to upload cover image. Check folder permissions.");
    }
    $cover_image_url = substr($cover_target, 3); // Path relative to the root

    // --- Book File Upload ---
    $book_ext = pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($book_ext), ['pdf', 'epub'])) {
         throw new Exception("Invalid book file format. Only PDF/EPUB allowed.");
    }
    $book_file_name = uniqid('book_') . '.' . $book_ext;
    $book_target = $fileDir . $book_file_name;

    if (!move_uploaded_file($_FILES['book_file']['tmp_name'], $book_target)) {
         throw new Exception("Failed to upload book file. Check folder permissions.");
    }
    $file_link = substr($book_target, 3); // Path relative to the root

    // 5. DATABASE INSERTION (Category ID එක ඇතුළත් කිරීම)
    $stmt = $pdo->prepare("
        INSERT INTO ebooks (
            publisher_id, category_id, title, author, description, price, 
            cover_image_url, file_link, status, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, NOW()
        )
    ");
    
    if ($stmt->execute([
        $publisher_id, $category_id, $title, $author, $description, $price, 
        $cover_image_url, $file_link, $status
    ])) {
        $_SESSION['msg'] = "Book '**" . htmlspecialchars($title) . "**' uploaded successfully and saved as **" . htmlspecialchars($status) . "**!";
        
    } else {
        throw new Exception("Database insertion failed. Please try again.");
    }

} catch (Exception $e) {
    $error = "Upload Error: " . $e->getMessage();
    error_log("Ebook Upload Error: " . $e->getMessage());
    
    // දෝෂයක් ඇත්නම් uploaded files මකා දැමීම
    if (isset($cover_target) && file_exists($cover_target)) unlink($cover_target);
    if (isset($book_target) && file_exists($book_target)) unlink($book_target);

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
    error_log("PDO Ebook Upload Error: " . $e->getMessage());
}

// 6. Redirect: සාර්ථක නම් Dashboard එකට, නැතිනම් Error message එක සමඟ Form එකට
if (!empty($error)) {
    $_SESSION['error'] = $error;
    $_SESSION['form_data'] = $_POST; 
    header("Location: upload_book.php");
} else {
    header("Location: dashboard.php");
}
exit();
?>