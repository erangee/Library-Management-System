<?php
session_start();
// මෙය ඔබගේ database connection file එක වෙත නිවැරදිව යොමු කර ඇති බව තහවුරු කරන්න.
require_once '../root/config.php'; 

// 1. Security Check: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['full_name'] ?? 'Admin User';

// Capture Messages
$msg = $_SESSION['msg'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['msg'], $_SESSION['error']); // Messages clear after displaying

// 2. Fetch Dashboard Statistics
try {
    // Stat: Total Users (Reader/Publisher)
    $userStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_name != 'Admin'");
    $totalUsers = $userStmt->fetchColumn();
    
    // Stat: Total Books (All statuses)
    $bookStmt = $pdo->query("SELECT COUNT(*) FROM ebooks");
    $totalBooks = $bookStmt->fetchColumn();
    
    // Stat: Active Subscriptions
    $subStmt = $pdo->query("SELECT COUNT(*) FROM reader_subscriptions WHERE is_active = 1");
    $activeSubs = $subStmt->fetchColumn();

    // 3. Fetch Recent Users (For Recent Activity Table)
    $recentUsersStmt = $pdo->query("
        SELECT user_id, full_name, email, role_name, is_active, created_at 
        FROM users 
        WHERE role_name != 'Admin' 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentUsers = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Recent Books (For Recent Activity Table)
    $recentBooksStmt = $pdo->query("
        SELECT e.ebook_id, e.title, e.status, u.full_name as publisher_name 
        FROM ebooks e 
        JOIN users u ON e.publisher_id = u.user_id 
        ORDER BY e.created_at DESC 
        LIMIT 5
    ");
    $recentBooks = $recentBooksStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error fetching dashboard data: " . $e->getMessage();
    // Set fallback values
    $totalUsers = 0; $totalBooks = 0; $activeSubs = 0;
    $recentUsers = []; $recentBooks = [];
}

// Helper function to get status class
function getStatusClass($status) {
    if ($status === 1 || $status === 'Published') return 'status-published';
    if ($status === 0 || $status === 'Draft' || $status === 'Banned') return 'status-blocked';
    return 'status-default';
}

function getStatusName($status) {
    if ($status === 1) return 'Active';
    if ($status === 0) return 'Banned';
    return $status;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* CSS Theme - Dark Mode Consistency */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --bg-primary: #0a0e27;
            --bg-secondary: #151937;
            --bg-card: #1a1f3a;
            --accent-primary: #6366f1; /* Purple/Indigo */
            --accent-secondary: #8b5cf6;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --success: #10b981; /* Green */
            --warning: #f59e0b; /* Yellow/Orange */
            --danger: #ef4444; /* Red */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            display: flex; /* Setup for sidebar layout */
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: var(--bg-primary);
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .logo {
            color: var(--accent-primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            flex-grow: 1;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 15px;
            margin-bottom: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            border-radius: 8px;
            transition: background-color 0.2s, color 0.2s;
            font-weight: 500;
        }

        .sidebar ul li a:hover {
            background-color: var(--bg-card);
            color: var(--text-primary);
        }

        .sidebar ul li a.active {
            background-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        /* User Mini Profile */
        .user-mini-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            border-top: 1px solid var(--bg-card);
            margin-top: auto;
        }
        .user-mini-profile .avatar {
            width: 40px;
            height: 40px;
            background-color: var(--accent-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        .user-mini-profile a {
            color: var(--danger);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.2s;
        }
        .user-mini-profile a:hover {
            color: #ff7f7f;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h2 {
            color: var(--text-primary);
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }
        .welcome-text p {
            color: var(--text-muted);
            margin: 5px 0 0 0;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--bg-card);
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            font-size: 2rem;
            margin-right: 20px;
            color: var(--accent-primary);
        }
        .stat-card > div > div:last-child {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        /* Activity Tables */
        .activity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .activity-card {
            background-color: var(--bg-primary);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .activity-card h3 {
            color: var(--accent-primary);
            border-bottom: 1px solid var(--bg-card);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        /* General Table Styling for Activity */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 10px 0;
            text-align: left;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--bg-secondary);
        }
        .data-table th {
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 60px;
            text-align: center;
            color: var(--bg-primary);
        }
        .status-published { background-color: var(--success); }
        .status-blocked, .status-banned { background-color: var(--danger); }
        .status-draft { background-color: var(--warning); color: var(--bg-primary);}
        .status-active { background-color: var(--success); }

        /* Action Buttons */
        .btn-action {
            background-color: var(--accent-secondary);
            color: var(--text-primary);
            border: none;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background-color 0.2s;
        }
        .btn-action:hover {
            background-color: #7b4acf;
        }

        /* Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* Modal Styles (From your previous snippets) */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: var(--bg-primary);
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .modal-content h3 {
            margin-top: 0;
            color: var(--accent-primary);
            border-bottom: 1px solid var(--bg-card);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .modal-content .form-group {
            margin-bottom: 15px;
        }
        .modal-content label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .modal-content input, .modal-content select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--bg-card);
            border-radius: 6px;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            box-sizing: border-box;
        }
        .modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        .modal-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .modal-actions .btn-close {
            background-color: var(--danger);
            color: white;
            margin-right: 10px;
        }
        .modal-actions .btn-update {
            background-color: var(--success);
            color: white;
        }
    </style>
</head>
<body>
    
    <nav class="sidebar">
        <div class="logo">📚 Readify Admin</div>
        
        <ul>
            <li><a href="dashboard.php" class="active">📊 Overview</a></li>
            
            <li><a href="manage_users.php">👥 Manage Users</a></li> 
            
            <li><a href="bookmanage.php">📚 Manage Books</a></li> 
            
            <li><a href="manage_categories.php">📂 Manage Categories</a></li> 
            <li><a href="settings.php">⚙️ Settings</a></li>
        </ul>
        
        <div class="user-mini-profile">
            <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($admin_name); ?></div>
                <a href="../root/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <header class="header">
            <div class="welcome-text">
                <h2>Admin Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($admin_name); ?>. Here is the system overview.</p>
            </div>
        </header>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--accent-primary);">👥</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Total Users</div>
                    <div><?php echo number_format($totalUsers); ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);">📚</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Total Books</div>
                    <div><?php echo number_format($totalBooks); ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--warning);">⭐</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Active Subscriptions</div>
                    <div><?php echo number_format($activeSubs); ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--danger);">🚫</div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Blocked Accounts</div>
                    <?php 
                        try {
                            $bannedStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0 AND role_name != 'Admin'");
                            $bannedUsers = $bannedStmt->fetchColumn();
                        } catch (PDOException $e) { $bannedUsers = 0; }
                    ?>
                    <div><?php echo number_format($bannedUsers); ?></div>
                </div>
            </div>
        </div>
        
        <div class="activity-grid">
            
            <div class="activity-card">
                <h3>Recent User Signups</h3>
                <?php if (!empty($recentUsers)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo getStatusClass($user['is_active']); ?>">
                                            <?php echo getStatusName($user['is_active']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn-action" 
                                           onclick="openUserModal('<?php echo $user['user_id']; ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role_name']); ?>', '<?php echo htmlspecialchars($user['is_active']); ?>'); return false;">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No recent user signups.</p>
                <?php endif; ?>
            </div>
            
            <div class="activity-card">
                <h3>Recent Book Uploads</h3>
                <?php if (!empty($recentBooks)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Publisher</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBooks as $book): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?php echo htmlspecialchars(substr($book['title'], 0, 25)) . (strlen($book['title']) > 25 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo getStatusClass($book['status']); ?>">
                                            <?php echo htmlspecialchars($book['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn-action" 
                                           onclick="openBookModal('<?php echo $book['ebook_id']; ?>', '<?php echo htmlspecialchars($book['title']); ?>', '<?php echo htmlspecialchars($book['publisher_name']); ?>', '<?php echo htmlspecialchars($book['status']); ?>'); return false;">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No recent book uploads.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>
    
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h3>Manage User</h3>
            <form method="POST" action="manage_users.php">
                <input type="hidden" name="user_id" id="u_id">
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="u_name" name="full_name" readonly>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="u_email" name="email" readonly>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="u_role">
                        <option value="Reader">Reader</option>
                        <option value="Publisher">Publisher</option>
                        </select>
                </div>
                
                <div class="form-group">
                    <label>Account Status</label>
                    <select name="status" id="u_status">
                        <option value="1">Active</option>
                        <option value="0">Banned</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeUserModal()" class="btn-close">Close</button>
                    <button type="submit" name="action" value="update" class="btn-update">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <div id="bookModal" class="modal">
        <div class="modal-content">
            <h3>Manage Book Status</h3>
            <form method="POST" action="manage_books.php">
                <input type="hidden" name="book_id" id="b_id">
                
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" id="b_title" readonly>
                </div>
                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" id="b_pub" readonly>
                </div>
                
                <div class="form-group">
                    <label>Visibility Status</label>
                    <select name="status" id="b_status">
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeBookModal()" class="btn-close">Close</button>
                    <button type="submit" name="action" value="update" class="btn-update">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const userModal = document.getElementById('userModal');
        const bookModal = document.getElementById('bookModal');

        function openUserModal(id, name, email, role, status) {
            document.getElementById('u_id').value = id;
            document.getElementById('u_name').value = name;
            document.getElementById('u_email').value = email;
            
            // Set role dropdown value
            document.getElementById('u_role').value = role;
            
            // Set status dropdown value (status is 1 or 0)
            document.getElementById('u_status').value = status; 
            
            userModal.style.display = 'flex';
        }

        function openBookModal(id, title, pub, status) {
            document.getElementById('b_id').value = id;
            document.getElementById('b_title').value = title;
            document.getElementById('b_pub').value = pub;
            
            // Set status dropdown value (status is 'Published', 'Draft', or 'Blocked')
            document.getElementById('b_status').value = status;
            
            bookModal.style.display = 'flex';
        }

        function closeUserModal() { userModal.style.display = 'none'; }
        function closeBookModal() { bookModal.style.display = 'none'; }

        // Close modal when clicking outside
        window.onclick = function(e) {
            if (e.target == userModal) closeUserModal();
            if (e.target == bookModal) closeBookModal();
        }
    </script>
</body>
</html>