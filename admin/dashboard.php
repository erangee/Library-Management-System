<?php
session_start();

// PATH CORRECTION: 
// We go up one level (..) to the main folder, then into the 'root' folder to find config.php
require_once '../root/config.php'; 

// 1. Security: Check if user is logged in AND is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    // Login page is in the main folder (up one level)
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['full_name'];

// 2. Fetch Admin Stats
try {
    // Stat: Total Users (Excluding Admins)
    $userStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_name != 'Admin'");
    $totalUsers = $userStmt->fetchColumn();

    // Stat: Total Books
    $bookStmt = $pdo->query("SELECT COUNT(*) FROM ebooks");
    $totalBooks = $bookStmt->fetchColumn();

    // Stat: Active Subscriptions
    $subStmt = $pdo->query("SELECT COUNT(*) FROM reader_subscriptions WHERE is_active = 1");
    $activeSubs = $subStmt->fetchColumn();

    // List: Recent Users (Newest 5)
    $recentUsersStmt = $pdo->query("SELECT * FROM users WHERE role_name != 'Admin' ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);

    // List: Recent Books (Newest 5)
    $recentBooksStmt = $pdo->query("
        SELECT e.*, u.full_name as publisher_name 
        FROM ebooks e 
        JOIN users u ON e.publisher_id = u.user_id 
        ORDER BY e.created_at DESC 
        LIMIT 5
    ");
    $recentBooks = $recentBooksStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error loading dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Readify</title>
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
            background: var(--danger); /* Red for Admin */
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

        /* --- TABLES --- */
        .table-container {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table { width: 100%; border-collapse: collapse; color: var(--text-secondary); }
        th { text-align: left; padding: 16px; color: var(--text-muted); font-weight: 500; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        td { padding: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .role-badge, .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-reader { background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); }
        .role-publisher { background: rgba(139, 92, 246, 0.1); color: var(--accent-secondary); }

        .status-active { color: var(--success); }
        .status-inactive { color: var(--danger); }

        .action-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .action-btn:hover { background: var(--bg-secondary); color: white; }

    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">🛡️ Admin</div>
        <ul class="nav-links">
            <li><a href="#" class="active">📊 Overview</a></li>
            <li><a href="#">👥 Manage Users</a></li>
            <li><a href="#">📚 Manage Books</a></li>
            <li><a href="#">🏷️ Categories</a></li>
            <li><a href="#">⚙️ Settings</a></li>
        </ul>
        
        <div class="user-mini-profile">
            <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($admin_name); ?></div>
                <a href="../root/logout.php" style="color: var(--danger); font-size: 0.8rem;">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <header class="header">
            <div class="welcome-text">
                <h2>Admin Control Panel</h2>
                <p style="color: var(--text-muted);">System Overview & Management</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Total Users</div>
                    <div style="font-weight: 700; font-size: 1.5rem;"><?php echo $totalUsers; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">📚</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Total Books</div>
                    <div style="font-weight: 700; font-size: 1.5rem;"><?php echo $totalBooks; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">💎</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Active Subs</div>
                    <div style="font-weight: 700; font-size: 1.5rem;"><?php echo $activeSubs; ?></div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="section-header">
                <h3>Recent Registrations</h3>
                <a href="#" style="color: var(--accent-primary); text-decoration: none; font-size: 0.9rem;">View All Users</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge <?php echo ($user['role_name'] == 'Reader') ? 'role-reader' : 'role-publisher'; ?>">
                                <?php echo htmlspecialchars($user['role_name']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?php echo ($user['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ($user['is_active']) ? '● Active' : '● Banned'; ?>
                            </span>
                        </td>
                        <td style="font-size: 0.9rem; color: var(--text-muted);"><?php echo date('M d', strtotime($user['created_at'])); ?></td>
                        <td><button class="action-btn">Edit</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <div class="section-header">
                <h3>Recently Uploaded Books</h3>
                <a href="#" style="color: var(--accent-primary); text-decoration: none; font-size: 0.9rem;">View All Books</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Publisher</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBooks as $book): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                        <td><?php echo ($book['price'] > 0) ? '$'.number_format($book['price'], 2) : 'Free'; ?></td>
                        <td>
                            <span style="color: <?php echo ($book['status'] == 'Published') ? 'var(--success)' : 'var(--warning)'; ?>">
                                <?php echo htmlspecialchars($book['status']); ?>
                            </span>
                        </td>
                        <td><button class="action-btn">Manage</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>