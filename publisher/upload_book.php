<?php
session_start();
require_once '../root/config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Publisher') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$error = '';

// 2. Fetch Categories for Dropdown
try {
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching categories.";
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $status = $_POST['status'];
    $publisher_id = $_SESSION['user_id'];

    // File Upload Logic
    $coverDir = "../uploads/covers/";
    $fileDir = "../uploads/files/";
    
    // Ensure directories exist
    if (!file_exists($coverDir)) mkdir($coverDir, 0777, true);
    if (!file_exists($fileDir)) mkdir($fileDir, 0777, true);

    $uploadOk = true;

    // A. Handle Cover Image
    $coverName = basename($_FILES["cover_image"]["name"]);
    $coverTarget = $coverDir . uniqid() . "_" . $coverName;
    $imageFileType = strtolower(pathinfo($coverTarget, PATHINFO_EXTENSION));
    
    // B. Handle Book File (PDF/EPUB)
    $fileName = basename($_FILES["book_file"]["name"]);
    $fileTarget = $fileDir . uniqid() . "_" . $fileName;
    $bookFileType = strtolower(pathinfo($fileTarget, PATHINFO_EXTENSION));

    // Validations
    if (empty($title) || empty($author) || empty($category_id) || empty($fileName)) {
        $error = "Please fill in all required fields and upload a book file.";
        $uploadOk = false;
    } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'webp'])) {
        $error = "Cover image must be JPG, PNG, or WEBP.";
        $uploadOk = false;
    } elseif (!in_array($bookFileType, ['pdf', 'epub'])) {
        $error = "Book file must be PDF or EPUB.";
        $uploadOk = false;
    }

    if ($uploadOk) {
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $coverTarget) && 
            move_uploaded_file($_FILES["book_file"]["tmp_name"], $fileTarget)) {
            
            try {
                // Insert into Database
                $sql = "INSERT INTO ebooks (publisher_id, category_id, title, author, description, cover_image_url, file_link, price, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $publisher_id, 
                    $category_id, 
                    $title, 
                    $author, 
                    $description, 
                    $coverTarget, 
                    $fileTarget, 
                    $price, 
                    $status
                ]);

                // Redirect to Dashboard on success
                header("Location: dashboard.php");
                exit();

            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        } else {
            $error = "Sorry, there was an error uploading your files.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Book - Readify</title>
    <style>
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
            --danger: #ef4444;
            --success: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .upload-container {
            background: var(--bg-card);
            max-width: 800px;
            width: 100%;
            padding: 40px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.15);
        }

        h2 { margin-bottom: 30px; font-size: 1.8rem; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 24px; }
        .full-width { grid-column: span 2; }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit;
        }

        textarea { height: 120px; resize: vertical; }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        .file-upload-box {
            border: 2px dashed rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-box:hover {
            border-color: var(--accent-primary);
            background: rgba(99, 102, 241, 0.05);
        }

        input[type="file"] { display: none; }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            text-decoration: none;
        }
        
        .alert {
            padding: 15px;
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="upload-container">
        <h2>📤 Upload New Book</h2>

        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" required placeholder="e.g. The Cosmic Journey">
                </div>

                <div class="form-group">
                    <label>Author Name *</label>
                    <input type="text" name="author" required placeholder="e.g. John Doe">
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price (USD) - 0 for Free</label>
                    <input type="number" name="price" step="0.01" min="0" value="0.00" required>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" placeholder="What is this book about?"></textarea>
                </div>

                <div class="form-group">
                    <label>Cover Image (JPG/PNG) *</label>
                    <label for="cover_image" class="file-upload-box">
                        <span id="cover-text">Click to Select Image</span>
                    </label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" required onchange="updateFileName('cover_image', 'cover-text')">
                </div>

                <div class="form-group">
                    <label>Book File (PDF/EPUB) *</label>
                    <label for="book_file" class="file-upload-box">
                        <span id="file-text">Click to Select PDF/EPUB</span>
                    </label>
                    <input type="file" id="book_file" name="book_file" accept=".pdf,.epub" required onchange="updateFileName('book_file', 'file-text')">
                </div>

                <div class="form-group full-width">
                    <label>Visibility</label>
                    <select name="status">
                        <option value="Published">Publish Immediately</option>
                        <option value="Draft">Save as Draft</option>
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
                span.style.color = "var(--success)";
            }
        }
    </script>
</body>
</html>