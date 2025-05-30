<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

$blogModel = new BlogModel();

// Get current page and category
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;

// Get posts and pagination info
$posts = $blogModel->getPosts($page, $category);
$totalPosts = $blogModel->getPostsCount($category);
$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// Get featured post
$featuredPost = $blogModel->getFeaturedPost();

// Get categories with counts
$categories = $blogModel->getCategoriesWithCounts();

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = sanitizeInput($_POST['newsletter_email']);
    
    if (isValidEmail($email)) {
        if ($blogModel->subscribeNewsletter($email)) {
            $newsletterMessage = 'Thank you for subscribing to our newsletter!';
            $newsletterSuccess = true;
        } else {
            $newsletterMessage = 'You are already subscribed or there was an error.';
            $newsletterSuccess = false;
        }
    } else {
        $newsletterMessage = 'Please enter a valid email address.';
        $newsletterSuccess = false;
    }
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    if ($_GET['ajax'] === 'load_more') {
        $ajaxPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $ajaxCategory = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
        $ajaxPosts = $blogModel->getPosts($ajaxPage, $ajaxCategory);
        
        sendJsonResponse([
            'success' => true,
            'posts' => $ajaxPosts,
            'has_more' => $ajaxPage < $totalPages
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category ? getCategoryName($category) . ' - ' : ''; ?>Blog - Selenix.io</title>
    <meta name="description" content="<?php echo BLOG_DESCRIPTION; ?>">
    
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
                    <a href="blog.php" class="active">Blog</a>
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

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo BLOG_TITLE; ?></h1>
                <p><?php echo BLOG_DESCRIPTION; ?></p>
                <div class="blog-categories">
                    <a href="blog.php" class="category-tag <?php echo !$category ? 'active' : ''; ?>">All Posts</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="blog.php?category=<?php echo $cat['category']; ?>" 
                           class="category-tag <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                            <?php echo getCategoryName($cat['category']); ?> (<?php echo $cat['post_count']; ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($featuredPost && !$category && $page === 1): ?>
    <section class="featured-post">
        <div class="container">
            <div class="featured-content">
                <div class="featured-image">
                    <?php if ($featuredPost['featured_image']): ?>
                        <img src="<?php echo UPLOAD_URL . $featuredPost['featured_image']; ?>" alt="<?php echo htmlspecialchars($featuredPost['title']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600x400/4f46e5/ffffff?text=Featured+Post" alt="Featured Post">
                    <?php endif; ?>
                    <div class="featured-badge">Featured</div>
                </div>
                <div class="featured-text">
                    <div class="post-meta">
                        <span class="category <?php echo getCategoryColor($featuredPost['category']); ?>">
                            <?php echo getCategoryName($featuredPost['category']); ?>
                        </span>
                        <span class="date"><?php echo formatDate($featuredPost['published_timestamp']); ?></span>
                        <span class="read-time"><?php echo $featuredPost['read_time']; ?> min read</span>
                    </div>
                    <h2><?php echo htmlspecialchars($featuredPost['title']); ?></h2>
                    <p><?php echo htmlspecialchars($featuredPost['excerpt']); ?></p>
                    <div class="post-author">
                        <?php if ($featuredPost['author_avatar']): ?>
                            <img src="<?php echo UPLOAD_URL . $featuredPost['author_avatar']; ?>" alt="<?php echo htmlspecialchars($featuredPost['author_name']); ?>" class="author-avatar">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/50x50/6b7280/ffffff?text=<?php echo strtoupper(substr($featuredPost['author_name'], 0, 2)); ?>" alt="<?php echo htmlspecialchars($featuredPost['author_name']); ?>" class="author-avatar">
                        <?php endif; ?>
                        <div class="author-info">
                            <span class="author-name"><?php echo htmlspecialchars($featuredPost['author_name']); ?></span>
                            <?php if ($featuredPost['author_title']): ?>
                                <span class="author-title"><?php echo htmlspecialchars($featuredPost['author_title']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($featuredPost['comment_count'] > 0): ?>
                            <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>#comments" class="featured-comment-count" title="View comments">
                                <i class="fa-solid fa-comments"></i>
                                <?php echo $featuredPost['comment_count']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>" class="read-more-btn">Read Full Article</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="blog-posts">
        <div class="container">
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <i class="fa-solid fa-newspaper"></i>
                    <h3>No posts found</h3>
                    <p><?php echo $category ? 'No posts found in the ' . getCategoryName($category) . ' category.' : 'No blog posts available at the moment.'; ?></p>
                    <?php if ($category): ?>
                        <a href="blog.php" class="primary-button">View All Posts</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="posts-grid" id="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card" data-category="<?php echo $post['category']; ?>">
                            <div class="post-image">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x250/<?php echo substr(md5($post['category']), 0, 6); ?>/ffffff?text=<?php echo urlencode(getCategoryName($post['category'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="category <?php echo getCategoryColor($post['category']); ?>">
                                        <?php echo getCategoryName($post['category']); ?>
                                    </span>
                                    <span class="date"><?php echo formatDate($post['published_timestamp']); ?></span>
                                </div>
                                <h3><a href="post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                <div class="post-footer">
                                    <div class="author-mini">
                                        <?php if ($post['author_avatar']): ?>
                                            <img src="<?php echo UPLOAD_URL . $post['author_avatar']; ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/30x30/6b7280/ffffff?text=<?php echo strtoupper(substr($post['author_name'], 0, 2)); ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($post['author_name']); ?></span>
                                    </div>
                                    <div class="post-stats">
                                        <span class="read-time"><?php echo $post['read_time']; ?> min read</span>
                                        <?php if ($post['comment_count'] > 0): ?>
                                            <a href="post.php?slug=<?php echo $post['slug']; ?>#comments" class="comment-count" title="View comments">
                                                <i class="fa-solid fa-comments"></i>
                                                <?php echo $post['comment_count']; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-section">
                        <?php
                        $baseUrl = 'blog.php' . ($category ? '?category=' . $category : '');
                        echo generatePagination($page, $totalPages, $baseUrl);
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="newsletter-signup">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Stay Updated</h2>
                    <p>Get the latest automation tips, tutorials, and product updates delivered to your inbox.</p>
                </div>
                <div class="newsletter-form">
                    <?php if (isset($newsletterMessage)): ?>
                        <div class="newsletter-message <?php echo $newsletterSuccess ? 'success' : 'error'; ?>">
                            <i class="fa-solid fa-<?php echo $newsletterSuccess ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo htmlspecialchars($newsletterMessage); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="email" name="newsletter_email" placeholder="Enter your email address" required>
                        <button type="submit">
                            <i class="fa-solid fa-paper-plane"></i>
                            Subscribe
                        </button>
                    </form>
                    <p class="newsletter-note">No spam, unsubscribe anytime. Join 5,000+ automation enthusiasts!</p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu functionality
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const navLinks = document.querySelector('.nav-links');

            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }

            // Newsletter form enhancement
            const newsletterForm = document.querySelector('.newsletter-form form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    
                    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subscribing...';
                    submitButton.disabled = true;
                    
                    // Re-enable button after form submission (in case of errors)
                    setTimeout(() => {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }, 3000);
                });
            }

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Hide newsletter message after 5 seconds
            const newsletterMessage = document.querySelector('.newsletter-message');
            if (newsletterMessage) {
                setTimeout(() => {
                    newsletterMessage.style.opacity = '0';
                    setTimeout(() => {
                        newsletterMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>
