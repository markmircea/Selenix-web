<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

requireAdmin();

$blogModel = new BlogModel();

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $commentId = intval($_POST['comment_id']);

    switch ($_POST['action']) {
        case 'approve':
            if ($blogModel->approveComment($commentId)) {
                $message = 'Comment approved successfully';
                $messageType = 'success';
            } else {
                $message = 'Error approving comment';
                $messageType = 'error';
            }
            break;

        case 'delete':
            if ($blogModel->deleteComment($commentId)) {
                $message = 'Comment deleted successfully';
                $messageType = 'success';
            } else {
                $message = 'Error deleting comment';
                $messageType = 'error';
            }
            break;

        case 'bulk_approve':
            $commentIds = $_POST['comment_ids'] ?? [];
            $approved = 0;
            foreach ($commentIds as $id) {
                if ($blogModel->approveComment(intval($id))) {
                    $approved++;
                }
            }
            $message = "Approved $approved comment(s) successfully";
            $messageType = 'success';
            break;

        case 'bulk_delete':
            $commentIds = $_POST['comment_ids'] ?? [];
            $deleted = 0;
            foreach ($commentIds as $id) {
                if ($blogModel->deleteComment(intval($id))) {
                    $deleted++;
                }
            }
            $message = "Deleted $deleted comment(s) successfully";
            $messageType = 'success';
            break;
    }
}

// Get comments with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';

// Get comments based on status filter
$comments = [];
$totalComments = 0;

if ($status === 'pending') {
    $sql = "
        SELECT c.*, p.title as post_title, p.slug as post_slug
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        WHERE c.is_approved = false
        ORDER BY c.created_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);

    $countSql = "SELECT COUNT(*) FROM comments WHERE is_approved = false";
} elseif ($status === 'approved') {
    $sql = "
        SELECT c.*, p.title as post_title, p.slug as post_slug
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        WHERE c.is_approved = true
        ORDER BY c.created_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);

    $countSql = "SELECT COUNT(*) FROM comments WHERE is_approved = true";
} else {
    $sql = "
        SELECT c.*, p.title as post_title, p.slug as post_slug
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        ORDER BY c.created_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);

    $countSql = "SELECT COUNT(*) FROM comments";
}

$db = Database::getInstance()->getConnection();
$stmt = $db->query($sql);
$comments = $stmt->fetchAll();

$stmt = $db->query($countSql);
$totalComments = $stmt->fetchColumn();
$totalPages = ceil($totalComments / ADMIN_POSTS_PER_PAGE);

// Get comment counts for filter tabs
$pendingCount = $db->query("SELECT COUNT(*) FROM comments WHERE is_approved = false")->fetchColumn();
$approvedCount = $db->query("SELECT COUNT(*) FROM comments WHERE is_approved = true")->fetchColumn();
$totalCount = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Selenix Blog Admin</title>
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
                    <li><a href="admin-subscribers.php"><i class="fa-solid fa-users"></i> Newsletter Subscribers</a></li>

                    <li class="nav-divider"></li>
                    <li><a href="../../admin.php"><i class="fa-solid fa-users"></i> Downloads Admin</a></li>
                    <li><a href="../support/admin.php"><i class="fa-solid fa-users"></i> Support Admin</a></li>

                    <li class="nav-divider"></li>

                    <li><a href="blog.php" target="_blank"><i class="fa-solid fa-external-link-alt"></i> View Blog</a></li>
                    <li><a href="admin-logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Manage Comments</h1>
                <div class="admin-actions">
                    <?php if ($pendingCount > 0): ?>
                        <span class="pending-badge">
                            <i class="fa-solid fa-clock"></i>
                            <?php echo $pendingCount; ?> pending
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($message)): ?>
                <div class="admin-message <?php echo $messageType; ?>">
                    <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="admin-comments.php?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
                    All Comments (<?php echo $totalCount; ?>)
                </a>
                <a href="admin-comments.php?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
                    Pending (<?php echo $pendingCount; ?>)
                </a>
                <a href="admin-comments.php?status=approved" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">
                    Approved (<?php echo $approvedCount; ?>)
                </a>
            </div>

            <!-- Bulk Actions -->
            <?php if (!empty($comments)): ?>
                <form method="POST" id="bulk-form">
                    <div class="bulk-actions">
                        <div class="bulk-select">
                            <label>
                                <input type="checkbox" id="select-all">
                                Select All
                            </label>
                        </div>
                        <div class="bulk-buttons">
                            <button type="submit" name="action" value="bulk_approve" class="btn btn-sm btn-success">
                                <i class="fa-solid fa-check"></i>
                                Approve Selected
                            </button>
                            <button type="submit" name="action" value="bulk_delete" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete the selected comments?')">
                                <i class="fa-solid fa-trash"></i>
                                Delete Selected
                            </button>
                        </div>
                    </div>

                    <!-- Comments List -->
                    <div class="comments-admin-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-admin-item">
                                <div class="comment-select">
                                    <input type="checkbox" name="comment_ids[]" value="<?php echo $comment['id']; ?>" class="comment-checkbox">
                                </div>

                                <div class="comment-avatar">
                                    <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($comment['email']))); ?>?s=50&d=identicon"
                                        alt="<?php echo htmlspecialchars($comment['name']); ?>">
                                </div>

                                <div class="comment-details">
                                    <div class="comment-header">
                                        <div class="comment-author">
                                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                            <span class="comment-email">&lt;<?php echo htmlspecialchars($comment['email']); ?>&gt;</span>
                                            <?php if (!empty($comment['website'])): ?>
                                                <a href="<?php echo htmlspecialchars($comment['website']); ?>" target="_blank" class="comment-website">
                                                    <i class="fa-solid fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="comment-meta">
                                            <span class="comment-date"><?php echo timeAgo(strtotime($comment['created_at'])); ?></span>
                                            <span class="comment-status <?php echo ($comment['is_approved'] === true || $comment['is_approved'] === 't' || $comment['is_approved'] === '1') ? 'approved' : 'pending'; ?>">
                                                <?php echo ($comment['is_approved'] === true || $comment['is_approved'] === 't' || $comment['is_approved'] === '1') ? 'Approved' : 'Pending'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="comment-post-link">
                                        <i class="fa-solid fa-newspaper"></i>
                                        On: <a href="post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($comment['post_title']); ?>
                                        </a>
                                    </div>

                                    <div class="comment-content">
                                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    </div>
                                </div>

                                <div class="comment-actions">
                                    <?php if ($comment['is_approved'] === false || $comment['is_approved'] === 'f' || $comment['is_approved'] === '0' || $comment['is_approved'] === 0): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-comments"></i>
                    <h3>No comments found</h3>
                    <p><?php echo $status === 'pending' ? 'No pending comments.' : ($status === 'approved' ? 'No approved comments.' : 'No comments yet.'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-section">
                    <?php
                    $baseUrl = 'admin-comments.php' . ($status !== 'all' ? '?status=' . $status : '');
                    $separator = $status !== 'all' ? '&' : '?';
                    echo str_replace('?page=', $separator . 'page=', generatePagination($page, $totalPages, $baseUrl));
                    ?>
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

            // Select all functionality
            const selectAllCheckbox = document.getElementById('select-all');
            const commentCheckboxes = document.querySelectorAll('.comment-checkbox');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    commentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // Update select all when individual checkboxes change
            commentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(commentCheckboxes).every(cb => cb.checked);
                    const noneChecked = Array.from(commentCheckboxes).every(cb => !cb.checked);

                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                    }
                });
            });

            // Bulk form submission validation
            const bulkForm = document.getElementById('bulk-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const checkedBoxes = document.querySelectorAll('.comment-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one comment.');
                    }
                });
            }
        });
    </script>

    <style>
        .pending-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .bulk-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid var(--border-color);
        }

        .bulk-select label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--heading-color);
        }

        .bulk-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .comments-admin-list {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .comment-admin-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.3s ease;
        }

        .comment-admin-item:hover {
            background: var(--light-bg);
        }

        .comment-admin-item:last-child {
            border-bottom: none;
        }

        .comment-select {
            display: flex;
            align-items: flex-start;
            padding-top: 0.5rem;
        }

        .comment-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .comment-details {
            flex: 1;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .comment-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .comment-email {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .comment-website {
            color: var(--primary-color);
            text-decoration: none;
        }

        .comment-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
            font-size: 0.9rem;
        }

        .comment-date {
            color: #6b7280;
        }

        .comment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .comment-status.approved {
            background: #d1fae5;
            color: #065f46;
        }

        .comment-status.pending {
            background: #fee2e2;
            color: #991b1b;
        }

        .comment-post-link {
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .comment-post-link a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .comment-post-link a:hover {
            text-decoration: underline;
        }

        .comment-content {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .comment-content p {
            margin: 0;
            line-height: 1.6;
        }

        .comment-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .comment-admin-item {
                flex-direction: column;
                gap: 1rem;
            }

            .comment-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .comment-actions {
                flex-direction: row;
            }

            .bulk-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .filter-tabs {
                flex-direction: column;
            }
        }
    </style>
</body>

</html>