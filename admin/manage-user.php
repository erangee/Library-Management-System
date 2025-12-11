<?php
session_start();
// මෙම path එක ඔබේ ගොනු ව්‍යුහය අනුව වෙනස් කිරීමට සිදු විය හැක.
// ඔබගේ ගොනු ව්‍යුහය අනුව, මෙය නිවැරදි යැයි උපකල්පනය කර ඇත:
require_once '../root/config.php'; 

// 1. Security Check: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? null;

    // 2. Critical Check: Is User ID received?
    if ($action == 'toggle_status' && !$user_id) {
        $_SESSION['error'] = "Error: User ID is missing for the Toggle action. (Form not submitting correctly)";
        header("Location: dashboard.php");
        exit();
    }

    try {
        // --- TOGGLE STATUS LOGIC ---
        if ($action == 'toggle_status') {

            echo "<h1>DEBUGGING: Toggle Status Action Detected!</h1>";
    echo "User ID received: " . htmlspecialchars($user_id) . "<br>";

            // වත්මන් තත්ත්වය ලබා ගැනීම
            // මෙතැනදී $pdo variable එක undefined නම්, Fatal error එකක් ලැබේ.
            $currentStmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
            $currentStmt->execute([$user_id]);
            $current_status = $currentStmt->fetchColumn();

            if ($current_status === false) {
                 $_SESSION['error'] = "Error: User not found in database (ID: " . $user_id . ").";
                 echo "ERROR: User ID: " . $user_id . " not found in the 'users' table.";
         exit(); // Stop execution
            } else {
                echo "Current DB Status: " . htmlspecialchars($current_status) . " (1 = Active, 0 = Banned)<br>";
                // තත්ත්වය මාරු කිරීම (Toggle)
                $new_status = ((int)$current_status == 1) ? 0 : 1;
                
                // Database එකේ යාවත්කාලීන කිරීම
                $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
                $stmt->execute([$new_status, $user_id]);
                
                $status_text = $new_status == 1 ? 'Activated' : 'Banned';
                $_SESSION['msg'] = "User status successfully **" . $status_text . "**.";
            }
        }
        // ... (Other actions: delete, update)

    } catch (PDOException $e) {
        // 3. Database Error Check: If DB connection is wrong or query fails
        $_SESSION['error'] = "Database Error: Cannot complete action. Check DB credentials. Details: " . $e->getMessage();
        // error_log("Database Error in manage_user.php: " . $e->getMessage()); // Check your server log
    }
    
    // Redirect back to Dashboard
    header("Location: dashboard.php");
    exit();
}
?>