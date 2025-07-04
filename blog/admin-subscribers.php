<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

requireAdmin();

$blogModel = new BlogModel();

// Handle subscriber actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'export_csv':
            // Export subscribers to CSV
            $subscribers = $blogModel->getNewsletterSubscribers(1, 10000); // Get all subscribers
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email', 'Subscribed Date', 'Status']);
            
            foreach ($subscribers as $subscriber) {
                fputcsv($output, [
                    $subscriber['email'],
                    formatDate(strtotime($subscriber['subscribed_at']), 'Y-m-d H:i:s'),
                    $subscriber['is_active'] === 't' ? 'Active' : 'Inactive'
                ]);
            }
            
            fclose($output);
            exit;
            
        case 'bulk_delete':
            $emails = $_POST['subscriber_emails'] ?? [];
            $deleted = 0;
            
            foreach ($emails as $email) {
                $email = sanitizeInput($email);
                $sql = "DELETE FROM newsletter_subscribers WHERE email = :email";
                $stmt = Database::getInstance()->getConnection()->prepare($sql);
                if ($stmt->execute(['email' => $email])) {
                    $deleted++;
                }
            }
            
            $message = "Deleted $deleted subscriber(s) successfully";
            $messageType = 'success';
            break;
    }
}

// Get subscribers with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';

// Get subscribers based on status filter
$subscribers = [];
$totalSubscribers = 0;

$db = Database::getInstance()->getConnection();

if ($status === 'active') {
    $sql = "
        SELECT email, subscribed_at, is_active, unsubscribed_at
        FROM newsletter_subscribers
        WHERE is_active = true
        ORDER BY subscribed_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);
    
    $countSql = "SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = true";
} elseif ($status === 'inactive') {
    $sql = "
        SELECT email, subscribed_at, is_active, unsubscribed_at
        FROM newsletter_subscribers
        WHERE is_active = false
        ORDER BY unsubscribed_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);
    
    $countSql = "SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = false";
} else {
    $sql = "
        SELECT email, subscribed_at, is_active, unsubscribed_at
        FROM newsletter_subscribers
        ORDER BY subscribed_at DESC
        LIMIT " . ADMIN_POSTS_PER_PAGE . " OFFSET " . (($page - 1) * ADMIN_POSTS_PER_PAGE);
    
    $countSql = "SELECT COUNT(*) FROM newsletter_subscribers";
}

$stmt = $db->query($sql);
$subscribers = $stmt->fetchAll();

$stmt = $db->query($countSql);
$totalSubscribers = $stmt->fetchColumn();
$totalPages = ceil($totalSubscribers / ADMIN_POSTS_PER_PAGE);

// Get subscriber counts for filter tabs
$activeCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = true")->fetchColumn();
$inactiveCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = false")->fetchColumn();
$totalCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();

// Get recent subscription stats
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$thisMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));

$todayCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE(subscribed_at) = '$today'")->fetchColumn();
$yesterdayCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE(subscribed_at) = '$yesterday'")->fetchColumn();
$thisMonthCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE_TRUNC('month', subscribed_at) = '$thisMonth-01'")->fetchColumn();
$lastMonthCount = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE_TRUNC('month', subscribed_at) = '$lastMonth-01'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers - Selenix Blog Admin</title>
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
                <h1>Newsletter Subscribers</h1>
                <div class="admin-actions">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="export_csv">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fa-solid fa-download"></i>
                            Export CSV
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="admin-message <?php echo $messageType; ?>">
                    <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="subscriber-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $totalCount; ?></h3>
                        <p>Total Subscribers</p>
                        <div class="stat-detail">
                            <span class="active"><?php echo $activeCount; ?> Active</span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $todayCount; ?></h3>
                        <p>Today</p>
                        <div class="stat-detail">
                            <span class="info">Yesterday: <?php echo $yesterdayCount; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $thisMonthCount; ?></h3>
                        <p>This Month</p>
                        <div class="stat-detail">
                            <span class="info">Last month: <?php echo $lastMonthCount; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $activeCount > 0 ? round(($activeCount / $totalCount) * 100, 1) : 0; ?>%</h3>
                        <p>Active Rate</p>
                        <div class="stat-detail">
                            <span class="active">Engagement metric</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="admin-subscribers.php?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
                    All Subscribers (<?php echo $totalCount; ?>)
                </a>
                <a href="admin-subscribers.php?status=active" class="filter-tab <?php echo $status === 'active' ? 'active' : ''; ?>">
                    Active (<?php echo $activeCount; ?>)
                </a>
                <a href="admin-subscribers.php?status=inactive" class="filter-tab <?php echo $status === 'inactive' ? 'active' : ''; ?>">
                    Inactive (<?php echo $inactiveCount; ?>)
                </a>
            </div>
            
            <!-- Subscribers Table -->
            <?php if (!empty($subscribers)): ?>
                <form method="POST" id="bulk-form">
                    <div class="bulk-actions">
                        <div class="bulk-select">
                            <label>
                                <input type="checkbox" id="select-all">
                                Select All
                            </label>
                        </div>
                        <div class="bulk-buttons">
                            <button type="submit" name="action" value="bulk_delete" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete the selected subscribers?')">
                                <i class="fa-solid fa-trash"></i>
                                Delete Selected
                            </button>
                        </div>
                    </div>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-table">
                                    </th>
                                    <th>Email Address</th>
                                    <th>Status</th>
                                    <th>Subscribed Date</th>
                                    <th>Unsubscribed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscribers as $subscriber): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="subscriber_emails[]" 
                                                   value="<?php echo htmlspecialchars($subscriber['email']); ?>" 
                                                   class="subscriber-checkbox">
                                        </td>
                                        <td>
                                            <div class="subscriber-email">
                                                <i class="fa-solid fa-envelope"></i>
                                                <?php echo htmlspecialchars($subscriber['email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status <?php echo $subscriber['is_active'] === 't' ? 'active' : 'inactive'; ?>">
                                                <?php echo $subscriber['is_active'] === 't' ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate(strtotime($subscriber['subscribed_at']), 'M j, Y g:i A'); ?></td>
                                        <td>
                                            <?php if ($subscriber['unsubscribed_at']): ?>
                                                <?php echo formatDate(strtotime($subscriber['unsubscribed_at']), 'M j, Y g:i A'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-users"></i>
                    <h3>No subscribers found</h3>
                    <p><?php echo $status === 'active' ? 'No active subscribers.' : ($status === 'inactive' ? 'No inactive subscribers.' : 'No newsletter subscribers yet.'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-section">
                    <?php
                    $baseUrl = 'admin-subscribers.php' . ($status !== 'all' ? '?status=' . $status : '');
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
            const selectAllTableCheckbox = document.getElementById('select-all-table');
            const subscriberCheckboxes = document.querySelectorAll('.subscriber-checkbox');
            
            function updateSelectAll() {
                const allChecked = Array.from(subscriberCheckboxes).every(cb => cb.checked);
                const noneChecked = Array.from(subscriberCheckboxes).every(cb => !cb.checked);
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                }
                
                if (selectAllTableCheckbox) {
                    selectAllTableCheckbox.checked = allChecked;
                    selectAllTableCheckbox.indeterminate = !allChecked && !noneChecked;
                }
            }
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    subscriberCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    if (selectAllTableCheckbox) {
                        selectAllTableCheckbox.checked = this.checked;
                    }
                });
            }
            
            if (selectAllTableCheckbox) {
                selectAllTableCheckbox.addEventListener('change', function() {
                    subscriberCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = this.checked;
                    }
                });
            }
            
            // Update select all when individual checkboxes change
            subscriberCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAll);
            });
            
            // Bulk form submission validation
            const bulkForm = document.getElementById('bulk-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const checkedBoxes = document.querySelectorAll('.subscriber-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one subscriber.');
                    }
                });
            }
        });
    </script>

    <style>
        .subscriber-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .subscriber-email {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .subscriber-email i {
            color: #6b7280;
        }
        
        .status.active {
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .status.inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .text-muted {
            color: #6b7280;
            font-style: italic;
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
        
        .data-table {
            border-radius: 0 0 12px 12px;
        }
        
        @media (max-width: 768px) {
            .subscriber-stats {
                grid-template-columns: 1fr;
            }
            
            .bulk-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .filter-tabs {
                flex-direction: column;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
        }
    </style>
</body>
</html>
