<?php
// admin.php
// Simple admin panel to view download statistics

// Basic password protection (change this!)
$ADMIN_PASSWORD = 'selenix2024';

session_start();

// Check authentication
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['password'] ?? '' === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Database connection
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login - Selenix</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            input[type="password"] { width: 200px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        </style>
    </head>
    <body>
        <form method="POST" class="login-form">
            <h2>Selenix Admin</h2>
            <div>
                <input type="password" name="password" placeholder="Admin Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
}

// Handle date filtering
$dateFilter = $_GET['date_filter'] ?? '30';
$startDate = date('Y-m-d', strtotime("-{$dateFilter} days"));

// Get software download statistics
$stats = $pdo->query("SELECT COUNT(*) as total_downloads FROM downloads")->fetch();
$totalDownloads = $stats['total_downloads'];

$stats = $pdo->query("SELECT COUNT(DISTINCT email) as unique_emails FROM downloads")->fetch();
$uniqueEmails = $stats['unique_emails'];

// Get platform statistics
$platformStats = $pdo->query("
    SELECT 
        COALESCE(platform, 'windows') as platform, 
        COUNT(*) as count 
    FROM downloads 
    GROUP BY COALESCE(platform, 'windows')
    ORDER BY count DESC
")->fetchAll();

$windowsDownloads = 0;
$macDownloads = 0;
foreach ($platformStats as $stat) {
    if ($stat['platform'] === 'windows') {
        $windowsDownloads = $stat['count'];
    } elseif ($stat['platform'] === 'mac') {
        $macDownloads = $stat['count'];
    }
}

// Get template download statistics
$templateStats = $pdo->query("SELECT COUNT(*) as total_downloads FROM template_downloads")->fetch();
$totalTemplateDownloads = $templateStats['total_downloads'];

$templateStats = $pdo->query("SELECT COUNT(DISTINCT email) as unique_emails FROM template_downloads WHERE email IS NOT NULL")->fetch();
$uniqueTemplateEmails = $templateStats['unique_emails'];

// Get recent software downloads
$recentDownloads = $pdo->query("
    SELECT email, download_time, ip_address, COALESCE(platform, 'windows') as platform
    FROM downloads 
    WHERE DATE(download_time) >= '$startDate'
    ORDER BY download_time DESC 
    LIMIT 50
")->fetchAll();

// Get recent template downloads
$recentTemplateDownloads = $pdo->query("
    SELECT t.title, td.email, td.download_time, td.ip_address 
    FROM template_downloads td 
    JOIN templates t ON td.template_id = t.id 
    WHERE DATE(td.download_time) >= '$startDate'
    ORDER BY td.download_time DESC 
    LIMIT 50
")->fetchAll();

// Get software downloads by date
$downloadsByDate = $pdo->query("
    SELECT DATE(download_time) as date, COUNT(*) as count 
    FROM downloads 
    WHERE DATE(download_time) >= '$startDate'
    GROUP BY DATE(download_time) 
    ORDER BY date ASC
")->fetchAll();

// Get template downloads by date
$templateDownloadsByDate = $pdo->query("
    SELECT DATE(download_time) as date, COUNT(*) as count 
    FROM template_downloads 
    WHERE DATE(download_time) >= '$startDate'
    GROUP BY DATE(download_time) 
    ORDER BY date ASC
")->fetchAll();

// Get top downloaded templates
$topTemplates = $pdo->query("
    SELECT t.title, COUNT(td.id) as download_count 
    FROM templates t 
    LEFT JOIN template_downloads td ON t.id = td.template_id 
    WHERE DATE(td.download_time) >= '$startDate' OR td.download_time IS NULL
    GROUP BY t.id, t.title 
    ORDER BY download_count DESC 
    LIMIT 10
")->fetchAll();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Selenix Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .header { background: #667eea; color: white; padding: 20px; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; margin-top: 5px; }
        .section { background: white; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee; font-weight: bold; border-radius: 8px 8px 0 0; }
        .section-content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        .logout { float: right; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 4px; text-decoration: none; color: white; }
        .chart { height: 300px; margin: 20px 0; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <h1>Selenix Admin Panel</h1>
        <a href="?action=logout" class="logout">Logout</a>
    </div>
    
    <div class="container">
        <!-- Date Filter -->
        <div class="section">
            <div class="section-header">Date Filter</div>
            <div class="section-content">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <label for="date_filter">Show data for last:</label>
                    <select name="date_filter" id="date_filter" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="7" <?php echo $dateFilter == '7' ? 'selected' : ''; ?>>7 days</option>
                        <option value="30" <?php echo $dateFilter == '30' ? 'selected' : ''; ?>>30 days</option>
                        <option value="90" <?php echo $dateFilter == '90' ? 'selected' : ''; ?>>90 days</option>
                        <option value="365" <?php echo $dateFilter == '365' ? 'selected' : ''; ?>>1 year</option>
                        <option value="9999" <?php echo $dateFilter == '9999' ? 'selected' : ''; ?>>All time</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Software Downloads Stats -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #667eea; margin-bottom: 20px;">📥 Software Downloads</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalDownloads; ?></div>
                    <div class="stat-label">Total Software Downloads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $uniqueEmails; ?></div>
                    <div class="stat-label">Unique Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalDownloads > 0 ? round($totalDownloads / max($uniqueEmails, 1), 1) : 0; ?></div>
                    <div class="stat-label">Avg Downloads/User</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $windowsDownloads; ?></div>
                    <div class="stat-label">Windows Downloads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $macDownloads; ?></div>
                    <div class="stat-label">Mac Downloads</div>
                </div>
            </div>
        </div>

        <!-- Template Downloads Stats -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #28a745; margin-bottom: 20px;">📋 Template Downloads</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalTemplateDownloads; ?></div>
                    <div class="stat-label">Total Template Downloads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $uniqueTemplateEmails; ?></div>
                    <div class="stat-label">Unique Template Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalTemplateDownloads > 0 ? round($totalTemplateDownloads / max($uniqueTemplateEmails, 1), 1) : 0; ?></div>
                    <div class="stat-label">Avg Template Downloads/User</div>
                </div>
            </div>
        </div>
        
        <!-- Platform Distribution Chart -->
        <div class="section">
            <div class="section-header">💻 Platform Distribution</div>
            <div class="section-content">
                <canvas id="platformChart" class="chart"></canvas>
            </div>
        </div>
        
        <!-- Software Downloads Chart -->
        <div class="section">
            <div class="section-header">📥 Software Downloads by Day</div>
            <div class="section-content">
                <canvas id="softwareDownloadsChart" class="chart"></canvas>
            </div>
        </div>

        <!-- Template Downloads Chart -->
        <div class="section">
            <div class="section-header">📋 Template Downloads by Day</div>
            <div class="section-content">
                <canvas id="templateDownloadsChart" class="chart"></canvas>
            </div>
        </div>

        <!-- Top Downloaded Templates -->
        <div class="section">
            <div class="section-header">🏆 Top Downloaded Templates</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topTemplates as $template): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($template['title']); ?></td>
                            <td><?php echo number_format($template['download_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Software Downloads -->
        <div class="section">
            <div class="section-header">📥 Recent Software Downloads</div>
            <div class="section-content">
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
                                    <span style="color: #007AFF;">🍎 Mac</span>
                                <?php else: ?>
                                    <span style="color: #0078D4;">🪟 Windows</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($download['download_time'])); ?></td>
                            <td><?php echo htmlspecialchars($download['ip_address']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Template Downloads -->
        <div class="section">
            <div class="section-header">📋 Recent Template Downloads</div>
            <div class="section-content">
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
    </div>
    
    <script>
        // Platform Distribution Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformChart = new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: ['Windows', 'Mac'],
                datasets: [{
                    data: [<?php echo $windowsDownloads; ?>, <?php echo $macDownloads; ?>],
                    backgroundColor: ['#0078D4', '#007AFF'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Downloads by Platform'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Software Downloads Chart
        const softwareCtx = document.getElementById('softwareDownloadsChart').getContext('2d');
        const softwareChart = new Chart(softwareCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($d) { return '"' . date('M j', strtotime($d['date'])) . '"'; }, $downloadsByDate)); ?>],
                datasets: [{
                    label: 'Software Downloads',
                    data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $downloadsByDate)); ?>],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Software Downloads Over Time'
                    }
                }
            }
        });

        // Template Downloads Chart
        const templateCtx = document.getElementById('templateDownloadsChart').getContext('2d');
        const templateChart = new Chart(templateCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($d) { return '"' . date('M j', strtotime($d['date'])) . '"'; }, $templateDownloadsByDate)); ?>],
                datasets: [{
                    label: 'Template Downloads',
                    data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $templateDownloadsByDate)); ?>],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Template Downloads Over Time'
                    }
                }
            }
        });
    </script>
</body>
</html>
