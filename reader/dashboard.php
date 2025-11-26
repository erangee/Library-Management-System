<?php
session_start();
require_once '../root/config.php'; // Go up one level to find config

// 1. Security: Check if user is logged in AND is a Reader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Reader') {
    header("Location: ../login.php");
    exit();
}

$reader_id = $_SESSION['user_id'];
$reader_name = $_SESSION['full_name'];

// 2. Fetch Data
try {
    // Get Subscription Status
    $subStmt = $pdo->prepare("
        SELECT p.name, rs.expiry_date 
        FROM reader_subscriptions rs 
        JOIN subscription_plans p ON rs.plan_id = p.plan_id 
        WHERE rs.reader_id = ? AND rs.is_active = 1 AND rs.expiry_date > NOW() 
        LIMIT 1
    ");
    $subStmt->execute([$reader_id]);
    $subscription = $subStmt->fetch(PDO::FETCH_ASSOC);

    // Get Latest Published Books (Joining with users to get Author/Publisher name)
    $bookStmt = $pdo->query("
        SELECT e.*, u.full_name as publisher_name, c.category_name 
        FROM ebooks e 
        JOIN users u ON e.publisher_id = u.user_id 
        JOIN categories c ON e.category_id = c.category_id
        WHERE e.status = 'Published' 
        ORDER BY e.created_at DESC 
        LIMIT 6
    ");
    $books = $bookStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error loading dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Dashboard - Readify</title>
    <style>
        /* --- THEME & VARIABLES (Matching Register/Login) --- */
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
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.15);
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
            z-index: 10;
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
            background: var(--accent-primary);
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
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h2 { font-size: 1.8rem; font-weight: 700; }
        .welcome-text p { color: var(--text-muted); }

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

        /* --- BOOK GRID --- */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 20px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-color: var(--accent-primary);
        }

        .book-cover {
            height: 280px;
            background: #2a3055;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            position: relative;
        }
        
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            color: var(--warning);
            font-weight: 600;
        }

        .book-info {
            padding: 16px;
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .book-author {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .btn-read {
            display: block;
            width: 100%;
            padding: 10px;
            background: var(--accent-primary);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .btn-read:hover { background: var(--accent-secondary); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">📚 Readify</div>
        <ul class="nav-links">
            <li><a href="#" class="active">🏠 Dashboard</a></li>
            <li><a href="#">📖 My Library</a></li>
            <li><a href="#">❤️ Favorites</a></li>
            <li><a href="#">🔍 Browse Categories</a></li>
            <li><a href="#">💳 Subscription</a></li>
        </ul>
        
        <div class="user-mini-profile">
            <div class="avatar"><?php echo strtoupper(substr($reader_name, 0, 1)); ?></div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($reader_name); ?></div>
                <a href="../root/logout.php" style="color: var(--danger); font-size: 0.8rem; padding: 0;">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <header class="header">
            <div class="welcome-text">
                <h2>Hello, <?php echo htmlspecialchars($reader_name); ?>! 👋</h2>
                <p>Ready to jump back into your reading list?</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">💎</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Plan Status</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">
                        <?php echo $subscription ? htmlspecialchars($subscription['name']) : 'Free Plan'; ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Books Read</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">0</div>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h3>Latest Arrivals</h3>
            <a href="#" style="color: var(--accent-primary); text-decoration: none;">View All</a>
        </div>

        <div class="book-grid">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if($book['cover_image_url']): ?>
                                <img src="<?php echo htmlspecialchars($book['cover_image_url']); ?>" alt="Cover">
                            <?php else: ?>
                                <span>No Cover</span>
                            <?php endif; ?>
                            
                            <div class="book-badge">
                                <?php echo ($book['price'] > 0) ? '$'.number_format($book['price'], 2) : 'FREE'; ?>
                            </div>
                        </div>
                        <div class="book-info">
                            <div class="book-title" title="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['publisher_name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--accent-secondary); margin-bottom: 10px;">
                                <?php echo htmlspecialchars($book['category_name']); ?>
                            </div>
                            <a href="#" class="btn-read">Read Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted);">No books published yet.</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>