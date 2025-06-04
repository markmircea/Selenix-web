<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

requireAdmin();

$blogModel = new BlogModel();

// Get dashboard stats
$stats = $blogModel->getDashboardStats();

// Get recent posts
$recentPosts = $blogModel->getAllPosts(1, 5);

// Get recent comments
$recentComments = $blogModel->getRecentComments(5);

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve_comment':
                $commentId = intval($_POST['comment_id']);
                if ($blogModel->approveComment($commentId)) {
                    $message = 'Comment approved successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error approving comment';
                    $messageType = 'error';
                }
                break;
                
            case 'delete_comment':
                $commentId = intval($_POST['comment_id']);
                if ($blogModel->deleteComment($commentId)) {
                    $message = 'Comment deleted successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting comment';
                    $messageType = 'error';
                }
                break;
        }
        
        // Refresh comments after action
        $recentComments = $blogModel->getRecentComments(5);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Selenix Blog</title>
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
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                    <span class="admin-label">Admin</span>
                </h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin-dashboard.php" class="active"><i class="fa-solid fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="admin-posts.php"><i class="fa-solid fa-newspaper"></i> Posts</a></li>
                    <li><a href="admin-add-post.php"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
                    <li><a href="admin-ai-generate.php"><i class="fa-solid fa-brain"></i> AI Generator</a></li>
                    <li><a href="admin-comments.php"><i class="fa-solid fa-comments"></i> Comments</a></li>
                    <li><a href="admin-subscribers.php"><i class="fa-solid fa-users"></i> Subscribers</a></li>
                    <li class="nav-divider"></li>
                    <li><a href="blog.php" target="_blank"><i class="fa-solid fa-external-link-alt"></i> View Blog</a></li>
                    <li><a href="admin-logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-actions">
                    <a href="admin-ai-generate.php" class="btn btn-success">
                        <i class="fa-solid fa-brain"></i>
                        AI Generator
                    </a>
                    <a href="admin-add-post.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        New Post
                    </a>
                </div>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="admin-message <?php echo $messageType; ?>">
                    <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_posts']; ?></h3>
                        <p>Total Posts</p>
                        <div class="stat-detail">
                            <span class="published"><?php echo $stats['published_posts']; ?> Published</span>
                            <span class="draft"><?php echo $stats['draft_posts']; ?> Drafts</span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_comments']; ?></h3>
                        <p>Total Comments</p>
                        <div class="stat-detail">
                            <?php if ($stats['pending_comments'] > 0): ?>
                                <span class="pending"><?php echo $stats['pending_comments']; ?> Pending Review</span>
                            <?php else: ?>
                                <span class="approved">All comments approved</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['newsletter_subscribers']; ?></h3>
                        <p>Newsletter Subscribers</p>
                        <div class="stat-detail">
                            <span class="active">Active subscribers</span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo date('M Y'); ?></h3>
                        <p>Current Month</p>
                        <div class="stat-detail">
                            <span class="info">Blog analytics</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="dashboard-content">
                <div class="content-section">
                    <div class="section-header">
                        <h2>
                            <i class="fa-solid fa-newspaper"></i>
                            Recent Posts
                        </h2>
                        <a href="admin-posts.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (!empty($recentPosts)): ?>
                        <div class="recent-posts-list">
                            <?php foreach ($recentPosts as $post): ?>
                                <div class="recent-post-item">
                                    <div class="post-info">
                                        <h4>
                                            <a href="admin-edit-post.php?id=<?php echo $post['id']; ?>">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h4>
                                        <div class="post-meta">
                                            <span class="category <?php echo getCategoryColor($post['category']); ?>">
                                                <?php echo getCategoryName($post['category']); ?>
                                            </span>
                                            <span class="status <?php echo $post['is_published'] ? 'published' : 'draft'; ?>">
                                                <?php echo $post['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                            <span class="date"><?php echo formatDate(strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="post-actions">
                                        <a href="admin-edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <?php if ($post['is_published']): ?>
                                            <a href="post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-newspaper"></i>
                            <p>No posts yet. <a href="admin-add-post.php">Create your first post</a>.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="content-section">
                    <div class="section-header">
                        <h2>
                            <i class="fa-solid fa-comments"></i>
                            Recent Comments
                        </h2>
                        <a href="admin-comments.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (!empty($recentComments)): ?>
                        <div class="recent-comments-list">
                            <?php foreach ($recentComments as $comment): ?>
                                <div class="recent-comment-item">
                                    <div class="comment-avatar">
                                        <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($comment['email']))); ?>?s=40&d=identicon" 
                                             alt="<?php echo htmlspecialchars($comment['name']); ?>">
                                    </div>
                                    <div class="comment-info">
                                        <div class="comment-header">
                                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                            <span class="comment-post">on <a href="post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank"><?php echo htmlspecialchars($comment['post_title']); ?></a></span>
                                        </div>
                                        <p class="comment-content"><?php echo truncateText($comment['content'], 100); ?></p>
                                        <div class="comment-meta">
                                            <span class="date"><?php echo timeAgo(strtotime($comment['created_at'])); ?></span>
                                            <span class="status <?php echo $comment['is_approved'] ? 'approved' : 'pending'; ?>">
                                                <?php echo $comment['is_approved'] ? 'Approved' : 'Pending'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (!$comment['is_approved']): ?>
                                        <div class="comment-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="approve_comment">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" 
                                                        onclick="return confirm('Are you sure you want to delete this comment?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-comments"></i>
                            <p>No comments yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide admin messages after 5 seconds
            const adminMessage = document.querySelector('.admin-message');
            if (adminMessage) {
                setTimeout(() => {
                    adminMessage.style.opacity = '0';
                    setTimeout(() => {
                        adminMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
            
            // Confirm delete actions
            document.querySelectorAll('.btn-danger').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
