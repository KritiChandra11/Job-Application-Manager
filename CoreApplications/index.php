<?php
require_once 'config.php';
require_once 'models/User.php';
require_once 'models/Event.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Check if the user is logged in for protected pages
$protected_pages = ['dashboard', 'portals', 'calendar', 'analytics', 'documents', 'interview_notes', 'network'];
if (in_array($page, $protected_pages) && !isLoggedIn()) {
    redirect('index.php?page=login');
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'register') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Form validation
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'All fields are required'];
        } elseif ($password !== $confirm_password) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Passwords do not match'];
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Username already exists'];
                } else {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email already registered'];
                }
            } else {
                // Create new user
                if (User::create($username, $email, $password)) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration successful. Please login.'];
                    redirect('index.php?page=login');
                } else {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Registration failed'];
                }
            }
        }
    } elseif ($page === 'login') {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $remember_me = isset($_POST['remember_me']);
        
        $user = User::getByEmail($email);
        
        if ($user && User::checkPassword($user, $password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if ($remember_me) {
                // Set a cookie that expires in 30 days
                setcookie('remember_user', $user['id'], time() + (86400 * 30), "/");
            }
            
            redirect('index.php?page=dashboard');
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid email or password'];
        }
    }
}

// Handle GET requests
if ($page === 'logout') {
    session_destroy();
    setcookie('remember_user', '', time() - 3600, "/"); // Delete the cookie
    redirect('index.php');
}

// Include the appropriate view
$template = 'templates/' . $page . '.php';
if (file_exists($template)) {
    include 'templates/header.php';
    include $template;
    include 'templates/footer.php';
} else {
    include 'templates/header.php';
    echo "<div class='container mt-5'><h1>404 Page Not Found</h1></div>";
    include 'templates/footer.php';
}
?>
