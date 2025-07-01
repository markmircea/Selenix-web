<?php
/**
 * Professional Services Contact Form Handler
 * Handles custom template requests and newsletter subscriptions
 */

// Clean output buffer to prevent any HTML output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON header immediately
header('Content-Type: application/json');

// Basic sanitization and validation functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Try to connect to the database for newsletter functionality
    $pdo = null;
    if (file_exists('../../blog/config.php')) {
        require_once '../../blog/config.php';
        try {
            $pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Get and sanitize form data
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $automation_type = sanitizeInput($_POST['automation_type'] ?? '');
        $data_extract = sanitizeInput($_POST['data_extract'] ?? '');
        $output_format = sanitizeInput($_POST['output_format'] ?? '');
        $scheduling = sanitizeInput($_POST['scheduling'] ?? '');
        $budget = sanitizeInput($_POST['budget'] ?? '');
        $timeline = sanitizeInput($_POST['timeline'] ?? '');
        $special_requirements = sanitizeInput($_POST['special_requirements'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        $newsletter_subscribe = isset($_POST['newsletter_subscribe']) ? true : false;
        
        // Validation - only email and automation_type are required
        if (empty($email)) {
            $response['errors']['email'] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $response['errors']['email'] = 'Please enter a valid email address';
        }
        
        if (empty($automation_type)) {
            $response['errors']['automation_type'] = 'Please describe what you want to automate';
        }
        
        // If no validation errors, process the form
        if (empty($response['errors'])) {
            // Subscribe to newsletter if requested and database available
            $newsletterSuccess = false;
            if ($newsletter_subscribe && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO newsletter_subscribers (email, subscribed_at, is_active) 
                        VALUES (:email, CURRENT_TIMESTAMP, true) 
                        ON CONFLICT (email) 
                        DO UPDATE SET is_active = true, unsubscribed_at = NULL
                    ");
                    $stmt->execute(['email' => $email]);
                    $newsletterSuccess = true;
                } catch (PDOException $e) {
                    // Newsletter subscription failed, but don't stop the main process
                    error_log("Newsletter subscription failed: " . $e->getMessage());
                }
            }
            
            // Prepare email content
            $emailSubject = "New Custom Template Request" . ($name ? " from $name" : "");
            $emailBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .header { background: #4f46e5; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .field { margin-bottom: 15px; }
                    .label { font-weight: bold; color: #4f46e5; }
                    .value { margin-left: 10px; }
                    .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h2>New Custom Template Request</h2>
                </div>
                <div class='content'>";
            
            if (!empty($name)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>" . htmlspecialchars($name) . "</span>
                    </div>";
            }
            
            $emailBody .= "
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>" . htmlspecialchars($email) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Automation Type:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($automation_type)) . "</span>
                    </div>";
            
            if (!empty($data_extract)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Data to Extract:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($data_extract)) . "</span>
                    </div>";
            }
            
            if (!empty($output_format)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Output Format:</span>
                        <span class='value'>" . htmlspecialchars($output_format) . "</span>
                    </div>";
            }
            
            if (!empty($scheduling)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Scheduling Requirements:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($scheduling)) . "</span>
                    </div>";
            }
            
            if (!empty($budget)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Budget Range:</span>
                        <span class='value'>" . htmlspecialchars($budget) . "</span>
                    </div>";
            }
            
            if (!empty($timeline)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Timeline:</span>
                        <span class='value'>" . htmlspecialchars($timeline) . "</span>
                    </div>";
            }
            
            if (!empty($special_requirements)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Special Requirements:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($special_requirements)) . "</span>
                    </div>";
            }
            
            if (!empty($message)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Additional Message:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($message)) . "</span>
                    </div>";
            }
            
            $emailBody .= "
                    <div class='field'>
                        <span class='label'>Newsletter Subscription:</span>
                        <span class='value'>" . ($newsletter_subscribe ? 'Yes' : 'No') . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Submitted:</span>
                        <span class='value'>" . date('Y-m-d H:i:s T') . "</span>
                    </div>
                </div>
                <div class='footer'>
                    <p>This request was submitted through the Selenix Professional Services page.</p>
                </div>
            </body>
            </html>";
            
            // Email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: Selenix Contact Form <noreply@selenix.io>',
                'Reply-To: ' . $email,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            // Send email
            $emailSent = mail('support@selenix.io', $emailSubject, $emailBody, implode("\r\n", $headers));
            
            if ($emailSent) {
                $response['success'] = true;
                $response['message'] = 'Thank you for your request! We\'ll get back to you within 24 hours with a custom quote.';
                
                if ($newsletter_subscribe && $newsletterSuccess) {
                    $response['message'] .= ' You\'ve also been subscribed to our newsletter.';
                }
                
                // Log the successful submission
                error_log("Professional Services Contact Form Submission: " . ($name ?: 'No name') . " ($email) - $automation_type");
                
            } else {
                $response['message'] = 'There was an error sending your request. Please try again or email us directly at support@selenix.io';
                error_log("Failed to send professional services contact email for: " . ($name ?: 'No name') . " ($email)");
            }
        } else {
            $response['message'] = 'Please correct the errors below and try again.';
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'There was an error processing your request. Please try again.',
        'errors' => []
    ];
    error_log("Professional Services Contact Form Error: " . $e->getMessage());
}

// Ensure clean JSON output
echo json_encode($response);
exit();
?>
