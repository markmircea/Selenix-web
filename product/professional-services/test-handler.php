<?php
/**
 * Simple Test Contact Handler
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always return JSON
header('Content-Type: application/json');

try {
    // Basic sanitization function
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    // Basic email validation
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    $response = [
        'success' => false,
        'message' => '',
        'errors' => []
    ];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = sanitizeInput($_POST['email'] ?? '');
        $automation_type = sanitizeInput($_POST['automation_type'] ?? '');
        $name = sanitizeInput($_POST['name'] ?? '');
        
        // Validation
        if (empty($email)) {
            $response['errors']['email'] = 'Email is required';
        } elseif (!isValidEmail($email)) {
            $response['errors']['email'] = 'Please enter a valid email address';
        }
        
        if (empty($automation_type)) {
            $response['errors']['automation_type'] = 'Please describe what you want to automate';
        }
        
        if (empty($response['errors'])) {
            // Try to send email
            $emailSubject = "New Custom Template Request" . ($name ? " from $name" : "");
            $emailBody = "
            Email: $email
            Name: $name
            Automation Type: $automation_type
            Submitted: " . date('Y-m-d H:i:s') . "
            ";
            
            $headers = [
                'From: Selenix Contact Form <noreply@selenix.io>',
                'Reply-To: ' . $email
            ];
            
            $emailSent = mail('support@selenix.io', $emailSubject, $emailBody, implode("\r\n", $headers));
            
            if ($emailSent) {
                $response['success'] = true;
                $response['message'] = 'Thank you for your request! We\'ll get back to you within 24 hours.';
            } else {
                $response['message'] = 'There was an error sending your request. Please try again.';
            }
        } else {
            $response['message'] = 'Please correct the errors and try again.';
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'errors' => []
    ];
}

echo json_encode($response);
exit;
?>
