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
if ($_GET['action'] === 'logout') {
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

// Get statistics
$stats = $pdo->query("SELECT COUNT(*) as total_downloads FROM downloads")->fetch();
$totalDownloads = $stats['total_downloads'];

$stats = $pdo->query("SELECT COUNT(DISTINCT email) as unique_emails FROM downloads")->fetch();
$uniqueEmails = $stats['unique_emails'];

// Get recent downloads
$recentDownloads = $pdo->query("
    SELECT email, download_time, ip_address 
    FROM downloads 
    ORDER BY download_time DESC 
    LIMIT 50
")->fetchAll();

// Get downloads by date
$downloadsByDate = $pdo->query("
    SELECT DATE(download_time) as date, COUNT(*) as count 
    FROM downloads 
    GROUP BY DATE(download_time) 
    ORDER BY date DESC 
    LIMIT 30
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
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalDownloads; ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $uniqueEmails; ?></div>
                <div class="stat-label">Unique Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalDownloads > 0 ? round($totalDownloads / max($uniqueEmails, 1), 1) : 0; ?></div>
                <div class="stat-label">Avg Downloads/User</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">Downloads by Day</div>
            <div class="section-content">
                <canvas id="downloadsChart" class="chart"></canvas>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">Recent Downloads</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Date/Time</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentDownloads as $download): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($download['email']); ?></td>
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
        // Downloads chart
        const ctx = document.getElementById('downloadsChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($d) { return '"' . date('M j', strtotime($d['date'])) . '"'; }, array_reverse($downloadsByDate))); ?>],
                datasets: [{
                    label: 'Downloads',
                    data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, array_reverse($downloadsByDate))); ?>],
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
                }
            }
        });
    </script>
</body>
</html>