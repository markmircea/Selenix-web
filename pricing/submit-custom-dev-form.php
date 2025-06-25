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
$required_fields = ['name', 'email', 'description', 'subscriptionId'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$name = $input['name'];
$email = $input['email'];
$company = $input['company'] ?? '';
$phone = $input['phone'] ?? '';
$description = $input['description'];
$timeline = $input['timeline'] ?? 'Not specified';
$notes = $input['notes'] ?? '';
$subscriptionId = $input['subscriptionId'];
$timestamp = date('Y-m-d H:i:s');

// Your email address - UPDATE THIS TO YOUR ACTUAL EMAIL
$admin_email = 'support@selenix.io';

// Email subject and message
$subject = "New Custom Development Project - Subscription ID: $subscriptionId";
$message = "
New Custom Development Project Submission:

CUSTOMER INFORMATION:
Name: $name
Email: $email
Company: $company
Phone: $phone
Subscription ID: $subscriptionId
Submitted: $timestamp

PROJECT DETAILS:
Description: $description

Timeline: $timeline

Additional Notes: $notes

---
Please contact the customer within 24 hours to discuss their project requirements.

Selenix Custom Development Team
";

// Email headers
$headers = "From: noreply@selenix.io\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$email_sent = mail($admin_email, $subject, $message, $headers);

if ($email_sent) {
    // Log the submission (optional)
    $log_entry = "[$timestamp] Custom Dev Project: $subscriptionId - $name ($email) - $description\n";
    file_put_contents('custom_dev_submissions.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Send confirmation email to customer
    $customer_subject = "Custom Development Project Received - Selenix";
    $customer_message = "
Hi $name,

Thank you for subscribing to our Custom Development plan! We've received your project details and will contact you within 24 hours to discuss your requirements and get started.

Your Project Summary:
- Subscription ID: $subscriptionId
- Project: $description
- Timeline: $timeline

Our development team will reach out to you soon to:
1. Discuss your specific requirements in detail
2. Provide a project timeline and milestones
3. Set up regular check-ins and progress updates

If you have any immediate questions, feel free to reply to this email.

Best regards,
The Selenix Custom Development Team
";

    $customer_headers = "From: noreply@selenix.io\r\n";
    $customer_headers .= "Reply-To: admin@selenix.io\r\n";
    $customer_headers .= "X-Mailer: PHP/" . phpversion();
    
    mail($email, $customer_subject, $customer_message, $customer_headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Project details submitted successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send project details'
    ]);
}
?>