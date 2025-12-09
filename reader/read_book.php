<?php
session_start();
require_once '../root/config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Reader') {
    header("Location: ../login.php");
    exit();
}

// 2. Validate Book ID
if (!isset($_GET['id'])) {
    die("No book selected.");
}

$reader_id = $_SESSION['user_id'];
$ebook_id = $_GET['id'];

try {
    // 3. Fetch Book Details (File Path)
    $stmt = $pdo->prepare("SELECT title, file_link FROM ebooks WHERE ebook_id = ? AND status = 'Published'");
    $stmt->execute([$ebook_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        die("Book not found or not available.");
    }

    // 4. Update Reading Log (The "Logic" part)
    // Check if they have read this before
    $checkLog = $pdo->prepare("SELECT log_id FROM reading_log WHERE reader_id = ? AND ebook_id = ?");
    $checkLog->execute([$reader_id, $ebook_id]);

    if ($checkLog->rowCount() == 0) {
        // First time reading? Insert new log
        $logStmt = $pdo->prepare("INSERT INTO reading_log (reader_id, ebook_id, start_time) VALUES (?, ?, NOW())");
        $logStmt->execute([$reader_id, $ebook_id]);
    } else {
        // Already started? Update the 'start_time' or just let it be (Optional: You could update 'last_accessed')
        // For this assignment, we just ensure a log exists.
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading: <?php echo htmlspecialchars($book['title']); ?></title>
    <style>
        body { margin: 0; padding: 0; background-color: #0a0e27; overflow: hidden; }
        .toolbar {
            height: 50px;
            background: #151937;
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: white;
            font-family: sans-serif;
            justify-content: space-between;
        }
        .back-btn {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover { color: #6366f1; }
        iframe {
            width: 100vw;
            height: calc(100vh - 50px);
            border: none;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="dashboard.php" class="back-btn">← Back to Library</a>
        <span><?php echo htmlspecialchars($book['title']); ?></span>
        <span></span>
    </div>

    <iframe src="<?php echo htmlspecialchars($book['file_link']); ?>#toolbar=0">
    </iframe>
</body>
</html>