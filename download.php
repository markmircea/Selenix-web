<?php
// download.php
// Enhanced license generator with email registration and file download
// Version 2.2 - With loading animation and fixed admin warning

// Database configuration
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

// Generated encryption keys - keep these secret!
$SECRET_KEY = 'b53190786ae9a82abf52f8d9094012ee1bd1900bd48682a3c3806cc380258ce6';
$IV = '2ba22e6c427fff9073c4b1cecd75c6b3';
$HMAC_KEY = '74518ca45f74fe5fc2dfa8f5e235a14142b8ed07e41d9d65476622d54ce1753f';

// File to download
$DOWNLOAD_FILE = 'Selenix-win-unpackedBETA.zip';

class SelenixDownloader {
    private $pdo;
    private $secretKey;
    private $iv;
    private $hmacKey;
    private $downloadFile;
    
    public function __construct($host, $username, $password, $database, $secretKey, $iv, $hmacKey, $downloadFile) {
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
        $this->downloadFile = $downloadFile;
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
    private function checkDownloadFile() {
        if (!file_exists($this->downloadFile)) {
            throw new Exception("Download file not found: {$this->downloadFile}");
        }
        return true;
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
     * Store email and license in database
     */
    private function storeDownload($email, $licenseKey) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO downloads (email, license_key, ip_address, user_agent) VALUES (?, ?, ?, ?)"
        );
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return $stmt->execute([$email, $licenseKey, $ipAddress, $userAgent]);
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
            
            // Check if download file exists
            $this->checkDownloadFile();
            
            // Generate license
            $licenseKey = $this->generateLicense();
            
            // Store in database
            if (!$this->storeDownload($email, $licenseKey)) {
                throw new Exception('Failed to store download information');
            }
            
            // Create and send the download with license
            $this->createDownloadWithLicense($licenseKey);
            
        } catch (Exception $e) {
            $this->showError($e->getMessage());
        }
    }
    
    /**
     * Create download with license using multiple methods
     */
    private function createDownloadWithLicense($licenseKey) {
        // Try different methods in order of preference
        if ($this->addLicenseWithSystemZip($licenseKey)) {
            return;
        } elseif ($this->addLicenseWithPython($licenseKey)) {
            return;
        } elseif (class_exists('ZipArchive')) {
            $this->addLicenseWithZipArchive($licenseKey);
        } else {
            // Fallback: send original file
            $this->sendFile($this->downloadFile, basename($this->downloadFile));
        }
    }
    
    /**
     * Method 1: Use system zip command
     */
    private function addLicenseWithSystemZip($licenseKey) {
        if (!$this->commandExists('zip')) {
            return false;
        }
        
        $tempZip = tempnam(sys_get_temp_dir(), 'selenix_download_') . '.zip';
        $tempLicense = tempnam(sys_get_temp_dir(), 'license_') . '.txt';
        
        try {
            // Create license file
            file_put_contents($tempLicense, $licenseKey);
            
            // Copy original ZIP
            copy($this->downloadFile, $tempZip);
            
            // Add license to ZIP using system command
            $command = "zip -j " . escapeshellarg($tempZip) . " " . escapeshellarg($tempLicense);
            
            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempZip)) {
                $this->sendFile($tempZip, 'Selenix-with-License.zip');
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
    private function addLicenseWithPython($licenseKey) {
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
            copy($this->downloadFile, $tempZip);
            
            // Execute Python script
            $command = $this->getPythonCommand() . " " . escapeshellarg($tempScript) . " " . 
                      escapeshellarg($tempZip) . " " . escapeshellarg($tempLicense);
            
            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempZip)) {
                $this->sendFile($tempZip, 'Selenix-with-License.zip');
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
     * Method 3: Use ZipArchive if available
     */
    private function addLicenseWithZipArchive($licenseKey) {
        $tempZip = tempnam(sys_get_temp_dir(), 'selenix_download_') . '.zip';
        
        try {
            // Copy original ZIP
            copy($this->downloadFile, $tempZip);
            
            // Open and modify ZIP
            $zip = new ZipArchive();
            if ($zip->open($tempZip) === TRUE) {
                $zip->addFromString('license.txt', $licenseKey);
                $zip->close();
                
                $this->sendFile($tempZip, 'Selenix-with-License.zip');
                unlink($tempZip);
                return true;
            }
            
        } catch (Exception $e) {
            // Fallback to original file
        }
        
        if (file_exists($tempZip)) {
            unlink($tempZip);
        }
        
        // Send original file as fallback
        $this->sendFile($this->downloadFile, basename($this->downloadFile));
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
        
        // Clean up temp file if it's not the original
        if ($filePath !== $this->downloadFile) {
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
            <title>Download Selenix - Browser Automation Tool</title>
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
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                }
                
                .logo {
                    font-size: 32px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 10px;
                }
                
                .logo-dot {
                    color: #667eea;
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
            </style>
        </head>
        <body>
            <div class="download-container">
                <div class="logo">selenix<span class="logo-dot">.</span>io</div>
                <h1>Download Selenix</h1>
                <p class="subtitle">Browser automation made simple</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>1-year license included automatically</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-robot"></i>
                        <span>No-code browser automation</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-download"></i>
                        <span>Windows version (Beta)</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-file-archive"></i>
                        <span>Ready to run - no installation needed</span>
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
                    Your email is only used to provide you with updates and support. 
                    We never share your information with third parties.
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
    $downloader = new SelenixDownloader($host, $username, $password, $database, $SECRET_KEY, $IV, $HMAC_KEY, $DOWNLOAD_FILE);
    
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