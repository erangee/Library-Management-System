<?php
session_start();
require_once '../root/config.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
// ... (rest of the PHP setup code) ...
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

    // Fetch Users: Display all users that are NOT 'Admin'
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
        /* ... (Keep the previous CSS styles, including icon-btn and hover styles) ... */
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
        /* Style for the displayed status text */
        .status-active-text { color: var(--success); }
        .status-banned-text { color: var(--danger); }

        /* Icon Button Styles */
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
        /* Toggle hover styles for visual feedback */
        .btn-toggle-active:hover { background: rgba(16, 185, 129, 0.2) !important; color: var(--success) !important; border-color: var(--success) !important; }
        .btn-toggle-banned:hover { background: rgba(239, 68, 68, 0.2) !important; color: var(--danger) !important; border-color: var(--danger) !important; }

        /* ... (rest of the Modal CSS) ... */
    </style>
</head>
<body>

    <nav class="sidebar">
        </nav>

    <main class="main-content">
        <header class="header">
            </header>

        <?php if($msg): ?><div style="padding: 15px; background: rgba(16,185,129,0.1); color: var(--success); border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--success);"><?php echo $msg; ?></div><?php endif; ?>
        <?php if($error): ?><div style="padding: 15px; background: rgba(239,68,68,0.1); color: var(--danger); border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--danger);"><?php echo $error; ?></div><?php endif; ?>

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
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="status-active-text">Active</span>
                            <?php else: ?>
                                <span class="status-banned-text">Banned</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            
                            <form action="manage_user.php" method="POST" onsubmit="return confirm('Are you sure you want to <?php echo ($user['is_active'] ? 'BAN' : 'ACTIVATE'); ?> this user?');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <button type="submit" 
                                    class="icon-btn <?php echo ($user['is_active'] ? 'btn-toggle-banned' : 'btn-toggle-active'); ?>" 
                                    title="<?php echo ($user['is_active'] ? 'Ban User' : 'Activate User'); ?>" 
                                    style="background: <?php echo ($user['is_active'] ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)'); ?>; color: <?php echo ($user['is_active'] ? 'var(--danger)' : 'var(--success)'); ?>; border-color: <?php echo ($user['is_active'] ? 'var(--danger)' : 'var(--success)'); ?>;">
                                    
                                    <?php if ($user['is_active']): ?>
                                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6h1.9c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 16.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5-1.5.67-1.5 1.5z"/></svg>
                                    <?php endif; ?>
                                </button>
                            </form>
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

        </main>

    </body>
</html>