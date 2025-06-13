<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

requireAdmin();

$blogModel = new BlogModel();

// Handle post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $postId = intval($_POST['post_id']);
    
    switch ($_POST['action']) {
        case 'delete':
            if ($blogModel->deletePost($postId)) {
                $message = 'Post deleted successfully';
                $messageType = 'success';
            } else {
                $message = 'Error deleting post';
                $messageType = 'error';
            }
            break;
            
        case 'toggle_publish':
            $post = $blogModel->getPostForEdit($postId);
            if ($post) {
                $newStatus = $post['is_published'] === true ? false : true;
                $publishedAt = $newStatus === true ? date('Y-m-d H:i:s') : null;
                
                $updateData = [
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'content' => $post['content'],
                    'excerpt' => $post['excerpt'],
                    'category' => $post['category'],
                    'featured_image' => $post['featured_image'],
                    'is_featured' => $post['is_featured'],
                    'is_published' => $newStatus,
                    'author_name' => $post['author_name'],
                    'author_title' => $post['author_title'],
                    'author_avatar' => $post['author_avatar'],
                    'read_time' => $post['read_time'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'published_at' => $publishedAt
                ];
                
                if ($blogModel->updatePost($postId, $updateData)) {
                    $message = $newStatus === true ? 'Post published successfully' : 'Post unpublished successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating post status';
                    $messageType = 'error';
                }
            }
            break;
    }
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$posts = $blogModel->getAllPosts($page);
$totalPosts = $blogModel->getDashboardStats()['total_posts'];
$totalPages = ceil($totalPosts / ADMIN_POSTS_PER_PAGE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - Selenix Blog Admin</title>
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
                    <li><a href="admin-dashboard.php"><i class="fa-solid fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="admin-posts.php" class="active"><i class="fa-solid fa-newspaper"></i> Posts</a></li>
                    <li><a href="admin-add-post.php"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
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
                <h1>Manage Posts</h1>
                <div class="admin-actions">
                    <a href="admin-add-post.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Add New Post
                    </a>
                </div>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="admin-message <?php echo $messageType; ?>">
                    <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Posts Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($posts)): ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <div class="post-title-cell">
                                            <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                            <?php if ($post['is_featured'] === true): ?>
                                                <span class="badge featured">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category <?php echo getCategoryColor($post['category']); ?>">
                                            <?php echo getCategoryName($post['category']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td>
                                        <span class="status <?php echo $post['is_published'] === true ? 'published' : 'draft'; ?>">
                                            <?php echo $post['is_published'] === true ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate(strtotime($post['created_at']), 'M j, Y'); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="admin-edit-post.php?id=<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-secondary" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($post['is_published'] === true): ?>
                                                <a href="post.php?slug=<?php echo $post['slug']; ?>" 
                                                   target="_blank" class="btn btn-sm btn-info" title="View">
                                                    <i class="fa-solid fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_publish">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm <?php echo $post['is_published'] === true ? 'btn-warning' : 'btn-success'; ?>" 
                                                        title="<?php echo $post['is_published'] === true ? 'Unpublish' : 'Publish'; ?>">
                                                    <i class="fa-solid fa-<?php echo $post['is_published'] === true ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this post?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-cell">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-newspaper"></i>
                                        <p>No posts found. <a href="admin-add-post.php">Create your first post</a>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-section">
                    <?php echo generatePagination($page, $totalPages, 'admin-posts.php'); ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide admin messages
            const adminMessage = document.querySelector('.admin-message');
            if (adminMessage) {
                setTimeout(() => {
                    adminMessage.style.opacity = '0';
                    setTimeout(() => {
                        adminMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        });
    </script>

    <style>
        .post-title-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .badge.featured {
            background: #fbbf24;
            color: #92400e;
        }
        
        .empty-cell {
            text-align: center;
            padding: 3rem;
        }
        
        .status {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .status.published {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status.draft {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</body>
</html>
