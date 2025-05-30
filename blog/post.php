<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

$blogModel = new BlogModel();

// Get post slug from URL
$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit;
}

// Get post by slug
$post = $blogModel->getPostBySlug($slug);

if (!$post) {
    http_response_code(404);
    include '404.php';
    exit;
}

// Get related posts
$relatedPosts = $blogModel->getRecentPosts($post['id'], 3);

// Get comments
$comments = $blogModel->getComments($post['id']);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log all POST data
    error_log('Comment form submitted. POST data: ' . print_r($_POST, true));
    
    // Check if this is a comment submission
    $isCommentSubmission = isset($_POST['submit_comment']) || 
                          (isset($_POST['comment_name']) && isset($_POST['comment_email']) && isset($_POST['comment_content']));
    
    error_log('Is comment submission: ' . ($isCommentSubmission ? 'Yes' : 'No'));
    
    if ($isCommentSubmission) {
        $name = isset($_POST['comment_name']) ? sanitizeInput($_POST['comment_name']) : '';
        $email = isset($_POST['comment_email']) ? sanitizeInput($_POST['comment_email']) : '';
        $website = isset($_POST['comment_website']) ? sanitizeInput($_POST['comment_website']) : '';
        $content = isset($_POST['comment_content']) ? sanitizeInput($_POST['comment_content']) : '';
        
        error_log("Processed form data - Name: '$name', Email: '$email', Website: '$website', Content length: " . strlen($content));
        
        $errors = [];
        
        // Validate name
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Valid email is required';
        }
        
        // Validate content
        if (empty($content)) {
            $errors[] = 'Comment content is required';
        }
        
        // Debug: Log validation results
        error_log("Validation errors: " . (empty($errors) ? 'None' : implode(', ', $errors)));
        
        if (empty($errors)) {
            // Debug: Log before attempting to add comment
            error_log("Attempting to add comment for post ID: {$post['id']}");
            
            $commentId = $blogModel->addComment($post['id'], $name, $email, $website, $content);
            
            if ($commentId) {
                error_log("Comment added successfully with ID: $commentId");
                
                // Set success message and redirect to avoid form resubmission
                $redirectUrl = $_SERVER['REQUEST_URI'];
                // Remove any existing query parameters and add success parameter
                $redirectUrl = strtok($redirectUrl, '?') . '?comment=success#comments';
                header("Location: $redirectUrl");
                exit;
            } else {
                error_log("Failed to add comment for post ID: {$post['id']}");
                $commentMessage = 'There was an error submitting your comment. Please try again.';
                $commentSuccess = false;
            }
        } else {
            $commentMessage = implode(', ', $errors);
            $commentSuccess = false;
            error_log("Comment validation failed: " . $commentMessage);
        }
        
        // Refresh comments after submission attempt
        $comments = $blogModel->getComments($post['id']);
    }
}

// Check for success message from redirect
if (isset($_GET['comment']) && $_GET['comment'] === 'success') {
    $commentMessage = 'Thank you for your comment! It will be reviewed and published soon.';
    $commentSuccess = true;
}

// Generate breadcrumbs
$breadcrumbs = [
    ['title' => 'Home', 'url' => '../'],
    ['title' => 'Blog', 'url' => 'blog.php'],
    ['title' => $post['title'], 'url' => '']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php echo generateMetaTags($post); ?>
    
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
    
    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo htmlspecialchars($post['title']); ?>",
        "description": "<?php echo htmlspecialchars($post['excerpt']); ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo htmlspecialchars($post['author_name']); ?>"
        },
        "datePublished": "<?php echo date('c', $post['published_timestamp']); ?>",
        "dateModified": "<?php echo date('c', strtotime($post['created_at'])); ?>",
        "publisher": {
            "@type": "Organization",
            "name": "Selenix.io",
            "logo": {
                "@type": "ImageObject",
                "url": "<?php echo SITE_URL; ?>/favicon.ico"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?php echo BLOG_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>"
        }
        <?php if ($post['featured_image']): ?>
        ,
        "image": "<?php echo UPLOAD_URL . $post['featured_image']; ?>"
        <?php endif; ?>
    }
    </script>
</head>
<body>
    <!-- Navbar Component -->
    <div id="navbar-container"></div>

    <article class="post-single">
        <div class="container">
            <!-- Breadcrumbs -->
            <?php echo generateBreadcrumbs($breadcrumbs); ?>
            
            <!-- Post Header -->
            <header class="post-header">
                <div class="post-meta">
                    <span class="category <?php echo getCategoryColor($post['category']); ?>">
                        <a href="blog.php?category=<?php echo $post['category']; ?>">
                            <?php echo getCategoryName($post['category']); ?>
                        </a>
                    </span>
                    <span class="date"><?php echo formatDate($post['published_timestamp']); ?></span>
                    <span class="read-time"><?php echo $post['read_time']; ?> min read</span>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="post-excerpt">
                    <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                </div>
                
                <div class="post-author-card">
                    <div class="author-avatar-large">
                        <?php if ($post['author_avatar']): ?>
                            <img src="<?php echo UPLOAD_URL . $post['author_avatar']; ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/80x80/6b7280/ffffff?text=<?php echo strtoupper(substr($post['author_name'], 0, 2)); ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="author-info">
                        <h3 class="author-name"><?php echo htmlspecialchars($post['author_name']); ?></h3>
                        <?php if ($post['author_title']): ?>
                            <p class="author-title"><?php echo htmlspecialchars($post['author_title']); ?></p>
                        <?php endif; ?>
                        <p class="publish-date">Published <?php echo timeAgo($post['published_timestamp']); ?></p>
                    </div>
                </div>
                
                <!-- Social Share Buttons -->
                <div class="social-share">
                    <span class="share-label">Share this article:</span>
                    <div class="share-buttons">
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode(BLOG_URL . '/post.php?slug=' . $post['slug']); ?>" 
                           target="_blank" class="share-btn twitter">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(BLOG_URL . '/post.php?slug=' . $post['slug']); ?>" 
                           target="_blank" class="share-btn linkedin">
                            <i class="fa-brands fa-linkedin"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BLOG_URL . '/post.php?slug=' . $post['slug']); ?>" 
                           target="_blank" class="share-btn facebook">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                        <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode('Check out this article: ' . BLOG_URL . '/post.php?slug=' . $post['slug']); ?>" 
                           class="share-btn email">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Featured Image -->
            <?php if ($post['featured_image']): ?>
                <div class="post-featured-image">
                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
            <?php endif; ?>
            
            <!-- Post Content -->
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>
            
            <!-- Post Footer -->
            <footer class="post-footer">
                <div class="post-tags">
                    <!-- Tags would go here if implemented -->
                </div>
                
                <div class="post-navigation">
                    <a href="blog.php" class="back-to-blog">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Blog
                    </a>
                </div>
            </footer>
        </div>
    </article>

    <!-- Comments Section -->
    <section class="comments-section" id="comments">
        <div class="container">
            <div class="comments-container">
                <h3 class="comments-title">
                    <i class="fa-solid fa-comments"></i>
                    Comments (<?php echo count($comments); ?>)
                </h3>
                
                <!-- Comment Form -->
                <div class="comment-form-container">
                    <h4>Leave a Comment</h4>
                    
                    <!-- Debug information -->
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 10px; margin: 10px 0; border-radius: 5px;">
                            <strong>Debug Info:</strong><br>
                            Request Method: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
                            Form submitted: Yes<br>
                            POST data keys: <?php echo implode(', ', array_keys($_POST)); ?><br>
                            Has submit_comment: <?php echo isset($_POST['submit_comment']) ? 'Yes' : 'No'; ?><br>
                            Has required fields: <?php echo (isset($_POST['comment_name']) && isset($_POST['comment_email']) && isset($_POST['comment_content'])) ? 'Yes' : 'No'; ?><br>
                            Post ID: <?php echo isset($post['id']) ? $post['id'] : 'Not set'; ?><br>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($commentMessage)): ?>
                        <div class="comment-message <?php echo $commentSuccess ? 'success' : 'error'; ?>">
                            <i class="fa-solid fa-<?php echo $commentSuccess ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo htmlspecialchars($commentMessage); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="comment-form" id="comment-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="comment_name">Name *</label>
                                <input type="text" id="comment_name" name="comment_name" required 
                                       value="<?php echo isset($_POST['comment_name']) && !isset($commentSuccess) ? htmlspecialchars($_POST['comment_name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="comment_email">Email *</label>
                                <input type="email" id="comment_email" name="comment_email" required 
                                       value="<?php echo isset($_POST['comment_email']) && !isset($commentSuccess) ? htmlspecialchars($_POST['comment_email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment_website">Website (optional)</label>
                            <input type="url" id="comment_website" name="comment_website" 
                                   value="<?php echo isset($_POST['comment_website']) && !isset($commentSuccess) ? htmlspecialchars($_POST['comment_website']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="comment_content">Comment *</label>
                            <textarea id="comment_content" name="comment_content" rows="5" required 
                                      placeholder="Share your thoughts..."><?php echo isset($_POST['comment_content']) && !isset($commentSuccess) ? htmlspecialchars($_POST['comment_content']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="submit_comment" value="1" class="submit-comment-btn">
                            <i class="fa-solid fa-paper-plane"></i>
                            Submit Comment
                        </button>
                    </form>
                </div>
                
                <!-- Comments List -->
                <?php if (!empty($comments)): ?>
                    <div class="comments-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-avatar">
                                    <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($comment['email']))); ?>?s=50&d=identicon" 
                                         alt="<?php echo htmlspecialchars($comment['name']); ?>">
                                </div>
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <h5 class="comment-author">
                                            <?php if (!empty($comment['website'])): ?>
                                                <a href="<?php echo htmlspecialchars($comment['website']); ?>" target="_blank" rel="nofollow">
                                                    <?php echo htmlspecialchars($comment['name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($comment['name']); ?>
                                            <?php endif; ?>
                                        </h5>
                                        <span class="comment-date"><?php echo timeAgo(strtotime($comment['created_at'])); ?></span>
                                    </div>
                                    <div class="comment-body">
                                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-comments">
                        <i class="fa-solid fa-comments"></i>
                        <p>No comments yet. Be the first to share your thoughts!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <section class="related-posts">
        <div class="container">
            <h3 class="section-title">
                <i class="fa-solid fa-newspaper"></i>
                Related Articles
            </h3>
            <div class="related-posts-grid">
                <?php foreach ($relatedPosts as $relatedPost): ?>
                    <article class="related-post-card">
                        <div class="related-post-image">
                            <?php if ($relatedPost['featured_image']): ?>
                                <img src="<?php echo UPLOAD_URL . $relatedPost['featured_image']; ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200/<?php echo substr(md5($relatedPost['category']), 0, 6); ?>/ffffff?text=<?php echo urlencode(getCategoryName($relatedPost['category'])); ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="related-post-content">
                            <div class="related-post-meta">
                                <span class="category <?php echo getCategoryColor($relatedPost['category']); ?>">
                                    <?php echo getCategoryName($relatedPost['category']); ?>
                                </span>
                                <span class="read-time"><?php echo $relatedPost['read_time']; ?> min read</span>
                            </div>
                            <h4><a href="post.php?slug=<?php echo $relatedPost['slug']; ?>"><?php echo htmlspecialchars($relatedPost['title']); ?></a></h4>
                            <p><?php echo truncateText($relatedPost['excerpt'], 100); ?></p>
                            <div class="related-post-author">
                                <span><?php echo htmlspecialchars($relatedPost['author_name']); ?></span>
                                <span><?php echo formatDate($relatedPost['published_timestamp']); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Newsletter Signup -->
    <section class="newsletter-signup">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Get More Like This</h2>
                    <p>Subscribe to our newsletter for the latest automation tips, tutorials, and product updates.</p>
                </div>
                <div class="newsletter-form">
                    <form method="POST" action="blog.php">
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
    <script src="../components/navbar/navbar.js"></script>
    <script src="../components/navbar/navbar-loader.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Social share functionality
            document.querySelectorAll('.share-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (this.href.includes('mailto:')) {
                        return; // Let email links work normally
                    }
                    
                    e.preventDefault();
                    const url = this.href;
                    window.open(url, 'share', 'width=600,height=400,resizable=yes');
                });
            });

            // Enhanced comment form handling
            const commentForm = document.getElementById('comment-form');
            
            if (commentForm) {
                commentForm.addEventListener('submit', function(e) {
                    console.log('Comment form submitted');
                    
                    const submitButton = this.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    
                    // Disable button and show loading state
                    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
                    submitButton.disabled = true;
                    
                    // Log form data for debugging
                    const formData = new FormData(this);
                    console.log('Form data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    // Re-enable button after a timeout (in case of errors)
                    setTimeout(() => {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }, 5000);
                });
            }

            // Hide comment message after 5 seconds
            const commentMessage = document.querySelector('.comment-message');
            if (commentMessage) {
                setTimeout(() => {
                    commentMessage.style.opacity = '0';
                    setTimeout(() => {
                        commentMessage.style.display = 'none';
                    }, 300);
                }, 5000);
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
        });
    </script>
</body>
</html>
