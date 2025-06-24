<?php
/**
 * Contact Form Database Setup for Selenix Website
 * Run this file once to create the database and tables for the contact form system
 */

// Configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',  // Change this to your MySQL username
    'password' => '',      // Change this to your MySQL password
    'database' => 'selenix_contact'
];

// Function to create database connection
function createConnection($host, $username, $password, $database = null) {
    try {
        $dsn = "mysql:host=$host" . ($database ? ";dbname=$database" : "") . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Connection failed: " . $e->getMessage());
    }
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selenix Contact Form Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .logo {
            font-size: 2em;
            color: #667eea;
            margin-bottom: 10px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #667eea;
            background: #f8f9ff;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
            color: #155724;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
            color: #856404;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin: 10px 0;
            border: 1px solid #e9ecef;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover {
            background: #5a6fd8;
        }
        button:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .config-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📧 Selenix</div>
            <h1>Contact Form Database Setup</h1>
            <p>This wizard will create the database and tables needed for your contact form system.</p>
        </div>

<?php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['host'] = $_POST['host'] ?? 'localhost';
    $config['username'] = $_POST['username'] ?? 'root';
    $config['password'] = $_POST['password'] ?? '';
    $config['database'] = $_POST['database'] ?? 'selenix_contact';
    
    $allSuccess = true;
    
    try {
        // Step 1: Connect to MySQL server (without database)
        echo "<div class='step'>🔌 Connecting to MySQL server ({$config['host']})...</div>";
        flush();
        
        $pdo = createConnection($config['host'], $config['username'], $config['password']);
        echo "<div class='step success'>✅ Successfully connected to MySQL server</div>";
        
        // Step 2: Create database
        echo "<div class='step'>🗄️ Creating database '{$config['database']}'...</div>";
        flush();
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<div class='step success'>✅ Database '{$config['database']}' created successfully</div>";
        
        // Step 3: Connect to the new database
        echo "<div class='step'>🔗 Connecting to database...</div>";
        flush();
        
        $pdo = createConnection($config['host'], $config['username'], $config['password'], $config['database']);
        echo "<div class='step success'>✅ Connected to database '{$config['database']}'</div>";
        
        // Step 4: Create contact_submissions table
        echo "<div class='step'>📋 Creating contact_submissions table...</div>";
        flush();
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_number VARCHAR(20) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(500) NOT NULL,
                message TEXT NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
                priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
                assigned_to VARCHAR(255) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_ticket_number (ticket_number),
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at),
                INDEX idx_priority (priority)
            ) ENGINE=InnoDB
        ");
        echo "<div class='step success'>✅ Table 'contact_submissions' created successfully</div>";
        
        // Step 5: Create support_users table
        echo "<div class='step'>👥 Creating support_users table...</div>";
        flush();
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS support_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(255),
                role ENUM('admin', 'support', 'viewer') DEFAULT 'support',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        echo "<div class='step success'>✅ Table 'support_users' created successfully</div>";
        
        // Step 6: Insert default admin user
        echo "<div class='step'>👤 Creating default admin user...</div>";
        flush();
        
        // Check if admin user already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $defaultPassword = 'selenix2024';
            $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO support_users (username, email, password_hash, full_name, role) 
                VALUES ('admin', 'admin@selenix.io', ?, 'System Administrator', 'admin')
            ");
            $stmt->execute([$passwordHash]);
            
            echo "<div class='step success'>✅ Default admin user created</div>";
            echo "<div class='step warning'>
                ⚠️ <strong>Important:</strong> Default admin credentials:<br>
                Username: <code>admin</code><br>
                Password: <code>$defaultPassword</code><br>
                <strong>Please change these credentials immediately after first login!</strong>
            </div>";
        } else {
            echo "<div class='step warning'>⚠️ Admin user already exists, skipping creation</div>";
        }
        
        // Step 7: Create view for reporting
        echo "<div class='step'>📊 Creating reporting view...</div>";
        flush();
        
        $pdo->exec("
            CREATE OR REPLACE VIEW contact_submissions_summary AS
            SELECT 
                id,
                ticket_number,
                name,
                email,
                subject,
                LEFT(message, 100) as message_preview,
                status,
                priority,
                assigned_to,
                created_at,
                CASE 
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Today'
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'This Week'
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 'This Month'
                    ELSE 'Older'
                END as time_category
            FROM contact_submissions
            ORDER BY created_at DESC
        ");
        echo "<div class='step success'>✅ Reporting view created successfully</div>";
        
        // Step 8: Update config.php file
        echo "<div class='step'>⚙️ Updating configuration file...</div>";
        flush();
        
        $configContent = "<?php
/**
 * Database Configuration for Selenix Contact Form
 * Auto-generated by setup script on " . date('Y-m-d H:i:s') . "
 * 
 * IMPORTANT: 
 * 1. This file contains your database credentials
 * 2. Make sure this file is not accessible via web browser (place outside web root or use .htaccess)
 * 3. Use environment variables in production for better security
 */

// Database configuration
define('DB_HOST', '{$config['host']}');
define('DB_NAME', '{$config['database']}');
define('DB_USER', '{$config['username']}');
define('DB_PASS', '{$config['password']}');
define('DB_CHARSET', 'utf8mb4');

// Email configuration
define('SUPPORT_EMAIL', 'support@selenix.io');
define('FROM_EMAIL', 'noreply@selenix.io');
define('FROM_NAME', 'Selenix Support');

// Application settings
define('SITE_URL', 'https://selenix.io');
define('ADMIN_EMAIL', 'admin@selenix.io');

/**
 * Create database connection
 * @return PDO Database connection
 */
function getDatabaseConnection() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;
            \$options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        } catch (PDOException \$e) {
            error_log(\"Database connection failed: \" . \$e->getMessage());
            throw new Exception(\"Database connection failed\");
        }
    }
    
    return \$pdo;
}

/**
 * Test database connection
 * @return bool True if connection successful
 */
function testDatabaseConnection() {
    try {
        \$pdo = getDatabaseConnection();
        \$pdo->query(\"SELECT 1\");
        return true;
    } catch (Exception \$e) {
        return false;
    }
}
?>";
        
        if (file_put_contents('support/config.php', $configContent)) {
            echo "<div class='step success'>✅ Configuration file updated successfully</div>";
        } else {
            echo "<div class='step warning'>⚠️ Could not update config.php automatically. Please update it manually.</div>";
        }
        
        // Step 9: Test the setup
        echo "<div class='step'>🧪 Testing database connection...</div>";
        flush();
        
        $testPdo = createConnection($config['host'], $config['username'], $config['password'], $config['database']);
        $testResult = $testPdo->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
        echo "<div class='step success'>✅ Database connection test successful! Tables are ready.</div>";
        
        // Final success message
        echo "<div class='step success'>
            🎉 <strong>Contact Form Database Setup Completed Successfully!</strong><br><br>
            Your contact form database is now ready to use. Here's what was created:<br>
            • Database: <code>{$config['database']}</code><br>
            • Table: <code>contact_submissions</code> (for storing form submissions)<br>
            • Table: <code>support_users</code> (for admin users)<br>
            • View: <code>contact_submissions_summary</code> (for reporting)<br>
            • Configuration file: <code>support/config.php</code>
        </div>";
        
        echo "<div class='step warning'>
            <strong>Next Steps:</strong><br>
            1. Test your contact form at: <a href='support/index.html' target='_blank'>support/index.html</a><br>
            2. Access the admin panel at: <a href='support/admin.php' target='_blank'>support/admin.php</a><br>
            3. Change the default admin password<br>
            4. Update email settings in <code>support/config.php</code><br>
            5. Delete or secure this setup file: <code>setup-contact-database.php</code>
        </div>";
        
    } catch (Exception $e) {
        $allSuccess = false;
        echo "<div class='step error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='step error'>Setup failed. Please check your database credentials and try again.</div>";
    }
} else {
    // Show configuration form
    ?>
    <div class="step">
        <h3>📋 Contact Form Database Configuration</h3>
        <p>Please enter your MySQL database credentials below. The setup will create the database and tables automatically for the contact form system.</p>
    </div>

    <form method="POST" class="config-section">
        <div class="form-group">
            <label for="host">MySQL Host:</label>
            <input type="text" id="host" name="host" value="localhost" required>
            <small>Usually 'localhost' for local development</small>
        </div>

        <div class="form-group">
            <label for="username">MySQL Username:</label>
            <input type="text" id="username" name="username" value="root" required>
            <small>Your MySQL username (usually 'root' for local development)</small>
        </div>

        <div class="form-group">
            <label for="password">MySQL Password:</label>
            <input type="password" id="password" name="password" value="">
            <small>Your MySQL password (leave empty if no password is set)</small>
        </div>

        <div class="form-group">
            <label for="database">Database Name:</label>
            <input type="text" id="database" name="database" value="selenix_contact" required>
            <small>Name for your contact form database (will be created if it doesn't exist)</small>
        </div>

        <button type="submit">📧 Create Contact Form Database</button>
    </form>

    <div class="step warning">
        <strong>⚠️ Before you start:</strong><br>
        1. Make sure MySQL server is running<br>
        2. Ensure the MySQL user has permission to create databases<br>
        3. Backup any existing data if overwriting<br>
        4. This script will create/update the <code>support/config.php</code> file
    </div>

    <div class="step">
        <h3>🛠️ What this setup will create:</h3>
        <ul>
            <li><strong>Database:</strong> selenix_contact (or your custom name)</li>
            <li><strong>Table:</strong> contact_submissions - Stores all contact form submissions</li>
            <li><strong>Table:</strong> support_users - Admin users for managing submissions</li>
            <li><strong>View:</strong> contact_submissions_summary - For easy reporting</li>
            <li><strong>Config:</strong> support/config.php - Database connection settings</li>
            <li><strong>Admin User:</strong> Default login credentials for admin panel</li>
        </ul>
    </div>

    <div class="step">
        <h3>📧 Contact Form Features:</h3>
        <ul>
            <li><strong>Database Storage:</strong> All submissions stored securely in MySQL</li>
            <li><strong>Email Notifications:</strong> Automatic email alerts to support team</li>
            <li><strong>Auto-Reply:</strong> Confirmation emails sent to users with ticket numbers</li>
            <li><strong>Spam Protection:</strong> Basic keyword filtering and duplicate prevention</li>
            <li><strong>Admin Panel:</strong> Web interface to manage submissions and track status</li>
            <li><strong>Status Tracking:</strong> New, In Progress, Resolved, Closed</li>
            <li><strong>Search & Filter:</strong> Find submissions quickly by status, email, etc.</li>
            <li><strong>Mobile Responsive:</strong> Works perfectly on all devices</li>
        </ul>
    </div>
    <?php
}
?>

    </div>
</body>
</html>
