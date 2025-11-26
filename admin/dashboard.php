<?php
session_start();
require_once '../root/config.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['full_name'];

// Capture Messages
$msg = $_SESSION['msg'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['msg'], $_SESSION['error']); 

try {
    // Stats
    $userStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_name != 'Admin'");
    $totalUsers = $userStmt->fetchColumn();
    $bookStmt = $pdo->query("SELECT COUNT(*) FROM ebooks");
    $totalBooks = $bookStmt->fetchColumn();
    $subStmt = $pdo->query("SELECT COUNT(*) FROM reader_subscriptions WHERE is_active = 1");
    $activeSubs = $subStmt->fetchColumn();

    // Fetch Users
    $recentUsersStmt = $pdo->query("SELECT * FROM users WHERE role_name != 'Admin' ORDER BY created_at DESC LIMIT 10");
    $recentUsers = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Books
    $recentBooksStmt = $pdo->query("SELECT e.*, u.full_name as publisher_name FROM ebooks e JOIN users u ON e.publisher_id = u.user_id ORDER BY e.created_at DESC LIMIT 10");
    $recentBooks = $recentBooksStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Readify</title>
    <style>
        /* --- KEEP YOUR PREVIOUS CSS --- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        :root { --bg-primary: #0a0e27; --bg-secondary: #151937; --bg-card: #1a1f3a; --accent-primary: #6366f1; --accent-secondary: #8b5cf6; --text-primary: #f8fafc; --text-secondary: #cbd5e1; --text-muted: #64748b; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --sidebar-width: 260px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-primary); color: var(--text-primary); min-height: 100vh; display: flex; }
        
        .sidebar { width: var(--sidebar-width); background: var(--bg-card); border-right: 1px solid rgba(255, 255, 255, 0.05); padding: 30px 20px; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .logo { font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 40px; padding-left: 10px; }
        .nav-links { list-style: none; flex: 1; }
        .nav-links li { margin-bottom: 10px; }
        .nav-links a { display: flex; align-items: center; padding: 12px 16px; color: var(--text-secondary); text-decoration: none; border-radius: 12px; transition: all 0.3s ease; font-weight: 500; }
        .nav-links a:hover, .nav-links a.active { background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); }
        .user-mini-profile { padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; background: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--bg-card); border: 1px solid rgba(255, 255, 255, 0.05); padding: 24px; border-radius: 20px; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

        .table-container { background: var(--bg-card); border-radius: 20px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.05); margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; color: var(--text-secondary); }
        th { text-align: left; padding: 16px; color: var(--text-muted); font-weight: 500; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        td { padding: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        .role-badge, .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .role-reader { background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); }
        .role-publisher { background: rgba(139, 92, 246, 0.1); color: var(--accent-secondary); }
        .status-active { color: var(--success); }
        .status-inactive { color: var(--danger); }

        /* --- NEW ICON BUTTON CSS --- */
        .actions-cell { display: flex; gap: 8px; align-items: center; }
        
        .icon-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            width: 32px; height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }

        .icon-btn svg { width: 16px; height: 16px; fill: currentColor; }

        .btn-edit:hover { background: rgba(99, 102, 241, 0.2); color: var(--accent-primary); border-color: var(--accent-primary); }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.2); color: var(--danger); border-color: var(--danger); }

        /* Modal CSS */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center; }
        .modal-box { background: var(--bg-card); padding: 30px; border-radius: 20px; width: 400px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 0 30px rgba(99, 102, 241, 0.2); animation: popIn 0.3s ease; }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .modal-header h3 { color: var(--text-primary); margin: 0; }
        .close-btn { background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: var(--text-secondary); font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 10px; background: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; }
        .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-update { width: 100%; padding: 12px; background: var(--accent-primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">🛡️ Admin</div>
        <ul class="nav-links">
            <li><a href="#" class="active">📊 Overview</a></li>
            <li><a href="#">👥 Manage Users</a></li>
            <li><a href="#">📚 Manage Books</a></li>
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

        <?php if($msg): ?><div style="padding: 15px; background: rgba(16,185,129,0.1); color: var(--success); border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--success);"><?php echo $msg; ?></div><?php endif; ?>
        <?php if($error): ?><div style="padding: 15px; background: rgba(239,68,68,0.1); color: var(--danger); border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--danger);"><?php echo $error; ?></div><?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div><div style="color: var(--text-muted); font-size: 0.9rem;">Total Users</div><div style="font-weight: 700; font-size: 1.5rem;"><?php echo $totalUsers; ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">📚</div>
                <div><div style="color: var(--text-muted); font-size: 0.9rem;">Total Books</div><div style="font-weight: 700; font-size: 1.5rem;"><?php echo $totalBooks; ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">💎</div>
                <div><div style="color: var(--text-muted); font-size: 0.9rem;">Active Subs</div><div style="font-weight: 700; font-size: 1.5rem;"><?php echo $activeSubs; ?></div></div>
            </div>
        </div>

        <div class="table-container">
            <div class="section-header"><h3>Recent Users</h3></div>
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="role-badge <?php echo ($user['role_name'] == 'Reader') ? 'role-reader' : 'role-publisher'; ?>"><?php echo htmlspecialchars($user['role_name']); ?></span></td>
                        <td><?php echo ($user['is_active']) ? '<span style="color:var(--success)">Active</span>' : '<span style="color:var(--danger)">Banned</span>'; ?></td>
                        <td class="actions-cell">
                            <button class="icon-btn btn-edit" title="Edit User" onclick="openUserModal('<?php echo $user['user_id']; ?>','<?php echo htmlspecialchars($user['full_name']); ?>','<?php echo htmlspecialchars($user['email']); ?>','<?php echo $user['role_name']; ?>','<?php echo $user['is_active']; ?>')">
                                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </button>
                            
                            <form action="manage_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="icon-btn btn-delete" title="Delete User">
                                    <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <div class="section-header"><h3>Recent Books</h3></div>
            <table>
                <thead><tr><th>Title</th><th>Publisher</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($recentBooks as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                        <td><?php echo ($book['price'] > 0) ? '$'.number_format($book['price'], 2) : 'Free'; ?></td>
                        <td><span style="color: <?php echo ($book['status'] == 'Published') ? 'var(--success)' : 'var(--warning)'; ?>"><?php echo htmlspecialchars($book['status']); ?></span></td>
                        <td class="actions-cell">
                            <button class="icon-btn btn-edit" title="Manage Book" onclick="openBookModal('<?php echo $book['ebook_id']; ?>','<?php echo htmlspecialchars($book['title']); ?>','<?php echo htmlspecialchars($book['publisher_name']); ?>','<?php echo $book['status']; ?>')">
                                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </button>

                            <form action="manage_book.php" method="POST" onsubmit="return confirm('Delete this book permanently?');" style="display:inline;">
                                <input type="hidden" name="book_id" value="<?php echo $book['ebook_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="icon-btn btn-delete" title="Delete Book">
                                    <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="modal-overlay" id="userModal">
        <div class="modal-box">
            <div class="modal-header"><h3>Edit User</h3><button class="close-btn" onclick="closeUserModal()">×</button></div>
            <form action="manage_user.php" method="POST">
                <input type="hidden" name="user_id" id="u_id">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" id="u_name" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="u_email" required></div>
                <div class="form-group"><label>Role</label><select name="role" id="u_role"><option value="Reader">Reader</option><option value="Publisher">Publisher</option></select></div>
                <div class="form-group"><label>Status</label><select name="status" id="u_status"><option value="1">Active</option><option value="0">Banned</option></select></div>
                <div class="modal-actions">
                    <button type="submit" name="action" value="update" class="btn-update">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="bookModal">
        <div class="modal-box">
            <div class="modal-header"><h3>Manage Book</h3><button class="close-btn" onclick="closeBookModal()">×</button></div>
            <form action="manage_book.php" method="POST">
                <input type="hidden" name="book_id" id="b_id">
                <div class="form-group"><label>Title</label><input type="text" id="b_title" readonly style="opacity:0.7;"></div>
                <div class="form-group"><label>Publisher</label><input type="text" id="b_pub" readonly style="opacity:0.7;"></div>
                <div class="form-group"><label>Status</label><select name="status" id="b_status"><option value="Published">Published</option><option value="Draft">Draft</option><option value="Blocked">Blocked</option></select></div>
                <div class="modal-actions">
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
            document.getElementById('u_role').value = role;
            document.getElementById('u_status').value = status;
            userModal.style.display = 'flex';
        }

        function openBookModal(id, title, pub, status) {
            document.getElementById('b_id').value = id;
            document.getElementById('b_title').value = title;
            document.getElementById('b_pub').value = pub;
            document.getElementById('b_status').value = status;
            bookModal.style.display = 'flex';
        }

        function closeUserModal() { userModal.style.display = 'none'; }
        function closeBookModal() { bookModal.style.display = 'none'; }

        window.onclick = function(e) {
            if (e.target == userModal) closeUserModal();
            if (e.target == bookModal) closeBookModal();
        }
    </script>
</body>
</html>