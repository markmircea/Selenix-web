<?php
/**
 * Professional Services Contact Form Handler
 * Handles custom template requests and newsletter subscriptions
 */

// Include blog configuration and models for newsletter functionality
require_once '../../blog/config.php';
require_once '../../blog/models.php';
require_once '../../blog/functions.php';

// Initialize blog model for newsletter subscription
$blogModel = new BlogModel();

// Response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $automation_type = sanitizeInput($_POST['automation_type'] ?? '');
    $target_websites = sanitizeInput($_POST['target_websites'] ?? '');
    $data_extract = sanitizeInput($_POST['data_extract'] ?? '');
    $output_format = sanitizeInput($_POST['output_format'] ?? '');
    $scheduling = sanitizeInput($_POST['scheduling'] ?? '');
    $budget = sanitizeInput($_POST['budget'] ?? '');
    $timeline = sanitizeInput($_POST['timeline'] ?? '');
    $special_requirements = sanitizeInput($_POST['special_requirements'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    $newsletter_subscribe = isset($_POST['newsletter_subscribe']) ? true : false;
    
    // Validation
    if (empty($name)) {
        $response['errors']['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $response['errors']['email'] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $response['errors']['email'] = 'Please enter a valid email address';
    }
    
    if (empty($automation_type)) {
        $response['errors']['automation_type'] = 'Please describe what you want to automate';
    }
    
    if (empty($target_websites)) {
        $response['errors']['target_websites'] = 'Please specify target websites';
    }
    
    // If no validation errors, process the form
    if (empty($response['errors'])) {
        try {
            // Subscribe to newsletter if requested
            $newsletterSuccess = false;
            if ($newsletter_subscribe) {
                $newsletterSuccess = $blogModel->subscribeNewsletter($email);
            }
            
            // Prepare email content
            $emailSubject = "New Custom Template Request from $name";
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
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>" . htmlspecialchars($name) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>" . htmlspecialchars($email) . "</span>
                    </div>";
            
            if (!empty($company)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Company:</span>
                        <span class='value'>" . htmlspecialchars($company) . "</span>
                    </div>";
            }
            
            if (!empty($website)) {
                $emailBody .= "
                    <div class='field'>
                        <span class='label'>Website:</span>
                        <span class='value'>" . htmlspecialchars($website) . "</span>
                    </div>";
            }
            
            $emailBody .= "
                    <div class='field'>
                        <span class='label'>Automation Type:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($automation_type)) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Target Websites:</span>
                        <span class='value'>" . nl2br(htmlspecialchars($target_websites)) . "</span>
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
                error_log("Professional Services Contact Form Submission: $name ($email) - $automation_type");
                
            } else {
                $response['message'] = 'There was an error sending your request. Please try again or email us directly at support@selenix.io';
                error_log("Failed to send professional services contact email for: $name ($email)");
            }
            
        } catch (Exception $e) {
            $response['message'] = 'There was an error processing your request. Please try again.';
            error_log("Professional Services Contact Form Error: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Please correct the errors below and try again.';
    }
}

// Handle AJAX requests
if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// For non-AJAX requests, redirect back to the form with status
if ($response['success']) {
    header('Location: index.html?status=success&message=' . urlencode($response['message']));
} else {
    header('Location: index.html?status=error&message=' . urlencode($response['message']));
}
exit;
?>
