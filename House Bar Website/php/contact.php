<?php
/**
 * House Bar & Lounge — Contact Form Handler
 */

header('Content-Type: application/json');

define('RECIPIENT_EMAIL', 'hello@housebarandlounge.com'); // <-- Change this

function clean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function respond(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, 'Method Not Allowed');

$name    = clean($_POST['name']    ?? '');
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$subject = clean($_POST['subject'] ?? 'Contact Enquiry');
$message = clean($_POST['message'] ?? '');

if (!$name || !$email || !$message) respond(400, 'All fields are required');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(400, 'Invalid email');

$body    = "<p><b>From:</b> {$name} ({$email})</p><p><b>Message:</b><br/>{$message}</p>";
$headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: {$name} <{$email}>\r\nReply-To: {$email}\r\n";

$sent = mail(RECIPIENT_EMAIL, "Contact: {$subject}", $body, $headers);
respond($sent ? 200 : 500, $sent ? 'Message sent!' : 'Could not send message.');
