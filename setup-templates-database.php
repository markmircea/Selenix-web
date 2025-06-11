<?php
// setup-templates-database.php
// Database setup specifically for templates management

$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Selenix Templates Database Setup</title>
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
        <h1>🔧 Selenix Templates Database Setup</h1>
        
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
            
            // Check if templates table exists
            echo "<div class='step'><strong>Step 2:</strong> Checking for existing 'templates' table...</div>";
            
            $tableExists = false;
            try {
                $result = $pdo->query("SELECT 1 FROM templates LIMIT 1");
                $tableExists = true;
                echo "<div class='warning'>⚠️ Table 'templates' already exists</div>";
            } catch (PDOException $e) {
                echo "<div class='info'>ℹ️ Table 'templates' does not exist - will create it</div>";
            }
            
            // Create templates table
            echo "<div class='step'><strong>Step 3:</strong> Creating 'templates' table...</div>";
            
            $createTemplateTable = "
            CREATE TABLE IF NOT EXISTS templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                category VARCHAR(100) NOT NULL,
                icon VARCHAR(100) DEFAULT 'fa-solid fa-cog',
                downloads INT DEFAULT 0,
                featured BOOLEAN DEFAULT FALSE,
                premium BOOLEAN DEFAULT FALSE,
                badge VARCHAR(50) DEFAULT NULL,
                tags JSON DEFAULT NULL,
                file_path VARCHAR(500) DEFAULT NULL,
                preview_url VARCHAR(500) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
                INDEX idx_category (category),
                INDEX idx_status (status),
                INDEX idx_featured (featured),
                INDEX idx_created_at (created_at)
            )";
            
            $pdo->exec($createTemplateTable);
            
            if ($tableExists) {
                echo "<div class='success'>✅ Table 'templates' verified and updated</div>";
            } else {
                echo "<div class='success'>✅ Table 'templates' created successfully</div>";
            }
            
            // Create template_downloads table for tracking
            echo "<div class='step'><strong>Step 4:</strong> Creating 'template_downloads' table...</div>";
            
            $createDownloadsTable = "
            CREATE TABLE IF NOT EXISTS template_downloads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                template_id INT NOT NULL,
                email VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                download_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
                INDEX idx_template_id (template_id),
                INDEX idx_download_time (download_time)
            )";
            
            $pdo->exec($createDownloadsTable);
            echo "<div class='success'>✅ Table 'template_downloads' created successfully</div>";
            
            // Insert sample data only if table was newly created
            if (!$tableExists) {
                echo "<div class='step'><strong>Step 5:</strong> Inserting sample template data...</div>";
                
                $sampleTemplates = [
                    [
                        'title' => 'Product Data Scraper',
                        'description' => 'Extract product information from e-commerce websites including prices, descriptions, ratings, and more.',
                        'category' => 'data-scraping',
                        'icon' => 'fa-solid fa-table',
                        'downloads' => 2547,
                        'featured' => 1,
                        'premium' => 0,
                        'badge' => 'Featured',
                        'tags' => '["E-commerce", "Products", "Pricing"]'
                    ],
                    [
                        'title' => 'Contact Information Collector',
                        'description' => 'Gather contact information from business directories, LinkedIn, or company websites automatically.',
                        'category' => 'data-scraping',
                        'icon' => 'fa-solid fa-list',
                        'downloads' => 1823,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Lead Generation", "Business", "Contacts"]'
                    ],
                    [
                        'title' => 'Multi-Site Job Application',
                        'description' => 'Automatically fill out job applications across multiple job boards using your resume data.',
                        'category' => 'form-filling',
                        'icon' => 'fa-solid fa-keyboard',
                        'downloads' => 1356,
                        'featured' => 0,
                        'premium' => 1,
                        'badge' => 'Premium',
                        'tags' => '["Jobs", "Applications", "Career"]'
                    ],
                    [
                        'title' => 'Invoice Data Entry',
                        'description' => 'Automate entering invoice data into your accounting system from PDF or email invoices.',
                        'category' => 'form-filling',
                        'icon' => 'fa-solid fa-file-invoice',
                        'downloads' => 987,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Accounting", "Finance", "Invoicing"]'
                    ],
                    [
                        'title' => 'LinkedIn Connection Manager',
                        'description' => 'Automatically send personalized connection requests and follow-up messages on LinkedIn.',
                        'category' => 'social-media',
                        'icon' => 'fa-brands fa-linkedin',
                        'downloads' => 2134,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Networking", "Outreach", "LinkedIn"]'
                    ],
                    [
                        'title' => 'Cross-Platform Content Publisher',
                        'description' => 'Publish content across multiple social media platforms with custom formatting for each platform.',
                        'category' => 'social-media',
                        'icon' => 'fa-solid fa-share-nodes',
                        'downloads' => 1456,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Content", "Publishing", "Marketing"]'
                    ],
                    [
                        'title' => 'Price Monitor & Alerts',
                        'description' => 'Track product prices across multiple websites and receive alerts when prices drop.',
                        'category' => 'e-commerce',
                        'icon' => 'fa-solid fa-tags',
                        'downloads' => 876,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => 'New',
                        'tags' => '["Price Tracking", "Shopping", "Alerts"]'
                    ],
                    [
                        'title' => 'Inventory Manager',
                        'description' => 'Automatically update inventory across multiple e-commerce platforms when stock changes.',
                        'category' => 'e-commerce',
                        'icon' => 'fa-solid fa-cart-shopping',
                        'downloads' => 1234,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Inventory", "Stock Management", "Multi-platform"]'
                    ],
                    [
                        'title' => 'Competitor Analysis Tracker',
                        'description' => 'Monitor competitors\' websites, social media, and content for changes and new campaigns.',
                        'category' => 'marketing',
                        'icon' => 'fa-solid fa-bullhorn',
                        'downloads' => 1567,
                        'featured' => 0,
                        'premium' => 0,
                        'badge' => NULL,
                        'tags' => '["Competitor Analysis", "Research", "Monitoring"]'
                    ],
                    [
                        'title' => 'Email Campaign Manager',
                        'description' => 'Automate email campaign management across multiple platforms with analytics tracking.',
                        'category' => 'marketing',
                        'icon' => 'fa-solid fa-envelope-open-text',
                        'downloads' => 983,
                        'featured' => 0,
                        'premium' => 1,
                        'badge' => 'Premium',
                        'tags' => '["Email Marketing", "Campaigns", "Analytics"]'
                    ]
                ];
                
                $insertStmt = $pdo->prepare("
                    INSERT INTO templates (title, description, category, icon, downloads, featured, premium, badge, tags) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($sampleTemplates as $template) {
                    $insertStmt->execute([
                        $template['title'],
                        $template['description'],
                        $template['category'],
                        $template['icon'],
                        $template['downloads'],
                        $template['featured'],
                        $template['premium'],
                        $template['badge'],
                        $template['tags']
                    ]);
                }
                
                echo "<div class='success'>✅ Sample template data inserted successfully (" . count($sampleTemplates) . " templates)</div>";
            }
            
            // Test permissions
            echo "<div class='step'><strong>Step 6:</strong> Testing database permissions...</div>";
            
            // Test template operations
            $testStmt = $pdo->prepare("INSERT INTO templates (title, description, category) VALUES (?, ?, ?)");
            $testResult = $testStmt->execute(['Test Template', 'Test Description', 'test']);
            
            if ($testResult) {
                $testId = $pdo->lastInsertId();
                echo "<div class='success'>✅ INSERT permission confirmed for templates</div>";
                
                // Test UPDATE
                $updateStmt = $pdo->prepare("UPDATE templates SET downloads = downloads + 1 WHERE id = ?");
                $updateStmt->execute([$testId]);
                echo "<div class='success'>✅ UPDATE permission confirmed</div>";
                
                // Test DELETE
                $deleteStmt = $pdo->prepare("DELETE FROM templates WHERE id = ?");
                $deleteStmt->execute([$testId]);
                echo "<div class='success'>✅ DELETE permission confirmed</div>";
            } else {
                echo "<div class='error'>❌ Template INSERT permission failed</div>";
            }
            
            // Check current data
            echo "<div class='step'><strong>Step 7:</strong> Current database status...</div>";
            
            $stats = $pdo->query("SELECT COUNT(*) as total FROM templates WHERE status = 'active'")->fetch();
            echo "<div class='info'>📊 Active templates in database: " . $stats['total'] . "</div>";
            
            $categories = $pdo->query("SELECT category, COUNT(*) as count FROM templates WHERE status = 'active' GROUP BY category")->fetchAll();
            echo "<div class='info'>📂 Templates by category:</div>";
            foreach ($categories as $cat) {
                echo "<div class='info'>&nbsp;&nbsp;&nbsp;&nbsp;• " . ucfirst(str_replace('-', ' ', $cat['category'])) . ": " . $cat['count'] . "</div>";
            }
            
            echo "<div class='step'><strong>Setup Complete!</strong></div>";
            echo "<div class='success'>🎉 Templates database setup completed successfully!</div>";
            
            echo "<h2>Next Steps</h2>";
            echo "<ol>";
            echo "<li><a href='product/templates/index.html'>View the updated templates page</a></li>";
            echo "<li><a href='templates-admin.php'>Access the templates admin panel</a> (password: selenix2024)</li>";
            echo "<li><a href='admin.php'>Access the main admin panel</a></li>";
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
        }
        ?>
        
    </div>
</body>
</html>