<?php
// Clean output buffer to prevent any HTML output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON header immediately
header('Content-Type: application/json');

// Prevent any error output from interfering
ini_set('display_errors', 0);
error_reporting(0);

try {
    $response = [
        'success' => false,
        'message' => '',
        'errors' => []
    ];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $automation_type = trim($_POST['automation_type'] ?? '');
        $name = trim($_POST['name'] ?? '');
        
        // Simple validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['errors']['email'] = 'Valid email is required';
        }
        
        if (empty($automation_type)) {
            $response['errors']['automation_type'] = 'Please describe what you want to automate';
        }
        
        if (empty($response['errors'])) {
            // Send simple email
            $subject = "New Custom Template Request";
            $message = "Email: $email\nName: $name\nAutomation: $automation_type\n\nTime: " . date('Y-m-d H:i:s');
            $headers = "From: noreply@selenix.io\r\nReply-To: $email";
            
            if (mail('support@selenix.io', $subject, $message, $headers)) {
                $response['success'] = true;
                $response['message'] = 'Thank you! We\'ll contact you within 24 hours.';
            } else {
                $response['message'] = 'Error sending email. Please try again.';
            }
        } else {
            $response['message'] = 'Please correct the errors.';
        }
    } else {
        $response['message'] = 'Invalid request.';
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Server error occurred.',
        'errors' => []
    ];
}

// Ensure clean JSON output
echo json_encode($response);
exit();
?>
