<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$required_fields = ['subscriptionID', 'planType', 'userEmail'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$subscriptionID = $input['subscriptionID'];
$planType = $input['planType'];
$userEmail = $input['userEmail'];
$userName = $input['userName'] ?? 'New Subscriber';
$timestamp = date('Y-m-d H:i:s');

// Your email address - UPDATE THIS TO YOUR ACTUAL EMAIL
$admin_email = 'support@selenix.io';

// Email subject and message
$subject = "New Selenix Subscription: $planType Plan";
$message = "
New subscription notification:

Subscription ID: $subscriptionID
Plan Type: $planType
User Email: $userEmail
User Name: $userName
Timestamp: $timestamp

Subscription Details:
- Plan: Professional Support ($planType)
- Status: Active
- Payment Method: PayPal

You can manage this subscription in your PayPal dashboard.

---
Selenix Subscription System
";

// Email headers
$headers = "From: noreply@selenix.io\r\n";
$headers .= "Reply-To: noreply@selenix.io\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$email_sent = mail($admin_email, $subject, $message, $headers);

if ($email_sent) {
    // Log the subscription (optional)
    $log_entry = "[$timestamp] New subscription: $subscriptionID - $planType - $userEmail\n";
    file_put_contents('subscription_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true,
        'message' => 'Subscription notification sent successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send notification email'
    ]);
}
?>