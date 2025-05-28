<?php
// setup-database.php
// Enhanced version with detailed error checking for web server deployment

$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Selenix Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .success { color: #28a745; margin: 10px 0; }
        .error { color: #dc3545; margin: 10px 0; }
        .warning { color: #ffc107; margin: 10px 0; }
        .info { color: #17a2b8; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; }
        .credentials { background: #e9ecef; padding: 10px; font-family: monospace; border-radius: 4px; }
        h1 { color: #333; }
        h2 { color: #667eea; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Selenix Database Setup</h1>
        
        <h2>Database Credentials</h2>
        <div class="credentials">
            Host: <?php echo $host; ?><br>
            Database: <?php echo $database; ?><br>
            Username: <?php echo $username; ?><br>
            Password: <?php echo str_repeat('*', strlen($password)); ?>
        </div>

        <h2>Setup Process</h2>

        <?php
        echo "<div class='step'><strong>Step 1:</strong> Testing database connection...</div>";
        
        try {
            // Test connection
            $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div class='success'>✅ Successfully connected to database '$database'</div>";
            
            // Check if table exists
            echo "<div class='step'><strong>Step 2:</strong> Checking for existing 'downloads' table...</div>";
            
            $tableExists = false;
            try {
                $result = $pdo->query("SELECT 1 FROM downloads LIMIT 1");
                $tableExists = true;
                echo "<div class='warning'>⚠️ Table 'downloads' already exists</div>";
            } catch (PDOException $e) {
                echo "<div class='info'>ℹ️ Table 'downloads' does not exist - will create it</div>";
            }
            
            // Create table
            echo "<div class='step'><strong>Step 3:</strong> Creating 'downloads' table...</div>";
            
            $createTable = "
            CREATE TABLE IF NOT EXISTS downloads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                license_key TEXT NOT NULL,
                download_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                INDEX idx_email (email),
                INDEX idx_download_time (download_time)
            )";
            
            $pdo->exec($createTable);
            
            if ($tableExists) {
                echo "<div class='success'>✅ Table 'downloads' verified and updated</div>";
            } else {
                echo "<div class='success'>✅ Table 'downloads' created successfully</div>";
            }
            
            // Test permissions
            echo "<div class='step'><strong>Step 4:</strong> Testing database permissions...</div>";
            
            // Test INSERT
            $testStmt = $pdo->prepare("INSERT INTO downloads (email, license_key, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $testResult = $testStmt->execute(['setup-test@selenix.io', 'test_license_key_' . time(), $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 'Setup Test']);
            
            if ($testResult) {
                echo "<div class='success'>✅ INSERT permission confirmed</div>";
            } else {
                echo "<div class='error'>❌ INSERT permission failed</div>";
            }
            
            // Test SELECT
            $selectStmt = $pdo->query("SELECT COUNT(*) as count FROM downloads WHERE email LIKE 'setup-test%'");
            $count = $selectStmt->fetch()['count'];
            echo "<div class='success'>✅ SELECT permission confirmed (found $count test records)</div>";
            
            // Test DELETE
            $deleteStmt = $pdo->exec("DELETE FROM downloads WHERE email LIKE 'setup-test%'");
            echo "<div class='success'>✅ DELETE permission confirmed (removed $deleteStmt test records)</div>";
            
            // Check current data
            echo "<div class='step'><strong>Step 5:</strong> Current database status...</div>";
            
            $stats = $pdo->query("SELECT COUNT(*) as total FROM downloads")->fetch();
            echo "<div class='info'>📊 Current downloads in database: " . $stats['total'] . "</div>";
            
            if ($stats['total'] > 0) {
                $recent = $pdo->query("SELECT email, download_time FROM downloads ORDER BY download_time DESC LIMIT 1")->fetch();
                echo "<div class='info'>📅 Most recent download: " . $recent['email'] . " at " . $recent['download_time'] . "</div>";
            }
            
            echo "<div class='step'><strong>Setup Complete!</strong></div>";
            echo "<div class='success'>🎉 Database setup completed successfully!</div>";
            
            echo "<h2>Next Steps</h2>";
            echo "<ol>";
            echo "<li><a href='download.php'>Test the download page</a></li>";
            echo "<li><a href='admin.php'>Access the admin panel</a> (password: selenix2024)</li>";
            echo "<li><a href='index.html'>Visit the main website</a></li>";
            echo "</ol>";
            
            echo "<h2>System Information</h2>";
            echo "<div class='info'>";
            echo "PHP Version: " . phpversion() . "<br>";
            echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
            echo "MySQL Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
            echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database connection failed!</div>";
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
            
            echo "<h2>Troubleshooting</h2>";
            echo "<div class='warning'>";
            echo "<strong>Common issues:</strong><br>";
            echo "1. Database 'aibrainl_selenix' doesn't exist<br>";
            echo "2. User 'aibrainl_selenix' doesn't have access<br>";
            echo "3. Wrong password<br>";
            echo "4. MySQL service not running<br>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<strong>Check in cPanel:</strong><br>";
            echo "1. Go to MySQL Databases<br>";
            echo "2. Verify database exists: aibrainl_selenix<br>";
            echo "3. Check user permissions<br>";
            echo "4. Ensure user is assigned to database<br>";
            echo "</div>";
        }
        ?>
        
        <h2>Files Status</h2>
        <?php
        $requiredFiles = [
            'download.php' => 'Download system with email registration',
            'admin.php' => 'Admin panel for statistics',
            'index.html' => 'Main website homepage',
            'Selenix-win-unpackedBETA.zip' => 'Application download file'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            if (file_exists($file)) {
                echo "<div class='success'>✅ $file - $description</div>";
            } else {
                echo "<div class='error'>❌ $file - $description (MISSING)</div>";
            }
        }
        ?>
        
    </div>
</body>
</html>