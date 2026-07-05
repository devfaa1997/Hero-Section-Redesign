<?php
/**
 * CodeBuddy - Schedule Meeting Form Handler
 * 
 * Receives booking form submissions and sends notification emails.
 * Stores submissions in a JSON log for record-keeping.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Sanitize and collect input
$offering = isset($_POST['offering']) ? htmlspecialchars($_POST['offering'], ENT_QUOTES, 'UTF-8') : '';
$name     = isset($_POST['name'])     ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '';
$email    = isset($_POST['email'])    ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$company  = isset($_POST['company'])  ? htmlspecialchars($_POST['company'], ENT_QUOTES, 'UTF-8') : '';
$phone    = isset($_POST['phone'])    ? htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8') : '';
$date     = isset($_POST['date'])     ? htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8') : '';
$time     = isset($_POST['time'])     ? htmlspecialchars($_POST['time'], ENT_QUOTES, 'UTF-8') : '';
$message  = isset($_POST['message'])  ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : '';

// Validate required fields
$errors = [];
if (empty($name))    $errors[] = 'Name is required';
if (empty($email))   $errors[] = 'Email is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
if (empty($date))    $errors[] = 'Preferred date is required';
if (empty($time))    $errors[] = 'Preferred time is required';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

// Determine offering name
$offeringName = ($offering === 'studio') ? 'CodeBuddy Studio' : 'CodeBuddy Solutions';

// --- Email Notification ---
$to = 'support@codebuddy.com';
$subject = "Schedule a Meeting — $offeringName";

$emailBody = "New Meeting Request\n\n";
$emailBody .= "Offering: $offeringName\n";
$emailBody .= "Name: $name\n";
$emailBody .= "Email: $email\n";
$emailBody .= "Company: " . ($company ?: 'N/A') . "\n";
$emailBody .= "Phone: " . ($phone ?: 'N/A') . "\n";
$emailBody .= "Preferred Date: $date\n";
$emailBody .= "Preferred Time: $time\n\n";
$emailBody .= "Project Details:\n" . ($message ?: 'N/A') . "\n";

$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$mailSent = mail($to, $subject, $emailBody, $headers);

// --- Log submission (optional) ---
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'offering'  => $offering,
    'name'      => $name,
    'email'     => $email,
    'company'   => $company,
    'phone'     => $phone,
    'date'      => $date,
    'time'      => $time,
    'message'   => $message,
    'mail_sent' => $mailSent
];

$logFile = $logDir . '/schedule-requests.json';
$logs = [];
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $logs = json_decode($content, true) ?: [];
}
$logs[] = $logEntry;
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));

// --- Response ---
if ($mailSent) {
    echo json_encode(['success' => true, 'message' => 'Your meeting request has been submitted.']);
} else {
    // Email failed but we logged it
    echo json_encode(['success' => true, 'message' => 'Request received. We will contact you shortly.']);
}
