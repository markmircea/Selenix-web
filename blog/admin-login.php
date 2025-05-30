<?php
require_once 'config.php';
require_once 'functions.php';

// Check if already logged in
if (isAdmin()) {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (loginAdmin($username, $password)) {
        header('Location: admin-dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Selenix Blog</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="blog-styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                    <span class="admin-label">Admin</span>
                </h1>
                <p>Sign in to manage your blog</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fa-solid fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fa-solid fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fa-solid fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <a href="blog.php">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Blog
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on username field
            document.getElementById('username').focus();
            
            // Handle form submission
            const form = document.querySelector('.login-form');
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Signing In...';
                submitBtn.disabled = true;
                
                // Re-enable button after a delay (in case of errors)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            });
        });
    </script>
</body>
</html>
