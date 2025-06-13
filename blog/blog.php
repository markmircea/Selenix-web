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
                    <p><?php echo htmlspecialchars(strip_tags(cleanAIContent($featuredPost['excerpt']))); ?></p>
                    <div class="post-author">
                        <img src="<?php echo generateAvatarUrl($featuredPost['author_name'], $featuredPost['author_avatar'], 50); ?>" alt="<?php echo htmlspecialchars($featuredPost['author_name']); ?>" class="author-avatar">
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
                                <p><?php echo htmlspecialchars(strip_tags(cleanAIContent($post['excerpt']))); ?></p>
                                <div class="post-footer">
                                    <div class="author-mini">
                                        <img src="<?php echo generateAvatarUrl($post['author_name'], $post['author_avatar'], 30); ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
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
    <div id="footer-container"></div>

    <script src="../script.js"></script>
    <script src="../components/components.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
