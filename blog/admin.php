<?php
require_once 'blog/config.php';
require_once 'blog/models.php';
require_once 'blog/functions.php';
require_once 'support/config.php';

session_start();

// Check if user is logged in (we'll use the blog admin auth for now)
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) || isset($_SESSION['blog_admin_logged_in']);
}

function requireUnifiedAdmin() {
    if (!isAdminLoggedIn()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Check against both admin systems
            if (($username === 'admin' && $password === 'selenix2024') || 
                ($username === 'blog_admin' && $password === 'blog_password')) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['blog_admin_logged_in'] = true;
                $_SESSION['admin_user'] = $username;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = 'Invalid credentials';
            }
        }
        
        // Show unified login form
        showUnifiedLoginForm($error ?? null);
        exit;
    }
}

function showUnifiedLoginForm($error = null) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Selenix Admin Dashboard</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="blog/blog-styles.css">
        <link rel="stylesheet" href="blog/admin-styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="admin-login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>
                        <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                        <span class="admin-label">Unified Admin</span>
                    </h1>
                    <p>Access all admin functions from one dashboard</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">
                            <i class="fa-solid fa-user"></i>
                            Username
                        </label>
                        <input type="text" id="username" name="username" required>
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
                        Login to Dashboard
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="index.html">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Website
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

requireUnifiedAdmin();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Initialize models and connections
$blogModel = new BlogModel();

// Database connection for main admin and support
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

// Support database connection
try {
    $supportPdo = getDatabaseConnection(); // From support config
} catch (Exception $e) {
    $supportPdo = null;
}

// Get current section from URL parameter
$current_section = $_GET['section'] ?? 'overview';

// Handle various admin actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Blog comment actions
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
            
        // Support ticket actions
        case 'update_support_status':
            if ($supportPdo) {
                $id = $_POST['submission_id'];
                $status = $_POST['status'];
                $notes = $_POST['notes'] ?? '';
                
                try {
                    $stmt = $supportPdo->prepare("UPDATE contact_submissions SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $notes, $id]);
                    $message = 'Support ticket updated successfully';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error updating support ticket: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
            break;
    }
}

// Get dashboard data
$blogStats = $blogModel->getDashboardStats();
$recentPosts = $blogModel->getAllPosts(1, 5);
$recentComments = $blogModel->getRecentComments(5);

// Get download stats (if available)
$downloadStats = null;
$templateStats = null;
$recentDownloads = [];
$recentTemplateDownloads = [];

if ($pdo) {
    try {
        // Software download stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_downloads FROM downloads");
        $downloadStats = $stmt->fetch();
        
        $stmt = $pdo->query("SELECT COUNT(DISTINCT email) as unique_emails FROM downloads");
        $uniqueDownloadEmails = $stmt->fetch();
        $downloadStats['unique_emails'] = $uniqueDownloadEmails['unique_emails'];
        
        // Template download stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_downloads FROM template_downloads");
        $templateStats = $stmt->fetch();
        
        // Recent downloads
        $stmt = $pdo->query("
            SELECT email, download_time, ip_address, COALESCE(platform, 'windows') as platform
            FROM downloads 
            ORDER BY download_time DESC 
            LIMIT 10
        ");
        $recentDownloads = $stmt->fetchAll();
        
        // Recent template downloads
        $stmt = $pdo->query("
            SELECT t.title, td.email, td.download_time, td.ip_address 
            FROM template_downloads td 
            JOIN templates t ON td.template_id = t.id 
            ORDER BY td.download_time DESC 
            LIMIT 10
        ");
        $recentTemplateDownloads = $stmt->fetchAll();
        
    } catch (Exception $e) {
        // Ignore errors for now
    }
}

// Get support stats (if available)
$supportStats = null;
$recentTickets = [];

if ($supportPdo) {
    try {
        $stmt = $supportPdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week
            FROM contact_submissions
        ");
        $supportStats = $stmt->fetch();
        
        // Recent tickets
        $stmt = $supportPdo->query("
            SELECT * FROM contact_submissions 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $recentTickets = $stmt->fetchAll();
        
    } catch (Exception $e) {
        // Ignore errors for now
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selenix Unified Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="blog/blog-styles.css">
    <link rel="stylesheet" href="blog/admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-nav {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .section-nav li {
            flex: 1;
        }
        
        .section-nav a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            text-decoration: none;
            color: #6b7280;
            font-weight: 600;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .section-nav a:hover,
        .section-nav a.active {
            color: var(--primary-color);
            background: var(--light-bg);
            border-bottom-color: var(--primary-color);
        }
        
        .section-content {
            display: none;
        }
        
        .section-content.active {
            display: block;
        }
        
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .mini-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .mini-section-header {
            padding: 1rem 1.5rem;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--heading-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .mini-section-content {
            padding: 1.5rem;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .quick-stat {
            text-align: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
        }
        
        .quick-stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }
        
        .quick-stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .recent-items {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .recent-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item-info {
            flex: 1;
        }
        
        .recent-item-title {
            font-weight: 600;
            color: var(--heading-color);
            font-size: 0.9rem;
        }
        
        .recent-item-meta {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .section-nav ul {
                flex-direction: column;
            }
            
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                    <span class="admin-label">Unified Admin</span>
                </h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="?section=overview" class="<?php echo $current_section === 'overview' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-dashboard"></i> Overview
                    </a></li>
                    <li><a href="?section=blog" class="<?php echo $current_section === 'blog' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-newspaper"></i> Blog Management
                    </a></li>
                    <li><a href="?section=support" class="<?php echo $current_section === 'support' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-headset"></i> Support Tickets
                    </a></li>
                    <li><a href="?section=downloads" class="<?php echo $current_section === 'downloads' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-download"></i> Downloads
                    </a></li>
                    <li class="nav-divider"></li>
                    <li><a href="blog/admin-posts.php"><i class="fa-solid fa-newspaper"></i> Manage Posts</a></li>
                    <li><a href="blog/admin-add-post.php"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
                    <li><a href="blog/admin-ai-generate.php"><i class="fa-solid fa-brain"></i> AI Generator</a></li>
                    <li><a href="blog/admin-comments.php"><i class="fa-solid fa-comments"></i> All Comments</a></li>
                    <li><a href="blog/admin-subscribers.php"><i class="fa-solid fa-users"></i> Subscribers</a></li>
                    <li class="nav-divider"></li>
                    <li><a href="index.html" target="_blank"><i class="fa-solid fa-external-link-alt"></i> View Website</a></li>
                    <li><a href="blog/blog.php" target="_blank"><i class="fa-solid fa-external-link-alt"></i> View Blog</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Unified Admin Dashboard</h1>
                <div class="admin-actions">
                    <span style="color: #6b7280;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="admin-message <?php echo $messageType; ?>">
                    <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Section Navigation -->
            <nav class="section-nav">
                <ul>
                    <li><a href="?section=overview" class="<?php echo $current_section === 'overview' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie"></i> Overview
                    </a></li>
                    <li><a href="?section=blog" class="<?php echo $current_section === 'blog' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-newspaper"></i> Blog
                    </a></li>
                    <li><a href="?section=support" class="<?php echo $current_section === 'support' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-headset"></i> Support
                    </a></li>
                    <li><a href="?section=downloads" class="<?php echo $current_section === 'downloads' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-download"></i> Downloads
                    </a></li>
                </ul>
            </nav>
            
            <!-- Overview Section -->
            <div class="section-content <?php echo $current_section === 'overview' ? 'active' : ''; ?>">
                <div class="overview-grid">
                    <!-- Blog Overview -->
                    <div class="mini-section">
                        <div class="mini-section-header">
                            <i class="fa-solid fa-newspaper"></i>
                            Blog Overview
                        </div>
                        <div class="mini-section-content">
                            <div class="quick-stats">
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $blogStats['total_posts']; ?></div>
                                    <div class="quick-stat-label">Total Posts</div>
                                </div>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $blogStats['total_comments']; ?></div>
                                    <div class="quick-stat-label">Comments</div>
                                </div>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $blogStats['newsletter_subscribers']; ?></div>
                                    <div class="quick-stat-label">Subscribers</div>
                                </div>
                            </div>
                            <a href="?section=blog" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-arrow-right"></i> Manage Blog
                            </a>
                        </div>
                    </div>
                    
                    <!-- Support Overview -->
                    <?php if ($supportStats): ?>
                    <div class="mini-section">
                        <div class="mini-section-header">
                            <i class="fa-solid fa-headset"></i>
                            Support Overview
                        </div>
                        <div class="mini-section-content">
                            <div class="quick-stats">
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $supportStats['total']; ?></div>
                                    <div class="quick-stat-label">Total Tickets</div>
                                </div>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $supportStats['new_count']; ?></div>
                                    <div class="quick-stat-label">New</div>
                                </div>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $supportStats['in_progress']; ?></div>
                                    <div class="quick-stat-label">In Progress</div>
                                </div>
                            </div>
                            <a href="?section=support" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-arrow-right"></i> Manage Support
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Downloads Overview -->
                    <?php if ($downloadStats): ?>
                    <div class="mini-section">
                        <div class="mini-section-header">
                            <i class="fa-solid fa-download"></i>
                            Downloads Overview
                        </div>
                        <div class="mini-section-content">
                            <div class="quick-stats">
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $downloadStats['total_downloads']; ?></div>
                                    <div class="quick-stat-label">Software</div>
                                </div>
                                <?php if ($templateStats): ?>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $templateStats['total_downloads']; ?></div>
                                    <div class="quick-stat-label">Templates</div>
                                </div>
                                <?php endif; ?>
                                <div class="quick-stat">
                                    <div class="quick-stat-number"><?php echo $downloadStats['unique_emails']; ?></div>
                                    <div class="quick-stat-label">Unique Users</div>
                                </div>
                            </div>
                            <a href="?section=downloads" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-arrow-right"></i> View Downloads
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Recent Activity -->
                    <div class="mini-section">
                        <div class="mini-section-header">
                            <i class="fa-solid fa-clock"></i>
                            Recent Activity
                        </div>
                        <div class="mini-section-content">
                            <div class="recent-items">
                                <?php if (!empty($recentComments)): ?>
                                    <?php foreach (array_slice($recentComments, 0, 3) as $comment): ?>
                                        <div class="recent-item">
                                            <div class="recent-item-info">
                                                <div class="recent-item-title">New comment by <?php echo htmlspecialchars($comment['name']); ?></div>
                                                <div class="recent-item-meta"><?php echo timeAgo(strtotime($comment['created_at'])); ?></div>
                                            </div>
                                            <span class="status-badge <?php echo $comment['is_approved'] ? 'status-approved' : 'status-pending'; ?>">
                                                <?php echo $comment['is_approved'] ? 'Approved' : 'Pending'; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($recentTickets)): ?>
                                    <?php foreach (array_slice($recentTickets, 0, 2) as $ticket): ?>
                                        <div class="recent-item">
                                            <div class="recent-item-info">
                                                <div class="recent-item-title">Support: <?php echo htmlspecialchars(substr($ticket['subject'], 0, 30)); ?>...</div>
                                                <div class="recent-item-meta"><?php echo date('M j, H:i', strtotime($ticket['created_at'])); ?></div>
                                            </div>
                                            <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Blog Section -->
            <div class="section-content <?php echo $current_section === 'blog' ? 'active' : ''; ?>">
                <!-- Blog Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $blogStats['total_posts']; ?></h3>
                            <p>Total Posts</p>
                            <div class="stat-detail">
                                <span class="published"><?php echo $blogStats['published_posts']; ?> Published</span>
                                <span class="draft"><?php echo $blogStats['draft_posts']; ?> Drafts</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $blogStats['total_comments']; ?></h3>
                            <p>Total Comments</p>
                            <div class="stat-detail">
                                <?php if ($blogStats['pending_comments'] > 0): ?>
                                    <span class="pending"><?php echo $blogStats['pending_comments']; ?> Pending Review</span>
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
                            <h3><?php echo $blogStats['newsletter_subscribers']; ?></h3>
                            <p>Newsletter Subscribers</p>
                            <div class="stat-detail">
                                <span class="active">Active subscribers</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div style="margin-bottom: 2rem;">
                    <a href="blog/admin-add-post.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> New Post
                    </a>
                    <a href="blog/admin-ai-generate.php" class="btn btn-success">
                        <i class="fa-solid fa-brain"></i> AI Generator
                    </a>
                    <a href="blog/admin-posts.php" class="btn btn-secondary">
                        <i class="fa-solid fa-list"></i> All Posts
                    </a>
                    <a href="blog/admin-comments.php" class="btn btn-info">
                        <i class="fa-solid fa-comments"></i> All Comments
                    </a>
                </div>
                
                <!-- Recent Content -->
                <div class="dashboard-content">
                    <div class="content-section">
                        <div class="section-header">
                            <h2>
                                <i class="fa-solid fa-newspaper"></i>
                                Recent Posts
                            </h2>
                            <a href="blog/admin-posts.php" class="view-all">View All</a>
                        </div>
                        
                        <?php if (!empty($recentPosts)): ?>
                            <div class="recent-posts-list">
                                <?php foreach ($recentPosts as $post): ?>
                                    <div class="recent-post-item">
                                        <div class="post-info">
                                            <h4>
                                                <a href="blog/admin-edit-post.php?id=<?php echo $post['id']; ?>">
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
                                            <a href="blog/admin-edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-secondary">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <?php if ($post['is_published']): ?>
                                                <a href="blog/post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-info">
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
                                <p>No posts yet. <a href="blog/admin-add-post.php">Create your first post</a>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="content-section">
                        <div class="section-header">
                            <h2>
                                <i class="fa-solid fa-comments"></i>
                                Recent Comments
                            </h2>
                            <a href="blog/admin-comments.php" class="view-all">View All</a>
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
                                                <span class="comment-post">on <a href="blog/post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank"><?php echo htmlspecialchars($comment['post_title']); ?></a></span>
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
            </div>
            
            <!-- Support Section -->
            <div class="section-content <?php echo $current_section === 'support' ? 'active' : ''; ?>">
                <?php if ($supportStats): ?>
                    <!-- Support Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-ticket"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $supportStats['total']; ?></h3>
                                <p>Total Tickets</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $supportStats['new_count']; ?></h3>
                                <p>New Tickets</p>
                                <div class="stat-detail">
                                    <span class="pending">Needs attention</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-cog"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $supportStats['in_progress']; ?></h3>
                                <p>In Progress</p>
                                <div class="stat-detail">
                                    <span class="active">Being worked on</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-calendar-week"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $supportStats['this_week']; ?></h3>
                                <p>This Week</p>
                                <div class="stat-detail">
                                    <span class="info">Recent submissions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div style="margin-bottom: 2rem;">
                        <a href="support/admin.php" class="btn btn-primary">
                            <i class="fa-solid fa-external-link-alt"></i> Full Support Admin
                        </a>
                        <a href="support/admin.php?status=new" class="btn btn-warning">
                            <i class="fa-solid fa-exclamation-circle"></i> New Tickets
                        </a>
                    </div>
                    
                    <!-- Recent Tickets -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2>
                                <i class="fa-solid fa-headset"></i>
                                Recent Support Tickets
                            </h2>
                            <a href="support/admin.php" class="view-all">View All</a>
                        </div>
                        
                        <?php if (!empty($recentTickets)): ?>
                            <div class="data-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($ticket['ticket_number'] ?? $ticket['id']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                                                <td title="<?php echo htmlspecialchars($ticket['subject']); ?>">
                                                    <?php echo htmlspecialchars(substr($ticket['subject'], 0, 40) . (strlen($ticket['subject']) > 40 ? '...' : '')); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <a href="support/admin.php?view=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-headset"></i>
                                <p>No support tickets yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="background: white; border-radius: 12px; padding: 3rem;">
                        <i class="fa-solid fa-database"></i>
                        <p>Support system not available. Database connection failed.</p>
                        <a href="support/admin.php" class="btn btn-primary">
                            <i class="fa-solid fa-external-link-alt"></i> Try Support Admin
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Downloads Section -->
            <div class="section-content <?php echo $current_section === 'downloads' ? 'active' : ''; ?>">
                <?php if ($downloadStats): ?>
                    <!-- Downloads Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-download"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $downloadStats['total_downloads']; ?></h3>
                                <p>Software Downloads</p>
                            </div>
                        </div>
                        
                        <?php if ($templateStats): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $templateStats['total_downloads']; ?></h3>
                                <p>Template Downloads</p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $downloadStats['unique_emails']; ?></h3>
                                <p>Unique Users</p>
                                <div class="stat-detail">
                                    <span class="active">Downloaded software</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $downloadStats['total_downloads'] > 0 ? round($downloadStats['total_downloads'] / max($downloadStats['unique_emails'], 1), 1) : 0; ?></h3>
                                <p>Avg Downloads/User</p>
                                <div class="stat-detail">
                                    <span class="info">Download ratio</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div style="margin-bottom: 2rem;">
                        <a href="admin.php" class="btn btn-primary">
                            <i class="fa-solid fa-external-link-alt"></i> Full Downloads Admin
                        </a>
                        <a href="admin.php?date_filter=7" class="btn btn-info">
                            <i class="fa-solid fa-calendar-week"></i> This Week
                        </a>
                    </div>
                    
                    <!-- Recent Downloads -->
                    <div class="dashboard-content">
                        <div class="content-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fa-solid fa-download"></i>
                                    Recent Software Downloads
                                </h2>
                                <a href="admin.php" class="view-all">View All</a>
                            </div>
                            
                            <?php if (!empty($recentDownloads)): ?>
                                <div class="data-table">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Platform</th>
                                                <th>Date/Time</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentDownloads as $download): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($download['email']); ?></td>
                                                    <td>
                                                        <?php if ($download['platform'] === 'mac'): ?>
                                                            <span style="color: #007AFF;"><i class="fa-brands fa-apple"></i> Mac</span>
                                                        <?php else: ?>
                                                            <span style="color: #0078D4;"><i class="fa-brands fa-windows"></i> Windows</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M j, Y g:i A', strtotime($download['download_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($download['ip_address']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fa-solid fa-download"></i>
                                    <p>No downloads yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($recentTemplateDownloads)): ?>
                        <div class="content-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fa-solid fa-file-alt"></i>
                                    Recent Template Downloads
                                </h2>
                                <a href="admin.php" class="view-all">View All</a>
                            </div>
                            
                            <div class="data-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Template</th>
                                            <th>Email</th>
                                            <th>Date/Time</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTemplateDownloads as $download): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($download['title']); ?></td>
                                                <td><?php echo htmlspecialchars($download['email'] ?? 'Anonymous'); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($download['download_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($download['ip_address']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="background: white; border-radius: 12px; padding: 3rem;">
                        <i class="fa-solid fa-database"></i>
                        <p>Downloads system not available. Database connection failed.</p>
                        <a href="admin.php" class="btn btn-primary">
                            <i class="fa-solid fa-external-link-alt"></i> Try Downloads Admin
                        </a>
                    </div>
                <?php endif; ?>
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
            
            // Section navigation (if JavaScript is needed for tab switching)
            const sectionLinks = document.querySelectorAll('.section-nav a');
            const sections = document.querySelectorAll('.section-content');
            
            sectionLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const url = new URL(this.href);
                    const section = url.searchParams.get('section');
                    
                    if (section && !e.ctrlKey && !e.metaKey) {
                        e.preventDefault();
                        
                        // Update URL without reload
                        history.pushState(null, '', this.href);
                        
                        // Update active states
                        sectionLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Show/hide sections
                        sections.forEach(s => s.classList.remove('active'));
                        const targetSection = document.querySelector(`.section-content:nth-child(${getSectionIndex(section)})`);
                        if (targetSection) {
                            targetSection.classList.add('active');
                        }
                    }
                });
            });
            
            function getSectionIndex(section) {
                const sectionMap = {
                    'overview': 1,
                    'blog': 2,
                    'support': 3,
                    'downloads': 4
                };
                return sectionMap[section] || 1;
            }
        });
    </script>
</body>
</html>