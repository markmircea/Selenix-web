<?php
// download.php
// Enhanced license generator with email registration and file download
// Version 2.2 - With loading animation and fixed admin warning

// Database configuration
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

// NOTE: You may need to add a 'platform' column to your downloads table:
// ALTER TABLE downloads ADD COLUMN platform VARCHAR(10) DEFAULT 'windows';

// Generated encryption keys - keep these secret!
$SECRET_KEY = 'b53190786ae9a82abf52f8d9094012ee1bd1900bd48682a3c3806cc380258ce6';
$IV = '2ba22e6c427fff9073c4b1cecd75c6b3';
$HMAC_KEY = '74518ca45f74fe5fc2dfa8f5e235a14142b8ed07e41d9d65476622d54ce1753f';

// Files to download
$DOWNLOAD_FILES = [
    'windows' => 'Selenix-win-unpackedBETA.zip',
    'mac' => 'Selenix-mac-universalBETA.zip'
];

class SelenixDownloader {
    private $pdo;
    private $secretKey;
    private $iv;
    private $hmacKey;
    private $downloadFiles;
    
    public function __construct($host, $username, $password, $database, $secretKey, $iv, $hmacKey, $downloadFiles) {
        // Connect to database
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
        
        // Set encryption keys
        $this->secretKey = hex2bin($secretKey);
        $this->iv = hex2bin($iv);
        $this->hmacKey = hex2bin($hmacKey);
        $this->downloadFiles = $downloadFiles;
    }
    
    /**
     * Validate email address
     */
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check if download file exists
     */
    private function checkDownloadFile($platform) {
        $downloadFile = $this->downloadFiles[$platform] ?? null;
        if (!$downloadFile || !file_exists($downloadFile)) {
            throw new Exception("Download file not found for platform: {$platform}");
        }
        return $downloadFile;
    }
    
    /**
     * Detect platform from user agent or form input
     */
    private function detectPlatform() {
        // Check if platform is explicitly provided in POST data
        if (isset($_POST['platform']) && in_array($_POST['platform'], ['windows', 'mac'])) {
            return $_POST['platform'];
        }
        
        // Auto-detect from user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (stripos($userAgent, 'mac') !== false || stripos($userAgent, 'darwin') !== false) {
            return 'mac';
        }
        
        // Default to Windows
        return 'windows';
    }
    
    /**
     * Generate an encrypted license key
     */
    private function generateLicense() {
        // Create expiry date 1 year from now
        $expiryDate = new DateTime();
        $expiryDate->add(new DateInterval('P1Y'));
        
        // Format as ddmmyy
        $day = $expiryDate->format('d');
        $month = $expiryDate->format('m');
        $year = $expiryDate->format('y');
        $dateString = $day . $month . $year;
        
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
     * Store email and license in database, and handle newsletter subscription
     */
    private function storeDownload($email, $licenseKey, $platform) {
        // Store download info
        $stmt = $this->pdo->prepare(
            "INSERT INTO downloads (email, license_key, ip_address, user_agent, platform) VALUES (?, ?, ?, ?, ?)"
        );
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $downloadStored = $stmt->execute([$email, $licenseKey, $ipAddress, $userAgent, $platform]);
        
        // Handle newsletter subscription if requested
        $newsletterSubscribe = isset($_POST['newsletter_subscribe']) ? true : false;
        if ($newsletterSubscribe) {
            $this->subscribeToNewsletter($email);
        }
        
        return $downloadStored;
    }
    
    /**
     * Subscribe email to newsletter (compatible with professional services system)
     */
    private function subscribeToNewsletter($email) {
        try {
            // Try to connect to the blog database for newsletter functionality
            // This matches the logic from professional-services/contact-handler.php
            if (file_exists('/blog/config.php')) {
                require_once '/blog/config.php';
                try {
                    $newsletterPdo = new PDO(
                        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
                        DB_USER,
                        DB_PASS,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    $stmt = $newsletterPdo->prepare("
                        INSERT INTO newsletter_subscribers (email, subscribed_at, is_active) 
                        VALUES (:email, CURRENT_TIMESTAMP, true) 
                        ON CONFLICT (email) 
                        DO UPDATE SET is_active = true, unsubscribed_at = NULL
                    ");
                    $stmt->execute(['email' => $email]);
                    
                    // Log successful newsletter subscription
                    error_log("Newsletter subscription added for download: $email");
                    
                } catch (PDOException $e) {
                    // Newsletter subscription failed, but don't stop the download process
                    error_log("Newsletter subscription failed for download: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            // Newsletter subscription failed, but don't stop the download process
            error_log("Newsletter subscription error for download: " . $e->getMessage());
        }
    }
    
    /**
     * Process download request
     */
    public function processDownload() {
        try {
            // Check if email is provided
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                throw new Exception('Email address is required');
            }
            
            // Validate email
            if (!$this->validateEmail($email)) {
                throw new Exception('Please enter a valid email address');
            }
            
            // Detect platform
            $platform = $this->detectPlatform();
            
            // Check if download file exists for platform
            $downloadFile = $this->checkDownloadFile($platform);
            
            // Generate license
            $licenseKey = $this->generateLicense();
            
            // Store in database
            if (!$this->storeDownload($email, $licenseKey, $platform)) {
                throw new Exception('Failed to store download information');
            }
            
            // Create and send the download with license
            $this->createDownloadWithLicense($licenseKey, $platform, $downloadFile);
            
        } catch (Exception $e) {
            $this->showError($e->getMessage());
        }
    }
    
    /**
     * Create download with license using multiple methods
     */
    private function createDownloadWithLicense($licenseKey, $platform, $downloadFile) {
        $downloadName = $platform === 'mac' ? 'Selenix-mac-with-LicenseBETA.zip' : 'Selenix-win-with-LicenseBETA.zip';
        
        // Try different methods in order of preference
        if ($this->addLicenseWithPython($licenseKey, $downloadFile, $downloadName)) {
            return;
        } elseif ($this->addLicenseWithSystemZip($licenseKey, $downloadFile, $downloadName)) {
            return;
        } else {
            // Fallback: send original file
            $this->sendFile($downloadFile, basename($downloadFile));
        }
    }
    
    /**
     * Method 1: Use system zip command
     */
    private function addLicenseWithSystemZip($licenseKey, $downloadFile, $downloadName) {
        if (!$this->commandExists('zip')) {
            return false;
        }
        
        $tempZip = tempnam(sys_get_temp_dir(), 'selenix_download_') . '.zip';
        $tempLicense = tempnam(sys_get_temp_dir(), 'license_') . '.txt';
        
        try {
            // Create license file
            file_put_contents($tempLicense, $licenseKey);
            
            // Copy original ZIP
            copy($downloadFile, $tempZip);
            
            // Add license to ZIP using system command
            $command = "zip -j " . escapeshellarg($tempZip) . " " . escapeshellarg($tempLicense);
            
            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempZip)) {
                $this->sendFile($tempZip, $downloadName);
                $this->cleanup([$tempZip, $tempLicense]);
                return true;
            }
            
        } catch (Exception $e) {
            // Continue to next method
        }
        
        $this->cleanup([$tempZip, $tempLicense]);
        return false;
    }
    
    /**
     * Method 2: Use Python script to add license to ZIP
     */
    private function addLicenseWithPython($licenseKey, $downloadFile, $downloadName) {
        if (!$this->commandExists('python3') && !$this->commandExists('python')) {
            return false;
        }
        
        $pythonScript = $this->createPythonScript();
        $tempScript = tempnam(sys_get_temp_dir(), 'add_license_') . '.py';
        $tempZip = tempnam(sys_get_temp_dir(), 'selenix_download_') . '.zip';
        $tempLicense = tempnam(sys_get_temp_dir(), 'license_') . '.txt';
        
        try {
            // Write files
            file_put_contents($tempScript, $pythonScript);
            file_put_contents($tempLicense, $licenseKey);
            copy($downloadFile, $tempZip);
            
            // Execute Python script
            $command = $this->getPythonCommand() . " " . escapeshellarg($tempScript) . " " . 
                      escapeshellarg($tempZip) . " " . escapeshellarg($tempLicense);
            
            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempZip)) {
                $this->sendFile($tempZip, $downloadName);
                $this->cleanup([$tempScript, $tempZip, $tempLicense]);
                return true;
            }
            
        } catch (Exception $e) {
            // Continue to next method
        }
        
        $this->cleanup([$tempScript, $tempZip, $tempLicense]);
        return false;
    }
    

    /**
     * Create Python script for adding license to ZIP
     */
    private function createPythonScript() {
        return '#!/usr/bin/env python3
import sys
import zipfile
import os

def add_license_to_zip(zip_path, license_path):
    try:
        with zipfile.ZipFile(zip_path, "a") as zip_file:
            zip_file.write(license_path, "license.txt")
        return True
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        return False

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: script.py <zip_file> <license_file>", file=sys.stderr)
        sys.exit(1)
    
    zip_path = sys.argv[1]
    license_path = sys.argv[2]
    
    if not os.path.exists(zip_path):
        print(f"ZIP file not found: {zip_path}", file=sys.stderr)
        sys.exit(1)
    
    if not os.path.exists(license_path):
        print(f"License file not found: {license_path}", file=sys.stderr)
        sys.exit(1)
    
    if add_license_to_zip(zip_path, license_path):
        print("License added successfully")
        sys.exit(0)
    else:
        sys.exit(1)
';
    }
    
    /**
     * Check if a command exists
     */
    private function commandExists($command) {
        $whereIsCommand = shell_exec("which $command 2>/dev/null");
        return !empty($whereIsCommand);
    }
    
    /**
     * Get Python command (try python3 first, then python)
     */
    private function getPythonCommand() {
        if ($this->commandExists('python3')) {
            return 'python3';
        } elseif ($this->commandExists('python')) {
            return 'python';
        }
        return 'python3'; // fallback
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanup($files) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Send file for download
     */
    private function sendFile($filePath, $downloadName) {
        if (!file_exists($filePath)) {
            throw new Exception('Download file not found');
        }
        
        $fileSize = filesize($filePath);
        
        // Set headers for file download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output the file
        readfile($filePath);
        
        // Clean up temp file if it's not one of the original files
        if (!in_array($filePath, $this->downloadFiles)) {
            unlink($filePath);
        }
        
        exit;
    }
    
    /**
     * Show download form
     */
    public function showForm() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Download Selenix BETA - Browser Automation Tool</title>
            
            <!-- SEO Meta Tags -->
            <meta name="description" content="Download Selenix browser automation tool with free 1-year license. No-code browser automation for Windows. Get automatic web scraping, data extraction, and workflow automation with AI assistance.">
            <meta name="keywords" content="download selenix, browser automation tool, web scraping software, free automation tool, windows automation, no-code automation">
            <meta name="author" content="Selenix.io">
            <meta name="robots" content="index, follow">
            
            <!-- Open Graph Meta Tags -->
            <meta property="og:title" content="Download Selenix - Browser Automation Tool with Free License">
            <meta property="og:description" content="Download Selenix browser automation tool with free 1-year license. No-code browser automation for Windows. Get automatic web scraping, data extraction, and workflow automation with AI assistance.">
            <meta property="og:image" content="https://selenix.io/selenixlogo.png">
            <meta property="og:url" content="https://selenix.io/download.php">
            <meta property="og:type" content="website">
            <meta property="og:site_name" content="Selenix.io">
            
            <!-- Twitter Card Meta Tags -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Download Selenix - Browser Automation Tool">
            <meta name="twitter:description" content="Download Selenix browser automation tool with free 1-year license. No-code browser automation for Windows with AI assistance.">
            <meta name="twitter:image" content="https://selenix.io/selenixlogo.png">
            
            <!-- Additional SEO Meta Tags -->
            <meta name="theme-color" content="#667eea">
            <link rel="canonical" href="https://selenix.io/download.php">
            
            <!-- Structured Data for SEO -->
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "DownloadAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "https://selenix.io/download.php",
                    "actionPlatform": ["http://schema.org/DesktopWebPlatform"]
                },
                "object": {
                    "@type": "SoftwareApplication",
                    "name": "Selenix",
                    "description": "Browser automation tool with AI assistance and free 1-year license",
                    "operatingSystem": "Windows",
                    "applicationCategory": "DeveloperApplication",
                    "offers": {
                        "@type": "Offer",
                        "price": "0",
                        "priceCurrency": "USD"
                    }
                }
            }
            </script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                
                .download-container {
                    background: white;
                    border-radius: 16px;
                    padding: 40px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
                    max-width: 700px;
                    width: 100%;
                    text-align: center;
                }
                
                .logo {
                    font-size: 1.5rem;
                    font-weight: 800;
                    text-decoration: none;
                    background: linear-gradient(90deg, #4f46e5, #7c3aed);
                    -webkit-background-clip: text;
                    background-clip: text;
                    -webkit-text-fill-color: transparent;
                    position: relative;
                    transition: transform 0.3s ease;
                    display: inline-block;
                    cursor: pointer;
                    margin-bottom: 10px;
                }
                
                .logo:hover {
                    transform: scale(1.05);
                }
                
                .logo-text {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .logo-dot {
                    color: #06b6d4;
                    -webkit-text-fill-color: #06b6d4;
                }
                
                h1 {
                    color: #333;
                    margin-bottom: 10px;
                    font-size: 28px;
                }
                
                .subtitle {
                    color: #666;
                    margin-bottom: 30px;
                    font-size: 16px;
                }
                
                .features {
                    background: #f8f9ff;
                    border-radius: 12px;
                    padding: 20px;
                    margin-bottom: 30px;
                    text-align: left;
                }

                .fa-download {
                    margin-right: 20px;
                }
                
                .feature {
                    display: flex;
                    align-items: center;
                    margin-bottom: 12px;
                }
                
                .feature:last-child {
                    margin-bottom: 0;
                }
                
                .feature i {
                    color: #667eea;
                    margin-right: 12px;
                    width: 20px;
                }
                
                .feature.highlight {
                    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
                    border: 1px solid #c8e6c9;
                    border-radius: 6px;
                    padding: 8px 12px;
                    margin: 8px -12px;
                }
                
                .feature.highlight i {
                    color: #2e7d32;
                }
                
                .feature.highlight span {
                    color: #1b5e20;
                    font-weight: 600;
                }
                
                .form-group {
                    margin-bottom: 20px;
                    text-align: left;
                }
                
                label {
                    display: block;
                    margin-bottom: 8px;
                    color: #333;
                    font-weight: 500;
                }
                
                input[type="email"] {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e1e5e9;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.3s;
                }
                
                input[type="email"]:focus {
                    outline: none;
                    border-color: #667eea;
                }
                
                .download-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    padding: 16px 32px;
                    border-radius: 8px;
                    font-size: 18px;
                    font-weight: 600;
                    cursor: pointer;
                    width: 100%;
                    transition: transform 0.3s, box-shadow 0.3s;
                    position: relative;
                    overflow: hidden;
                }
                
                .download-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
                }
                
                .download-btn:disabled {
                    background: #6c757d;
                    cursor: not-allowed;
                    transform: none;
                    box-shadow: none;
                }
                
                .btn-content {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: opacity 0.3s;
                }
                
                .btn-loading {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    display: none;
                }
                
                .processing-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                
                .processing-card {
                    background: white;
                    padding: 30px;
                    border-radius: 16px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 400px;
                    margin: 20px;
                }
                
                .spinner {
                    font-size: 32px;
                    color: #667eea;
                    margin-bottom: 20px;
                }
                
                .processing-text {
                    color: #333;
                    font-size: 18px;
                    margin-bottom: 10px;
                }
                
                .processing-subtext {
                    color: #666;
                    font-size: 14px;
                }
                
                .privacy-note {
                    font-size: 12px;
                    color: #888;
                    margin-top: 15px;
                    line-height: 1.4;
                }
                
                .platform-selector {
                    display: flex;
                    gap: 15px;
                    margin-top: 8px;
                }
                
                .platform-option {
                    flex: 1;
                    cursor: pointer;
                }
                
                .platform-option input[type="radio"] {
                    display: none;
                }
                
                .platform-card {
                    border: 2px solid #e1e5e9;
                    border-radius: 8px;
                    padding: 16px;
                    text-align: center;
                    transition: all 0.3s;
                    background: white;
                }
                
                .platform-option input[type="radio"]:checked + .platform-card {
                    border-color: #667eea;
                    background: #f8f9ff;
                }
                
                .platform-card:hover {
                    border-color: #667eea;
                }
                
                .platform-card i {
                    font-size: 24px;
                    color: #667eea;
                    margin-bottom: 8px;
                    display: block;
                }
                
                .platform-card span {
                    display: block;
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 4px;
                }
                
                .platform-card small {
                    color: #666;
                    font-size: 12px;
                }
                
                .platform-details {
                    margin-top: 8px;
                    font-size: 11px;
                    color: #888;
                    line-height: 1.3;
                    opacity: 0;
                    max-height: 0;
                    transition: all 0.3s ease;
                    overflow: hidden;
                }
                
                .platform-option input[type="radio"]:checked + .platform-card .platform-details {
                    opacity: 1;
                    max-height: 50px;
                }
                
                /* Newsletter Checkbox Styles */
                .checkbox-group {
                    margin: 20px 0;
                }
                
                .checkbox-label {
                    display: flex;
                    align-items: flex-start;
                    cursor: pointer;
                    font-size: 14px;
                    line-height: 1.4;
                    color: #333;
                    gap: 12px;
                }
                
                .checkbox-label input[type="checkbox"] {
                    display: none;
                }
                
                .checkmark {
                    position: relative;
                    width: 20px;
                    height: 20px;
                    background: white;
                    border: 2px solid #e1e5e9;
                    border-radius: 4px;
                    transition: all 0.3s ease;
                    flex-shrink: 0;
                    margin-top: 2px;
                }
                
                .checkbox-label:hover .checkmark {
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }
                
                .checkbox-label input[type="checkbox"]:checked + .checkmark {
                    background: #667eea;
                    border-color: #667eea;
                }
                
                .checkmark:after {
                    content: '';
                    position: absolute;
                    display: none;
                    left: 6px;
                    top: 2px;
                    width: 5px;
                    height: 10px;
                    border: solid white;
                    border-width: 0 2px 2px 0;
                    transform: rotate(45deg);
                }
                
                .checkbox-label input[type="checkbox"]:checked + .checkmark:after {
                    display: block;
                }
                
                .system-requirements {
                    background: #f8f9ff;
                    border-radius: 12px;
                    padding: 25px;
                    margin-bottom: 30px;
                    border: 1px solid #e1e5e9;
                }
                
                .system-requirements h3 {
                    color: #333;
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .system-requirements h3 i {
                    color: #667eea;
                }
                
                .requirements-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                
                @media (max-width: 768px) {
                    .requirements-grid {
                        grid-template-columns: 1fr;
                    }
                }
                
                .req-card {
                    background: white;
                    border-radius: 8px;
                    border: 2px solid #e1e5e9;
                    overflow: hidden;
                    transition: transform 0.3s, box-shadow 0.3s;
                    text-align: left;
                }
                
                .req-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                }
                
                .windows-req {
                    border-left: 4px solid #0078D4;
                }
                
                .mac-req {
                    border-left: 4px solid #007AFF;
                }
                
                .req-header {
                    padding: 15px 20px;
                    background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
                    border-bottom: 1px solid #e1e5e9;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-weight: 600;
                    color: #333;
                    text-align: left;
                }
                
                .req-header i {
                    font-size: 20px;
                }
                
                .windows-req .req-header i {
                    color: #0078D4;
                }
                
                .mac-req .req-header i {
                    color: #007AFF;
                }
                
                .req-content {
                    padding: 20px;
                    text-align: left;
                }
                
                .req-item {
                    margin-bottom: 12px;
                    line-height: 1.5;
                    color: #555;
                    font-size: 14px;
                }
                
                .req-item:last-child {
                    margin-bottom: 0;
                }
                
                .req-item strong {
                    color: #333;
                    font-weight: 600;
                    display: inline-block;
                    min-width: 100px;
                    vertical-align: top;
                }
                
                .compatibility-note {
                    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    font-size: 14px;
                    line-height: 1.5;
                    color: #856404;
                }
                
                .compatibility-note i {
                    color: #f39c12;
                    margin-top: 2px;
                    flex-shrink: 0;
                }
                
                .compatibility-note code {
                    background: rgba(0, 0, 0, 0.1);
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                    font-size: 13px;
                    color: #2c3e50;
                }
                
                /* Mobile Responsive Styles */
                @media (max-width: 768px) {
                    body {
                        padding: 10px;
                    }
                    
                    .download-container {
                        padding: 25px 20px;
                        margin: 10px 0;
                        border-radius: 12px;
                        max-width: 100%;
                    }
                    
                    .logo {
                        font-size: 1.4rem;
                        margin-bottom: 15px;
                    }
                    
                    h1 {
                        font-size: 24px;
                        margin-bottom: 8px;
                    }
                    
                    .subtitle {
                        font-size: 15px;
                        margin-bottom: 25px;
                    }
                    
                    .features {
                        padding: 15px;
                        margin-bottom: 25px;
                    }
                    
                    .feature {
                        margin-bottom: 10px;
                        font-size: 14px;
                    }
                    
                    .feature.highlight {
                        margin: 6px -10px;
                        padding: 6px 10px;
                    }
                    
                    .system-requirements {
                        padding: 20px 15px;
                        margin-bottom: 25px;
                    }
                    
                    .system-requirements h3 {
                        font-size: 18px;
                        margin-bottom: 15px;
                        flex-direction: column;
                        text-align: center;
                        gap: 5px;
                    }
                    
                    .requirements-grid {
                        grid-template-columns: 1fr;
                        gap: 15px;
                        margin-bottom: 15px;
                    }
                    
                    .req-card {
                        border-radius: 6px;
                    }
                    
                    .req-header {
                        padding: 12px 15px;
                        font-size: 14px;
                    }
                    
                    .req-header i {
                        font-size: 18px;
                    }
                    
                    .req-content {
                        padding: 15px;
                    }
                    
                    .req-item {
                        font-size: 13px;
                        margin-bottom: 10px;
                    }
                    
                    .req-item strong {
                        min-width: 85px;
                        font-size: 13px;
                    }
                    
                    .compatibility-note {
                        padding: 12px;
                        font-size: 13px;
                        flex-direction: column;
                        text-align: center;
                        gap: 8px;
                    }
                    
                    .compatibility-note i {
                        margin-top: 0;
                    }
                    
                    .platform-selector {
                        flex-direction: column;
                        gap: 12px;
                    }
                    
                    .platform-card {
                        padding: 14px;
                    }
                    
                    .platform-card i {
                        font-size: 22px;
                    }
                    
                    .platform-card span {
                        font-size: 15px;
                    }
                    
                    .platform-card small {
                        font-size: 11px;
                    }
                    
                    .platform-details {
                        font-size: 10px;
                        margin-top: 6px;
                    }
                    
                    .checkbox-group {
                        margin: 15px 0;
                    }
                    
                    .checkbox-label {
                        font-size: 13px;
                        gap: 10px;
                    }
                    
                    .checkmark {
                        width: 18px;
                        height: 18px;
                        margin-top: 1px;
                    }
                    
                    .checkmark:after {
                        left: 5px;
                        top: 1px;
                        width: 4px;
                        height: 9px;
                    }
                    
                    .download-btn {
                        padding: 14px 24px;
                        font-size: 16px;
                    }
                    
                    .privacy-note {
                        font-size: 11px;
                        margin-top: 12px;
                        text-align: center;
                    }
                    
                    .processing-card {
                        padding: 25px 20px;
                        margin: 15px;
                        max-width: 90%;
                    }
                    
                    .processing-text {
                        font-size: 16px;
                    }
                    
                    .processing-subtext {
                        font-size: 13px;
                    }
                }
                
                @media (max-width: 480px) {
                    body {
                        padding: 5px;
                    }
                    
                    .download-container {
                        padding: 20px 15px;
                        border-radius: 10px;
                    }
                    
                    .logo {
                        font-size: 1.3rem;
                    }
                    
                    h1 {
                        font-size: 22px;
                    }
                    
                    .subtitle {
                        font-size: 14px;
                    }
                    
                    .features {
                        padding: 12px;
                    }
                    
                    .feature {
                        font-size: 13px;
                        margin-bottom: 8px;
                    }
                    
                    .feature i {
                        width: 18px;
                        margin-right: 10px;
                        font-size: 14px;
                    }
                    
                    .system-requirements {
                        padding: 15px 12px;
                    }
                    
                    .system-requirements h3 {
                        font-size: 16px;
                    }
                    
                    .req-header {
                        padding: 10px 12px;
                        font-size: 13px;
                    }
                    
                    .req-content {
                        padding: 12px;
                    }
                    
                    .req-item {
                        font-size: 12px;
                        margin-bottom: 8px;
                    }
                    
                    .req-item strong {
                        min-width: 75px;
                        font-size: 12px;
                    }
                    
                    .platform-card {
                        padding: 12px;
                    }
                    
                    .platform-card i {
                        font-size: 20px;
                    }
                    
                    .platform-card span {
                        font-size: 14px;
                    }
                    
                    .platform-details {
                        font-size: 9px;
                    }
                    
                    .download-btn {
                        padding: 12px 20px;
                        font-size: 15px;
                    }
                    
                    input[type="email"] {
                        padding: 10px 14px;
                        font-size: 15px;
                    }
                    
                    label {
                        font-size: 14px;
                        margin-bottom: 6px;
                    }
                    
                    .compatibility-note {
                        padding: 10px;
                        font-size: 12px;
                    }
                    
                    .privacy-note {
                        font-size: 10px;
                    }
                }
                
                @media (max-width: 360px) {
                    .download-container {
                        padding: 15px 12px;
                    }
                    
                    h1 {
                        font-size: 20px;
                    }
                    
                    .logo {
                        font-size: 1.2rem;
                    }
                    
                    .req-item strong {
                        min-width: 65px;
                        display: block;
                        margin-bottom: 2px;
                    }
                    
                    .platform-selector {
                        gap: 10px;
                    }
                    
                    .platform-card {
                        padding: 10px;
                    }
                    
                    .system-requirements {
                        padding: 12px 10px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="download-container">
                <a href="/" class="logo">
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                </a>
                <h1>Download Selenix BETA</h1>
                <p class="subtitle">Browser scraping and automation made simple</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>1-year license included automatically</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-robot"></i>
                        <span>AI-powered browser automation & web scraping</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-download"></i>
                        <span>135+ automation commands with zero-code creation</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-puzzle-piece"></i>
                        <span><a href="product/templates/" style="color: inherit; text-decoration: none;">Ready-to-use templates for popular websites</a></span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-file-archive"></i>
                        <span>Portable - no installation required</span>
                    </div>
                </div>
                
                <!-- System Requirements Section -->
                <div class="system-requirements">
                    <h3><i class="fas fa-laptop"></i> System Requirements</h3>
                    <div class="requirements-grid">
                        <div class="req-card windows-req">
                            <div class="req-header">
                                <i class="fab fa-windows"></i>
                                <span>Windows</span>
                            </div>
                            <div class="req-content">
                                <div class="req-item">
                                    <strong>OS:</strong> Windows 10 (64-bit) or Windows 11
                                </div>
                                <div class="req-item">
                                    <strong>Architecture:</strong> x64 (Intel/AMD 64-bit)
                                </div>
                                <div class="req-item">
                                    <strong>RAM:</strong> 4GB minimum, 8GB recommended
                                </div>
                                <div class="req-item">
                                    <strong>Storage:</strong> 200MB free space
                                </div>
                                <div class="req-item">
                                    <strong>Browser:</strong> Chrome, Edge, or Firefox
                                </div>
                            </div>
                        </div>
                        
                        <div class="req-card mac-req">
                            <div class="req-header">
                                <i class="fab fa-apple"></i>
                                <span>Mac</span>
                            </div>
                            <div class="req-content">
                                <div class="req-item">
                                    <strong>OS:</strong> macOS 10.15 (Catalina) or later
                                </div>
                                <div class="req-item">
                                    <strong>Architecture:</strong> Universal (Intel & Apple Silicon)
                                </div>
                                <div class="req-item">
                                    <strong>RAM:</strong> 4GB minimum, 8GB recommended
                                </div>
                                <div class="req-item">
                                    <strong>Storage:</strong> 300MB free space
                                </div>
                                <div class="req-item">
                                    <strong>Browser:</strong> Chrome, Safari, or Firefox
                                </div>
                                <div class="req-item" style="color: #007AFF; font-weight: 600; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e1e5e9;">
                                    <strong style="color: #007AFF;">Setup:</strong> Copy license.txt from ZIP to Documents/Selenix/
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="compatibility-note">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Setup Instructions:</strong> Both versions are currently in Beta. The Mac Universal build supports both Intel Macs and Apple Silicon (M1/M2/M3) natively for optimal performance.
                            <br><br>
                            <strong>Mac Users:</strong> After extracting the ZIP file, copy the <code>license.txt</code> file to <code>~/Documents/Selenix/license.txt</code> for the application to work properly.
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="" id="downloadForm">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="your@email.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="platform">
                            <i class="fas fa-desktop"></i> Choose Your Platform
                        </label>
                        <div class="platform-selector">
                            <label class="platform-option">
                                <input type="radio" name="platform" value="windows" checked>
                                <div class="platform-card">
                                    <i class="fab fa-windows"></i>
                                    <span>Windows</span>
                                    <small>x64 - Windows 10/11</small>
                                    <div class="platform-details">
                                        Supports Intel & AMD 64-bit processors
                                        Professional web scraping & automation
                                    </div>
                                </div>
                            </label>
                            <label class="platform-option">
                                <input type="radio" name="platform" value="mac">
                                <div class="platform-card">
                                    <i class="fab fa-apple"></i>
                                    <span>Mac</span>
                                    <small>Universal - macOS 10.15+</small>
                                    <div class="platform-details">
                                        Native Intel & Apple Silicon support
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="newsletter_subscribe" name="newsletter_subscribe" checked>
                            <span class="checkmark"></span>
                            Subscribe to our newsletter for automation tips, new templates, and product updates
                        </label>
                    </div>
                    
                    <button type="submit" class="download-btn" id="downloadBtn">
                        <div class="btn-content">
                            <i class="fas fa-download"></i>
                            Download Selenix + License
                        </div>
                        <div class="btn-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Preparing Download...
                        </div>
                    </button>
                </form>
                
                <p class="privacy-note">
                    <i class="fas fa-lock"></i>
                    Your email is used to provide updates, support, and newsletter content (if subscribed). Both platforms include the same 135+ automation commands and AI-powered web scraping features. We never share your information and you can unsubscribe anytime.
                </p>
            </div>
            
            <!-- Processing Overlay -->
            <div class="processing-overlay" id="processingOverlay">
                <div class="processing-card">
                    <div class="spinner">
                        <i class="fas fa-cog fa-spin"></i>
                    </div>
                    <div class="processing-text">Preparing Your Download</div>
                    <div class="processing-subtext">Adding license to package... This may take a few seconds</div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('downloadForm');
                    const downloadBtn = document.getElementById('downloadBtn');
                    const btnContent = downloadBtn.querySelector('.btn-content');
                    const btnLoading = downloadBtn.querySelector('.btn-loading');
                    const processingOverlay = document.getElementById('processingOverlay');
                    const platformOptions = document.querySelectorAll('input[name="platform"]');
                    
                    // Update button text based on platform selection
                    function updateButtonText() {
                        const selectedPlatform = document.querySelector('input[name="platform"]:checked').value;
                        const platformName = selectedPlatform === 'mac' ? 'Mac' : 'Windows';
                        btnContent.innerHTML = '<i class="fas fa-download"></i> Download Selenix for ' + platformName + ' + License';
                    }
                    
                    // Listen for platform changes
                    platformOptions.forEach(option => {
                        option.addEventListener('change', updateButtonText);
                    });
                    
                    // Initialize button text
                    updateButtonText();
                    
                    form.addEventListener('submit', function(e) {
                        // Show loading state
                        downloadBtn.disabled = true;
                        btnContent.style.opacity = '0';
                        btnLoading.style.display = 'block';
                        
                        // Show overlay after a short delay
                        setTimeout(function() {
                            processingOverlay.style.display = 'flex';
                        }, 800);
                        
                        // Hide overlay and reset button after estimated download time
                        setTimeout(function() {
                            processingOverlay.style.display = 'none';
                            downloadBtn.disabled = false;
                            btnContent.style.opacity = '1';
                            btnLoading.style.display = 'none';
                            updateButtonText(); // Restore correct button text
                        }, 10000); // 10 seconds should be enough
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Show error message
     */
    private function showError($message) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Download Error - Selenix</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background: #f5f5f5; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    min-height: 100vh; 
                    margin: 0; 
                }
                .error-container { 
                    background: white; 
                    padding: 30px; 
                    border-radius: 8px; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
                    text-align: center;
                    max-width: 400px;
                }
                .error { color: #d32f2f; margin-bottom: 20px; }
                .btn { 
                    background: #667eea; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    display: inline-block;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h2>Download Error</h2>
                <p class="error"><?php echo htmlspecialchars($message); ?></p>
                <a href="download.php" class="btn">Try Again</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle the request
try {
    $downloader = new SelenixDownloader($host, $username, $password, $database, $SECRET_KEY, $IV, $HMAC_KEY, $DOWNLOAD_FILES);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $downloader->processDownload();
    } else {
        $downloader->showForm();
    }
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 20px; text-align: center;">';
    echo '<h2>System Error</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="setup-database.php">Setup Database</a> | <a href="download.php">Try Again</a></p>';
    echo '</div>';
}
?>