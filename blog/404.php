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
    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
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
                <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>
                <div class="error-actions">
                    <a href="blog.php" class="primary-button">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Blog
                    </a>
                    <a href="../" class="secondary-button">
                        <i class="fa-solid fa-home"></i>
                        Go Home
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>selenix.io</h3>
                    <p class="footer-description">Modern browser automation tool that helps you save time and increase productivity without writing a single line of code.</p>
                    <div class="social-links">
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin"></i></a>
                        <a href="#"><i class="fa-brands fa-github"></i></a>
                        <a href="#"><i class="fa-brands fa-discord"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Product</h3>
                    <ul>
                        <li><a href="../docs/index.html"><i class="fa-solid fa-file-lines"></i> Documentation</a></li>
                        <li><a href="../product/no-code-builder/index.html"><i class="fa-solid fa-cubes"></i> No-code builder</a></li>
                        <li><a href="../product/no-code-steps/index.html"><i class="fa-solid fa-list-check"></i> No-code steps</a></li>
                        <li><a href="../pricing/index.html"><i class="fa-solid fa-tag"></i> Pricing</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Web automation</h3>
                    <ul>
                        <li><a href="#"><i class="fa-solid fa-robot"></i> Automate website actions</a></li>
                        <li><a href="#"><i class="fa-solid fa-keyboard"></i> Automate Data Entry</a></li>
                        <li><a href="#"><i class="fa-solid fa-pen-to-square"></i> Automate Form Filling</a></li>
                        <li><a href="../product/templates/index.html"><i class="fa-solid fa-puzzle-piece"></i> Automation templates</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Blog</h3>
                    <ul>
                        <li><a href="blog.php?category=tutorials"><i class="fa-solid fa-graduation-cap"></i> Tutorials</a></li>
                        <li><a href="blog.php?category=features"><i class="fa-solid fa-star"></i> Features</a></li>
                        <li><a href="blog.php?category=case-studies"><i class="fa-solid fa-chart-line"></i> Case Studies</a></li>
                        <li><a href="blog.php?category=automation"><i class="fa-solid fa-lightbulb"></i> Automation tips</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> selenix.io. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../script.js"></script>
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
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }
        
        .error-page h1 {
            font-size: 3rem;
            color: var(--heading-color);
            margin-bottom: 1rem;
            font-weight: 800;
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
            
            .error-page p {
                font-size: 1.1rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .error-actions .primary-button,
            .error-actions .secondary-button {
                width: 200px;
            }
        }
    </style>
</body>
</html>
