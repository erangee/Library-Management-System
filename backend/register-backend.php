<?php


// FIX: Pointing correctly to the main folder (one level up)


// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            header("Location: admin/dashboard.php");
            break;
        case 'Publisher':
            header("Location: publisher/dashboard.php");
            break;
        case 'Reader':
            header("Location: reader/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitize input
    $role = trim($_POST['role']); 
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $dob = $_POST['dob']; // <--- Capture Birthday
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $bio = trim($_POST['bio'] ?? '');
    
    // Calculate Age (For validation)
    $age = 0;
    if (!empty($dob)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
    }

    // 2. Validate input
    if (empty($role) || empty($full_name) || empty($email) || empty($password) || empty($dob)) {
        $error = "All required fields must be filled.";
    } elseif (!in_array($role, ['Reader', 'Publisher'])) {
        $error = "Invalid role selected.";
    } elseif ($age < 5) {
        $error = "Please enter a valid date of birth."; 
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // 3. Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email address is already registered.";
            } else {
                // 4. Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // 5. Insert into 'users' table
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        role_name, 
                        email, 
                        password_hash, 
                        full_name, 
                        dob,
                        bio, 
                        is_active, 
                        is_verified, 
                        created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 1, 0, NOW()
                    )
                ");
                
                if ($stmt->execute([$role, $email, $password_hash, $full_name, $dob, $bio])) {
                    $success = "         Registration successful !";
                } else {
                    $error = "Could not register user. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>