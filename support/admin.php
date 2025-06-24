<?php
/**
 * Simple Admin Panel for Contact Form Submissions
 * Basic authentication and submission management
 */

require_once 'config.php';

// Simple authentication (replace with proper authentication system)
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Simple login form
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Simple hardcoded authentication (replace with database check)
        if ($username === 'admin' && $password === 'selenix2024') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Selenix Support Admin</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 50px; }
            .login-form { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { background: #667eea; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
            button:hover { background: #5a6fd8; }
            .error { color: red; margin-bottom: 10px; }
            .logo { text-align: center; margin-bottom: 30px; }
        </style>
    </head>
    <body>
        <div class="login-form">
            <div class="logo">
                <h2>Selenix Support Admin</h2>
            </div>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
                Default: admin / selenix2024
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $id = $_POST['submission_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $notes, $id]);
        $success = "Submission updated successfully";
    } catch (Exception $e) {
        $error = "Error updating submission: " . $e->getMessage();
    }
}

// Handle individual submission view
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE id = ?");
        $stmt->execute([$_GET['view']]);
        $submission = $stmt->fetch();
        
        if (!$submission) {
            $error = "Submission not found";
        }
    } catch (Exception $e) {
        $error = "Error loading submission: " . $e->getMessage();
    }
}

// Get submissions with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

try {
    $pdo = getDatabaseConnection();
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_submissions $where_clause");
    $count_stmt->execute($params);
    $total_submissions = $count_stmt->fetchColumn();
    
    // Get submissions
    $stmt = $pdo->prepare("
        SELECT * FROM contact_submissions 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
    
    $total_pages = ceil($total_submissions / $per_page);
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
    $submissions = [];
}

// If viewing individual submission
if (isset($submission) && $submission) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Submission #<?php echo $submission['id']; ?> - Selenix Support Admin</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
            .header { background: #667eea; color: white; padding: 20px; }
            .header h1 { margin: 0; }
            .header a { color: white; text-decoration: none; }
            .container { max-width: 800px; margin: 20px auto; padding: 0 20px; }
            .submission-detail { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .field-group { margin-bottom: 20px; }
            .field-label { font-weight: bold; color: #555; margin-bottom: 5px; }
            .field-value { background: #f8f9fa; padding: 10px; border-radius: 4px; border-left: 4px solid #667eea; }
            .message-content { max-height: 200px; overflow-y: auto; }
            .status-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
            .btn:hover { background: #5a6fd8; }
            .back-btn { background: #6c757d; }
            .back-btn:hover { background: #5a6268; }
            .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
            .status-new { background: #e3f2fd; color: #1976d2; }
            .status-in_progress { background: #fff3e0; color: #f57c00; }
            .status-resolved { background: #e8f5e8; color: #388e3c; }
            .status-closed { background: #f3e5f5; color: #7b1fa2; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Submission #<?php echo $submission['id']; ?></h1>
            <a href="admin.php">← Back to All Submissions</a>
        </div>

        <div class="container">
            <div class="submission-detail">
                <div class="field-group">
                    <div class="field-label">Status:</div>
                    <div class="field-value">
                        <span class="status-badge status-<?php echo $submission['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $submission['status'])); ?>
                        </span>
                    </div>
                </div>

                <div class="field-group">
                    <div class="field-label">Name:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['name']); ?></div>
                </div>

                <div class="field-group">
                    <div class="field-label">Email:</div>
                    <div class="field-value">
                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>">
                            <?php echo htmlspecialchars($submission['email']); ?>
                        </a>
                    </div>
                </div>

                <div class="field-group">
                    <div class="field-label">Subject:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['subject']); ?></div>
                </div>

                <div class="field-group">
                    <div class="field-label">Message:</div>
                    <div class="field-value message-content"><?php echo nl2br(htmlspecialchars($submission['message'])); ?></div>
                </div>

                <div class="field-group">
                    <div class="field-label">Submitted:</div>
                    <div class="field-value"><?php echo date('F j, Y \\a\\t g:i A', strtotime($submission['created_at'])); ?></div>
                </div>

                <div class="field-group">
                    <div class="field-label">IP Address:</div>
                    <div class="field-value"><?php echo htmlspecialchars($submission['ip_address'] ?? 'Unknown'); ?></div>
                </div>

                <?php if ($submission['notes']): ?>
                <div class="field-group">
                    <div class="field-label">Notes:</div>
                    <div class="field-value"><?php echo nl2br(htmlspecialchars($submission['notes'])); ?></div>
                </div>
                <?php endif; ?>

                <!-- Status Update Form -->
                <div class="status-form">
                    <h3>Update Status</h3>
                    <form method="POST">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status">
                                <option value="new" <?php echo $submission['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="in_progress" <?php echo $submission['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $submission['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $submission['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <textarea name="notes" id="notes" rows="4" placeholder="Add notes about this submission..."><?php echo htmlspecialchars($submission['notes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" name="update_status" class="btn">Update Submission</button>
                        <a href="admin.php" class="btn back-btn">Back to List</a>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selenix Support Admin - Contact Submissions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #667eea; color: white; padding: 20px; }
        .header h1 { margin: 0; }
        .header .user-info { float: right; }
        .header .user-info a { color: white; text-decoration: none; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filters form { display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { margin-bottom: 5px; font-weight: bold; }
        .filter-group input, .filter-group select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .filter-group button { background: #667eea; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .submissions-table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto; }
        .submissions-table table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .submissions-table th, .submissions-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .submissions-table th { background: #f8f9fa; font-weight: bold; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-in_progress { background: #fff3e0; color: #f57c00; }
        .status-resolved { background: #e8f5e8; color: #388e3c; }
        .status-closed { background: #f3e5f5; color: #7b1fa2; }
        .pagination { text-align: center; margin: 20px 0; }
        .pagination a, .pagination span { display: inline-block; padding: 8px 12px; margin: 0 4px; text-decoration: none; border: 1px solid #ddd; border-radius: 4px; }
        .pagination .current { background: #667eea; color: white; border-color: #667eea; }
        .pagination a:hover { background: #f8f9fa; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2em; font-weight: bold; color: #667eea; }
        .btn { padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer; margin-right: 5px; color: white; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-view { background: #17a2b8; }
        .btn-view:hover { background: #138496; }
        .no-data { text-align: center; padding: 40px; color: #666; }
        @media (max-width: 768px) {
            .filters form { flex-direction: column; }
            .stats { flex-direction: column; }
            .header .user-info { float: none; margin-top: 10px; }
            .container { padding: 0 10px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Selenix Support Admin</h1>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | 
            <a href="?logout=1">Logout</a>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <?php
            try {
                $stats = $pdo->query("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week
                    FROM contact_submissions
                ")->fetch();
            ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div>Total Submissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['new_count']; ?></div>
                    <div>New Tickets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                    <div>In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['this_week']; ?></div>
                    <div>This Week</div>
                </div>
            <?php } catch (Exception $e) { /* Ignore stats errors */ } ?>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, email, subject...">
                </div>
                <div class="filter-group">
                    <button type="submit">Filter</button>
                    <a href="admin.php" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-left: 5px;">Clear</a>
                </div>
            </form>
        </div>

        <!-- Submissions Table -->
        <div class="submissions-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <?php if ($search || $status_filter): ?>
                                    No submissions found matching your criteria.
                                <?php else: ?>
                                    No submissions found. The contact form is ready to receive submissions.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>#<?php echo $submission['id']; ?></td>
                                <td><?php echo htmlspecialchars($submission['name']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>">
                                        <?php echo htmlspecialchars($submission['email']); ?>
                                    </a>
                                </td>
                                <td title="<?php echo htmlspecialchars($submission['subject']); ?>">
                                    <?php echo htmlspecialchars(substr($submission['subject'], 0, 40) . (strlen($submission['subject']) > 40 ? '...' : '')); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $submission['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $submission['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($submission['created_at'])); ?></td>
                                <td>
                                    <a href="?view=<?php echo $submission['id']; ?>" class="btn btn-view">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">&laquo; First</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">&lsaquo; Previous</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next &rsaquo;</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Last &raquo;</a>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; color: #666; margin-top: 10px;">
                Showing <?php echo ($page - 1) * $per_page + 1; ?>-<?php echo min($page * $per_page, $total_submissions); ?> of <?php echo $total_submissions; ?> submissions
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 14px;">
            <p>Selenix Support Admin Panel | <a href="../index.html" style="color: #667eea;">Back to Website</a></p>
        </div>
    </div>
</body>
</html>
