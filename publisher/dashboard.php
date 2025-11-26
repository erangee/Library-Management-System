<?php
session_start();
require_once '../root/config.php'; // Go up one level to find config

// 1. Security: Check if user is logged in AND is a Publisher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Publisher') {
    header("Location: ../login.php");
    exit();
}

$publisher_id = $_SESSION['user_id'];
$publisher_name = $_SESSION['full_name'];

// 2. Fetch Publisher Data
try {
    // Stat: Total Books Uploaded
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ebooks WHERE publisher_id = ?");
    $countStmt->execute([$publisher_id]);
    $totalBooks = $countStmt->fetchColumn();

    // Stat: Total Reads (joining reading_log with publisher's ebooks)
    $readStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reading_log rl 
        JOIN ebooks e ON rl.ebook_id = e.ebook_id 
        WHERE e.publisher_id = ?
    ");
    $readStmt->execute([$publisher_id]);
    $totalReads = $readStmt->fetchColumn();

    // List: Get My Recent Books
    $booksStmt = $pdo->prepare("
        SELECT e.*, c.category_name 
        FROM ebooks e 
        LEFT JOIN categories c ON e.category_id = c.category_id
        WHERE e.publisher_id = ? 
        ORDER BY e.created_at DESC 
        LIMIT 5
    ");
    $booksStmt->execute([$publisher_id]);
    $myBooks = $booksStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error loading dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publisher Dashboard - Readify</title>
    <style>
        /* --- THEME & VARIABLES (Consistent with Login/Register) --- */
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
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 260px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .nav-links { list-style: none; flex: 1; }
        .nav-links li { margin-bottom: 10px; }
        
        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
        }
        
        .nav-links a i { margin-right: 12px; }

        .user-mini-profile {
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: var(--accent-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 30px 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
            display: inline-block;
        }
        
        .btn-primary:hover { transform: translateY(-2px); }

        /* --- STATS CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* --- DATA TABLE --- */
        .table-container {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-secondary);
        }

        th {
            text-align: left;
            padding: 16px;
            color: var(--text-muted);
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-published { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-draft { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-blocked { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        .book-thumb {
            width: 40px;
            height: 55px;
            background: #2a3055;
            border-radius: 4px;
            object-fit: cover;
        }

    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">📚 Readify</div>
        <ul class="nav-links">
            <li><a href="#" class="active">📊 Dashboard</a></li>
            <li><a href="#">📚 My Books</a></li>
            <li><a href="#">📤 Upload New</a></li>
            <li><a href="#">💰 Earnings</a></li>
            <li><a href="#">⚙️ Settings</a></li>
        </ul>
        
        <div class="user-mini-profile">
            <div class="avatar"><?php echo strtoupper(substr($publisher_name, 0, 1)); ?></div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($publisher_name); ?></div>
                <a href="../root/logout.php" style="color: var(--danger); font-size: 0.8rem;">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <header class="header">
            <div class="welcome-text">
                <h2>Publisher Dashboard</h2>
                <p style="color: var(--text-muted);">Manage your publications and analytics</p>
            </div>
            <a href="upload_book.php" class="btn-primary">+ Upload Book</a>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Total Books</div>
                    <div style="font-weight: 700; font-size: 1.5rem;"><?php echo $totalBooks; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat