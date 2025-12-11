<?php
session_start();
// Database සම්බන්ධතාවය
require_once '../root/config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Publisher') {
    header("Location: ../login.php");
    exit();
}

$publisher_id = $_SESSION['user_id'];
// Session එකෙන් messages සහ කලින් දැමූ form data ලබා ගැනීම
$message = $_SESSION['msg'] ?? '';
$error = $_SESSION['error'] ?? '';
$form_data = $_SESSION['form_data'] ?? [];

// Messages සහ form data ලබා ගත් පසු, ඒවා session එකෙන් ඉවත් කිරීම
unset($_SESSION['msg'], $_SESSION['error'], $_SESSION['form_data']); 


// =================================================================
// 2. ACTIVE STATUS CHECK for Publisher (Upload කිරීමට පෙර පරීක්ෂා කිරීම)
// =================================================================
try {
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
    $stmt->execute([$publisher_id]);
    $is_active = $stmt->fetchColumn();

    if ($is_active === '0') {
        if (empty($error)) {
             $error = "Your Publisher account is currently **INACTIVE** and under review. You cannot upload books until an Admin approves your account.";
        }
        $_SESSION['error'] = $error;
        header("Location: dashboard.php"); 
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "A system error occurred during your account status check.";
    header("Location: ../login.php");
    exit();
}
// =================================================================


// 3. FETCH CATEGORIES FOR DROPDOWN (මෙම කොටසින් Categories load කරයි)
try {
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Categories load කිරීමට දෝෂයක් ආවොත්
    $error = "Error fetching categories. Please ensure the 'categories' table exists in your database.";
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Book - Readify Publisher</title>
    <style>
        /* CSS Styles (පෙර ලබා දුන් style sheet එකම භාවිතා කළ හැක) */
        body { font-family: 'Inter', sans-serif; background-color: #151937; color: #f8fafc; }
        .container { max-width: 800px; margin: 40px auto; background: #1a1f3a; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); }
        h2 { color: #6366f1; border-bottom: 2px solid #1a1f3a; padding-bottom: 10px; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group.full-width { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #cbd5e1; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #333; background: #2a3048; color: #f8fafc; border-radius: 8px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        .btn-submit { background-color: #6366f1; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #4f46e5; }
        .file-upload-box { 
            display: block; padding: 10px; border: 2px dashed #6366f1; border-radius: 8px; 
            text-align: center; cursor: pointer; background: #2a3048; color: #cbd5e1; 
        }
        input[type="file"] { display: none; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background-color: #10b981; color: #064e3b; }
        .alert-error { background-color: #ef4444; color: #7f1d1d; }
        .back-link { display: inline-block; margin-top: 20px; color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload New Ebook</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="upload_book_backend.php" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Book Title *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Author Name *</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($form_data['author'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                <?php echo (isset($form_data['category_id']) && $form_data['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Description *</label>
                    <textarea name="description" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price (LKR) *</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($form_data['price'] ?? '0.00'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Cover Image (JPG/PNG) *</label>
                    <label for="cover_image" class="file-upload-box">
                        <span id="cover-text">Click to Select Cover Image</span>
                    </label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png" required onchange="updateFileName('cover_image', 'cover-text')">
                </div>

                <div class="form-group">
                    <label>Book File (PDF/EPUB) *</label>
                    <label for="book_file" class="file-upload-box">
                        <span id="file-text">Click to Select PDF/EPUB</span>
                    </label>
                    <input type="file" id="book_file" name="book_file" accept=".pdf,.epub" required onchange="updateFileName('book_file', 'file-text')">
                </div>

                <div class="form-group">
                    <label>Visibility</label>
                    <select name="status">
                        <option value="Published" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Published') ? 'selected' : ''; ?>>Publish Immediately</option>
                        <option value="Draft" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Draft') ? 'selected' : ''; ?>>Save as Draft</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">Upload Book</button>
        </form>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <script>
        function updateFileName(inputId, spanId) {
            const input = document.getElementById(inputId);
            const span = document.getElementById(spanId);
            if (input.files && input.files[0]) {
                span.innerText = "✅ " + input.files[0].name;
                span.style.color = "#10b981"; // Success color
            } else {
                span.innerText = (inputId === 'cover_image') ? 'Click to Select Cover Image' : 'Click to Select PDF/EPUB';
                span.style.color = "#cbd5e1"; // Default color
            }
        }
    </script>
</body>
</html>