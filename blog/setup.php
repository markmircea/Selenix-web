<?php
/**
 * Database Setup Script
 * Run this script to initialize the blog database
 */

require_once 'config.php';
require_once 'database.php';

// Set execution time limit for large operations
set_time_limit(300);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Selenix Blog</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="admin-login-container">
        <div class="login-card" style="max-width: 600px;">
            <div class="login-header">
                <h1>
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                    <span class="admin-label">Database Setup</span>
                </h1>
                <p>Initialize your blog database</p>
            </div>
            
            <div class="setup-content">
                <?php
                try {
                    echo '<div class="setup-step">';
                    echo '<h3><i class="fa-solid fa-database"></i> Connecting to Database</h3>';
                    
                    // Test database connection
                    $db = Database::getInstance();
                    echo '<p class="success"><i class="fa-solid fa-check"></i> Database connection successful!</p>';
                    
                    echo '<h3><i class="fa-solid fa-table"></i> Creating Tables</h3>';
                    
                    // Initialize tables
                    if ($db->initializeTables()) {
                        echo '<p class="success"><i class="fa-solid fa-check"></i> Database tables created successfully!</p>';
                        
                        echo '<h3><i class="fa-solid fa-seedling"></i> Inserting Sample Data</h3>';
                        
                        // Insert sample data
                        $db->insertSampleData();
                        echo '<p class="success"><i class="fa-solid fa-check"></i> Sample blog posts and data inserted!</p>';
                        
                        echo '<div class="setup-complete">';
                        echo '<h3><i class="fa-solid fa-check-circle"></i> Setup Complete!</h3>';
                        echo '<p>Your blog database has been set up successfully. You can now:</p>';
                        echo '<ul>';
                        echo '<li>View your blog at <a href="blog.php">blog.php</a></li>';
                        echo '<li>Access the admin panel at <a href="admin-login.php">admin-login.php</a></li>';
                        echo '<li>Login with username: <strong>admin</strong> and password: <strong>selenix2025!</strong></li>';
                        echo '</ul>';
                        echo '</div>';
                        
                    } else {
                        echo '<p class="error"><i class="fa-solid fa-exclamation-circle"></i> Error creating database tables!</p>';
                    }
                    
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="setup-step">';
                    echo '<h3><i class="fa-solid fa-exclamation-triangle"></i> Setup Error</h3>';
                    echo '<p class="error"><i class="fa-solid fa-exclamation-circle"></i> ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<h4>Common Solutions:</h4>';
                    echo '<ul>';
                    echo '<li>Check your database credentials in <code>config.php</code></li>';
                    echo '<li>Ensure PostgreSQL is running</li>';
                    echo '<li>Verify the database "' . DB_NAME . '" exists</li>';
                    echo '<li>Check that user "' . DB_USER . '" has proper permissions</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="setup-footer">
                <p><strong>Database Configuration:</strong></p>
                <ul>
                    <li>Host: <?php echo DB_HOST; ?></li>
                    <li>Database: <?php echo DB_NAME; ?></li>
                    <li>Username: <?php echo DB_USER; ?></li>
                    <li>Port: <?php echo DB_PORT; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .setup-content {
            text-align: left;
        }
        
        .setup-step {
            margin-bottom: 2rem;
        }
        
        .setup-step h3 {
            color: var(--heading-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .setup-step h3 i {
            color: var(--primary-color);
        }
        
        .setup-step p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .success {
            color: #065f46;
            background: #d1fae5;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }
        
        .error {
            color: #991b1b;
            background: #fee2e2;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }
        
        .setup-complete {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
        }
        
        .setup-complete h3 {
            color: #065f46;
            margin-bottom: 1rem;
        }
        
        .setup-complete ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }
        
        .setup-complete li {
            margin: 0.5rem 0;
        }
        
        .setup-complete a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .setup-complete a:hover {
            text-decoration: underline;
        }
        
        .setup-footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .setup-footer ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        
        .setup-footer li {
            margin: 0.25rem 0;
        }
        
        code {
            background: var(--light-bg);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</body>
</html>
