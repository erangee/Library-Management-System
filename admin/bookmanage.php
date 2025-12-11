<?php
session_start();
require_once '../root/config.php'; // Database connection file

// 1. Security Check: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$msg = $_SESSION['msg'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['msg'], $_SESSION['error']); // Messages clear

// 2. Fetch All Books Data
$allBooks = [];
try {
    // SQL Query to fetch all books with publisher name and category name
    $booksStmt = $pdo->query("
        SELECT 
            e.ebook_id, e.title, e.author, e.price, e.status, 
            u.full_name AS publisher_name, 
            c.category_name
        FROM 
            ebooks e
        JOIN 
            users u ON e.publisher_id = u.user_id
        LEFT JOIN 
            categories c ON e.category_id = c.category_id
        ORDER BY 
            e.created_at DESC
    ");
    $allBooks = $booksStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error fetching books: " . $e->getMessage();
}

// Function to determine badge class for status
function getStatusClass($status) {
    switch ($status) {
        case 'Published':
            return 'status-published';
        case 'Draft':
            return 'status-draft';
        case 'Blocked':
            return 'status-blocked';
        default:
            return 'status-default';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin</title>
    <style>
        /* CSS Styles (Theme එකට ගැළපෙන පරිදි) */
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
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
            background-color: var(--bg-primary);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            color: var(--accent-primary);
            border-bottom: 2px solid var(--bg-card);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Message Styles */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2); /* success with opacity */
            color: var(--success);
            border: 1px solid var(--success);
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.2); /* danger with opacity */
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* Table Styles */
        .book-table {
            width: 100%;
            border-collapse: separate; /* For rounded corners/spacing */
            border-spacing: 0 10px; /* Space between rows */
        }
        .book-table th, .book-table td {
            padding: 15px;
            text-align: left;
            border: none;
        }
        .book-table th {
            background-color: var(--bg-card);
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .book-table tr:first-child th:first-child { border-top-left-radius: 8px; }
        .book-table tr:first-child th:last-child { border-top-right-radius: 8px; }
        .book-table tbody tr {
            background-color: var(--bg-secondary);
            transition: background-color 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
        .book-table tbody tr:hover {
            background-color: #21264b; /* slightly lighter hover effect */
        }
        .book-table tbody td:first-child { border-bottom-left-radius: 8px; border-top-left-radius: 8px; }
        .book-table tbody td:last-child { border-bottom-right-radius: 8px; border-top-right-radius: 8px; }


        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--bg-primary); /* Text color inside badge */
            min-width: 80px;
            text-align: center;
        }
        .status-published { background-color: var(--success); }
        .status-draft { background-color: var(--warning); }
        .status-blocked { background-color: var(--danger); }
        .status-default { background-color: var(--text-muted); }

        /* Action Buttons */
        .action-btns a, .action-btns button {
            background-color: var(--accent-primary);
            color: var(--text-primary);
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-right: 5px;
            transition: background-color 0.2s;
        }
        .action-btns button.btn-delete {
            background-color: var(--danger);
        }
        .action-btns a:hover {
            background-color: #5558d4;
        }
        .action-btns button.btn-delete:hover {
            background-color: #c93030;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--accent-primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage All Books</h2>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($allBooks)): ?>
            <div class="alert alert-error">No books found in the database.</div>
        <?php else: ?>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Publisher</th>
                        <th>Category</th>
                        <th>Price (LKR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBooks as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['ebook_id']); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['category_name'] ?? 'N/A'); ?></td>
                            <td>LKR <?php echo number_format($book['price'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo getStatusClass($book['status']); ?>">
                                    <?php echo htmlspecialchars($book['status']); ?>
                                </span>
                            </td>
                            <td class="action-btns">
                                <a href="edit_book.php?id=<?php echo $book['ebook_id']; ?>">Edit/View</a>
                                
                                <form method="POST" action="manage_book.php" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete the book: <?php echo htmlspecialchars($book['title']); ?>? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="book_id" value="<?php echo $book['ebook_id']; ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Back to Admin Dashboard</a>
    </div>

    </body>
</html>