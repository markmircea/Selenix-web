<?php
/**
 * Contact Form Handler with Database Storage
 * Handles form submissions, validates data, stores in database, and sends email notifications
 */

// Include configuration
require_once 'config.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

try {
    // Get and sanitize form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Get additional data
    $ip_address = getClientIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) > 255) {
        $errors[] = 'Name is too long (maximum 255 characters)';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    } elseif (strlen($subject) > 500) {
        $errors[] = 'Subject is too long (maximum 500 characters)';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) > 10000) {
        $errors[] = 'Message is too long (maximum 10,000 characters)';
    }
    
    // Check for spam patterns (basic protection)
    $spamKeywords = ['casino', 'viagra', 'lottery', 'winner', 'congratulations'];
    $messageText = strtolower($message . ' ' . $subject);
    foreach ($spamKeywords as $keyword) {
        if (strpos($messageText, $keyword) !== false) {
            $errors[] = 'Your message appears to contain spam content';
            break;
        }
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Get database connection
    $pdo = getDatabaseConnection();
    
    // Check for duplicate submissions (same email + subject in last 5 minutes)
    $duplicateCheck = $pdo->prepare("
        SELECT COUNT(*) FROM contact_submissions 
        WHERE email = ? AND subject = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $duplicateCheck->execute([$email, $subject]);
    
    if ($duplicateCheck->fetchColumn() > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'You have already submitted a similar message recently. Please wait a few minutes before submitting again.'
        ]);
        exit;
    }
    
    // Generate date-based ticket number
    $today = date('Ymd'); // Format: 20240624
    
    // Get count of submissions today to determine sequence number
    $todayCount = $pdo->prepare("
        SELECT COUNT(*) FROM contact_submissions 
        WHERE DATE(created_at) = CURDATE()
    ");
    $todayCount->execute();
    $sequenceNumber = $todayCount->fetchColumn() + 1;
    
    // Format: YYYYMMDD-XXX (e.g., 20240624-001)
    $ticketNumber = $today . '-' . str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);
    
    // Insert into database with ticket number
    $stmt = $pdo->prepare("
        INSERT INTO contact_submissions (ticket_number, name, email, subject, message, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $dbInserted = $stmt->execute([$ticketNumber, $name, $email, $subject, $message, $ip_address, $user_agent]);
    $submissionId = $pdo->lastInsertId();
    
    if (!$dbInserted) {
        throw new Exception("Failed to save submission to database");
    }
    
    // Prepare email content
    $email_subject = 'Support Request #' . $ticketNumber . ': ' . $subject;
    $email_body = "
New support request from Selenix website:

Ticket Number: #$ticketNumber
Submission ID: #$submissionId
Name: $name
Email: $email
Subject: $subject

Message:
$message

---
Technical Details:
IP Address: $ip_address
User Agent: $user_agent
Submitted: " . date('Y-m-d H:i:s') . "
Website: " . SITE_URL . "

---
Manage this submission: " . SITE_URL . "/support/admin.php?id=$submissionId
";
    
    // Email headers
    $headers = array(
        'From' => FROM_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To' => $email,
        'X-Mailer' => 'PHP/' . phpversion(),
        'Content-Type' => 'text/plain; charset=UTF-8',
        'X-Submission-ID' => $submissionId,
        'X-Ticket-Number' => $ticketNumber
    );
    
    // Convert headers array to string
    $headers_string = '';
    foreach ($headers as $key => $value) {
        $headers_string .= $key . ': ' . $value . "\r\n";
    }
    
    // Send email notification
    $mail_sent = mail(SUPPORT_EMAIL, $email_subject, $email_body, $headers_string);
    
    // Update database with email status
    $emailStatus = $mail_sent ? 'sent' : 'failed';
    $pdo->prepare("UPDATE contact_submissions SET notes = ? WHERE id = ?")
        ->execute(["Email notification: $emailStatus", $submissionId]);
    
    // Log the submission
    $log_entry = date('Y-m-d H:i:s') . " - Ticket: #$ticketNumber - ID: #$submissionId - $name ($email) - Subject: $subject - Email: $emailStatus\n";
    file_put_contents('support_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Send auto-reply to user
    $autoReplySubject = "We received your message - Support Request #$ticketNumber";
    $autoReplyBody = "
Dear $name,

Thank you for contacting Selenix support! We have received your message and assigned it ticket number #$ticketNumber.

Your message:
Subject: $subject
Submitted: " . date('Y-m-d H:i:s') . "

Our support team will review your request and respond according to the SLA. If you need to reference this ticket, please include the ticket number #$ticketNumber in your communication.

Best regards,
Selenix Support Team
" . SITE_URL . "
";
    
    $autoReplyHeaders = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $autoReplyHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $autoReplySubject, $autoReplyBody, $autoReplyHeaders);
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => "Thank you for your message! We've received your support request (Ticket #$ticketNumber) and will get back to you within the SLA window.",
        'ticket_id' => $submissionId,
        'ticket_number' => $ticketNumber
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Contact form error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error processing your request. Please try again or contact us directly at ' . SUPPORT_EMAIL
    ]);
}
?>
