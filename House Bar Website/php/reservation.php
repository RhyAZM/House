<?php
/**
 * House Bar & Lounge — Reservation Handler
 * Receives reservation form data and sends email notification.
 */

header('Content-Type: application/json');

/* ---------- CONFIGURATION ---------- */
define('RECIPIENT_EMAIL', 'hello@housebarandlounge.com'); // <-- Change this
define('RECIPIENT_NAME',  'House Bar & Lounge');

/* ---------- HELPERS ---------- */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function respond(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

/* ---------- ONLY ACCEPT POST ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Method Not Allowed');
}

/* ---------- VALIDATE REQUIRED FIELDS ---------- */
$required = ['name', 'email', 'date', 'time'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        respond(400, "Missing required field: {$field}");
    }
}

/* ---------- SANITISE ---------- */
$name     = clean($_POST['name']);
$email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone    = clean($_POST['phone'] ?? '');
$date     = clean($_POST['date']);
$time     = clean($_POST['time']);
$guests   = clean($_POST['guests'] ?? '2');
$occasion = clean($_POST['occasion'] ?? '');
$notes    = clean($_POST['notes'] ?? '');

/* ---------- VALIDATE EMAIL ---------- */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, 'Invalid email address');
}

/* ---------- VALIDATE DATE (must not be in the past) ---------- */
$reservationDate = DateTime::createFromFormat('Y-m-d', $date);
$today           = new DateTime('today');
if (!$reservationDate || $reservationDate < $today) {
    respond(400, 'Reservation date must be today or in the future');
}

/* ---------- BUILD EMAIL ---------- */
$formattedDate = $reservationDate->format('l, F j, Y');

$subject = "New Reservation Request — {$name} ({$formattedDate})";

$body = "
<!DOCTYPE html>
<html>
<head>
  <style>
    body    { font-family: Arial, sans-serif; background: #0a0a0a; color: #ffffff; margin: 0; padding: 0; }
    .wrap   { max-width: 560px; margin: 0 auto; padding: 32px 24px; }
    .logo   { font-size: 1.5rem; font-weight: 800; letter-spacing: 4px; color: #C41230; margin-bottom: 24px; }
    h2      { font-size: 1.2rem; margin-bottom: 20px; color: #ffffff; }
    table   { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    td      { padding: 10px 0; border-bottom: 1px solid #222; font-size: 0.9rem; }
    td:first-child { color: #aaa; width: 40%; }
    .footer { font-size: 0.75rem; color: #555; margin-top: 32px; text-align: center; }
  </style>
</head>
<body>
  <div class='wrap'>
    <div class='logo'>HOUSE BAR &amp; LOUNGE</div>
    <h2>&#128197; New Reservation Request</h2>
    <table>
      <tr><td>Name</td><td><strong>{$name}</strong></td></tr>
      <tr><td>Email</td><td>{$email}</td></tr>
      <tr><td>Phone</td><td>{$phone}</td></tr>
      <tr><td>Date</td><td>{$formattedDate}</td></tr>
      <tr><td>Time</td><td>{$time}</td></tr>
      <tr><td>Guests</td><td>{$guests}</td></tr>
      <tr><td>Occasion</td><td>{$occasion}</td></tr>
      <tr><td>Notes</td><td>{$notes}</td></tr>
    </table>
    <p>Reply to this email to confirm or adjust the reservation.</p>
    <div class='footer'>House Bar &amp; Lounge &mdash; Heart Of yoUr Soul matE</div>
  </div>
</body>
</html>
";

/* ---------- SEND EMAIL ---------- */
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$name} <{$email}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = mail(RECIPIENT_EMAIL, $subject, $body, $headers);

if ($sent) {
    /* Also send confirmation to guest */
    $guestSubject = "Your Reservation at House Bar & Lounge — {$formattedDate}";
    $guestBody = "
    <!DOCTYPE html>
    <html>
    <head>
      <style>
        body  { font-family: Arial, sans-serif; background: #0a0a0a; color: #ffffff; margin: 0; padding: 0; }
        .wrap { max-width: 560px; margin: 0 auto; padding: 32px 24px; }
        .logo { font-size: 1.5rem; font-weight: 800; letter-spacing: 4px; color: #C41230; margin-bottom: 24px; }
        .box  { background: #181818; border: 1px solid #C41230; border-radius: 8px; padding: 24px; margin: 20px 0; }
        p     { font-size: 0.9rem; line-height: 1.6; color: rgba(255,255,255,0.8); }
        .footer { font-size: 0.75rem; color: #555; margin-top: 32px; text-align: center; }
      </style>
    </head>
    <body>
      <div class='wrap'>
        <div class='logo'>HOUSE BAR &amp; LOUNGE</div>
        <h2>&#10003; Reservation Received!</h2>
        <p>Hi {$name}, thanks for choosing House Bar &amp; Lounge. We've received your reservation request and will confirm shortly.</p>
        <div class='box'>
          <p><strong>Date:</strong> {$formattedDate}</p>
          <p><strong>Time:</strong> {$time}</p>
          <p><strong>Guests:</strong> {$guests}</p>
        </div>
        <p>Have questions? Contact us at <a href='mailto:" . RECIPIENT_EMAIL . "' style='color:#C41230;'>" . RECIPIENT_EMAIL . "</a></p>
        <div class='footer'>Heart Of yoUr Soul matE &mdash; Drink Responsibly. 18+ only.</div>
      </div>
    </body>
    </html>
    ";

    $guestHeaders  = "MIME-Version: 1.0\r\n";
    $guestHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    $guestHeaders .= "From: " . RECIPIENT_NAME . " <" . RECIPIENT_EMAIL . ">\r\n";
    $guestHeaders .= "Reply-To: " . RECIPIENT_EMAIL . "\r\n";

    mail($email, $guestSubject, $guestBody, $guestHeaders);

    respond(200, 'Reservation received. Confirmation email sent.');
} else {
    respond(500, 'Could not send email. Please contact us directly.');
}
