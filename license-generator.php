<?php
// license-generator.php
// Generates encrypted license keys for Selenix app downloads

// Generated encryption keys - keep these secret!
$SECRET_KEY = 'b53190786ae9a82abf52f8d9094012ee1bd1900bd48682a3c3806cc380258ce6';
$IV = '2ba22e6c427fff9073c4b1cecd75c6b3';
$HMAC_KEY = '74518ca45f74fe5fc2dfa8f5e235a14142b8ed07e41d9d65476622d54ce1753f';

class LicenseGenerator {
    private $secretKey;
    private $iv;
    private $hmacKey;
    
    public function __construct($secretKey, $iv, $hmacKey) {
        $this->secretKey = hex2bin($secretKey);
        $this->iv = hex2bin($iv);
        $this->hmacKey = hex2bin($hmacKey);
    }
    
    /**
     * Generate an encrypted license key with expiry date 1 year from now
     * @return string Encrypted license key in format: base64(encrypted):hmac
     */
    public function generateLicense() {
        // Create expiry date 1 year from now
        $expiryDate = new DateTime();
        $expiryDate->add(new DateInterval('P1Y')); // Add 1 year
        
        // Format as ddmmyy (same format your app expects)
        $day = $expiryDate->format('d');
        $month = $expiryDate->format('m');
        $year = $expiryDate->format('y');
        $dateString = $day . $month . $year;
        
        // Log for debugging (remove in production)
        error_log("Generating license for date: " . $dateString . " (expires: " . $expiryDate->format('Y-m-d') . ")");
        
        // Encrypt the date string
        $encrypted = openssl_encrypt(
            $dateString,
            'aes-256-cbc',
            $this->secretKey,
            OPENSSL_RAW_DATA,
            $this->iv
        );
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed: ' . openssl_error_string());
        }
        
        // Convert to Base64
        $encryptedBase64 = base64_encode($encrypted);
        
        // Generate HMAC for tamper protection
        $hmac = hash_hmac('sha256', $encryptedBase64, $this->hmacKey);
        
        // Return in format: encrypted:hmac
        return $encryptedBase64 . ':' . $hmac;
    }
    
    /**
     * Generate a license and return it as a downloadable file
     */
    public function downloadLicense() {
        try {
            $licenseKey = $this->generateLicense();
            
            // Set headers for file download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="license.txt"');
            header('Content-Length: ' . strlen($licenseKey));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the license key
            echo $licenseKey;
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Error generating license: ' . $e->getMessage();
            error_log('License generation error: ' . $e->getMessage());
            exit;
        }
    }
    
    /**
     * Test the encryption by generating and displaying a license
     */
    public function testLicense() {
        try {
            $licenseKey = $this->generateLicense();
            
            echo '<h2>Test License Generated</h2>';
            echo '<p><strong>Encrypted License Key:</strong></p>';
            echo '<textarea style="width: 100%; height: 100px;">' . htmlspecialchars($licenseKey) . '</textarea>';
            echo '<p><strong>Length:</strong> ' . strlen($licenseKey) . ' characters</p>';
            echo '<p><strong>Format:</strong> base64(encrypted):hmac</p>';
            
            // Show expiry date
            $expiryDate = new DateTime();
            $expiryDate->add(new DateInterval('P1Y'));
            echo '<p><strong>Expires:</strong> ' . $expiryDate->format('Y-m-d') . '</p>';
            
        } catch (Exception $e) {
            echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}

// Handle the request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'download':
        $generator = new LicenseGenerator($SECRET_KEY, $IV, $HMAC_KEY);
        $generator->downloadLicense();
        break;
        
    case 'test':
        $generator = new LicenseGenerator($SECRET_KEY, $IV, $HMAC_KEY);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>License Generator Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; }
        textarea { font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Selenix License Generator - Test</h1>';
        $generator->testLicense();
        echo '<p><a href="?action=download">Download License File</a> | <a href="./">Back to Main</a></p>
    </div>
</body>
</html>';
        break;
        
    default:
        // Show simple interface
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Selenix License Generator</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px; 
            background: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            background: white; 
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 10px 10px 0;
        }
        .btn:hover { background: #005a87; }
        .test-btn { background: #28a745; }
        .test-btn:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Selenix License Generator</h1>
        <p>Generate encrypted license keys for Selenix app downloads.</p>
        
        <h3>Options:</h3>
        <a href="?action=download" class="btn">📥 Download License File</a>
        <a href="?action=test" class="btn test-btn">🧪 Test Generator</a>
        
        <h3>How it works:</h3>
        <ul>
            <li>Each license is valid for 1 year from generation</li>
            <li>License keys are encrypted using AES-256-CBC</li>
            <li>HMAC signature prevents tampering</li>
            <li>Compatible with your Selenix app</li>
        </ul>
        
        <p><small>Place the downloaded <code>license.txt</code> file in the same directory as your Selenix app.</small></p>
    </div>
</body>
</html>';
        break;
}
?>