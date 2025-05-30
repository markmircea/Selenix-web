<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Selenix Blog</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../components/components.css">
    <link rel="stylesheet" href="../components/navbar/navbar.css">
    <link rel="stylesheet" href="blog-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Navbar Component -->
    <div id="navbar-container"></div>

    <section class="error-page">
        <div class="container">
            <div class="error-content">
                <div class="error-icon">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </div>
                <h1>404 - Page Not Found</h1>
                <p>The page you're looking for doesn't exist or has been moved.</p>
                <div class="error-actions">
                    <a href="blog.php" class="primary-button">
                        <i class="fa-solid fa-home"></i>
                        Back to Blog
                    </a>
                    <a href="../" class="secondary-button">
                        <i class="fa-solid fa-arrow-left"></i>
                        Home Page
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <div id="footer-container"></div>

    <script src="../script.js"></script>
    <script src="../components/components.js"></script>
</body>
</html>
