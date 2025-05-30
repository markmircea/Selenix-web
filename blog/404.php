<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Selenix Blog</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="blog-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar Component -->
    <header>
        <div class="container">
            <nav>
                <a href="../" class="logo">
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                </a>
                <div class="nav-links">
                    <a href="../docs/index.html">Docs</a>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle">Product <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <a href="../product/no-code-builder/index.html">No-code builder</a>
                            <a href="../product/no-code-steps/index.html">No-code steps</a>
                            <a href="../product/bot-runner/index.html">Bot runner</a>
                            <a href="../product/templates/index.html">Templates</a>
                            <a href="../product/video-guides/index.html">Video guides</a>
                            <a href="../product/release-notes/index.html">Release notes</a>
                        </div>
                    </div>
                    <a href="../pricing/index.html">Pricing</a>
                    <a href="../support/index.html">Support</a>
                    <a href="blog.php">Blog</a>
                </div>
                <a href="../download.php" class="cta-button">
                    <i class="fa-solid fa-download"></i>
                    Download Selenix
                </a>
                <div class="mobile-menu-button">
                    <i class="fa-solid fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

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

    <style>
        .error-page {
            padding: 8rem 0;
            text-align: center;
            min-height: 60vh;
            display: flex;
            align-items: center;
        }
        
        .error-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .error-icon {
            font-size: 6rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .error-page h1 {
            font-size: 3rem;
            color: var(--heading-color);
            margin-bottom: 1rem;
        }
        
        .error-page p {
            font-size: 1.25rem;
            color: var(--text-color);
            margin-bottom: 3rem;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .error-page h1 {
                font-size: 2rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>
