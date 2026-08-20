<?php
session_start(); // Start session to set status messages

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if you installed PHPMailer via Composer)
// If you haven't, you'll need to require the individual PHPMailer files.
// Assuming your vendor directory is accessible from mail/contact.php
require '../vendor/autoload.php'; // Adjust path if necessary

if(empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['message']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  http_response_code(400); // Bad request
  // For non-AJAX, you might set a session message and redirect
  $_SESSION['contact_form_status'] = 'error_validation';
  // header('Location: ../contact.php'); // Redirect back to the form page on your site
  exit('Invalid input.'); // Or a more user-friendly message
}

$name = strip_tags(htmlspecialchars($_POST['name']));
$email = strip_tags(htmlspecialchars($_POST['email']));
$m_subject = strip_tags(htmlspecialchars($_POST['subject']));
$message_body = strip_tags(htmlspecialchars($_POST['message'])); // Renamed to avoid conflict

$mail = new PHPMailer(true);

try {
    // Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output (for troubleshooting)
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through (e.g., smtp.gmail.com)
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = '149oanh@gmail.com';             // SMTP username
    $mail->Password   = 'exts zwiz caba prmf';        // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
    $mail->Port       = 587;                                    // TCP port to connect to (587 for TLS, 465 for SSL)

    // Recipients
    $mail->setFrom($email, $name); // Sender's email and name (from the form)
    $mail->addAddress('your-receiving-email@example.com', 'Your Shop Name');     // Add a recipient (your email address)
    // $mail->addReplyTo($email, $name); // So replies go to the user who filled the form

    // Content
    $mail->isHTML(false); // Set email format to plain text (can be true for HTML)
    $mail->Subject = "Contact Form: " . $m_subject . " from " . $name;
    $mail->Body    = "You have received a new message from your website contact form.\n\n" .
                     "Name: $name\n" .
                     "Email: $email\n" .
                     "Subject: $m_subject\n\n" .
                     "Message:\n$message_body";
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; // If isHTML(true)

    $mail->send();
    $_SESSION['contact_form_status'] = 'success';
    // For AJAX, you might echo JSON: echo json_encode(['status' => 'success', 'message' => 'Message has been sent']);
    // For non-AJAX redirect:
    // header('Location: ../contact.php'); // Redirect back to the main contact page on your site
    // exit();
    echo 'Message has been sent'; // Simple success message for now

} catch (Exception $e) {
    $_SESSION['contact_form_status'] = 'error_mailer';
    // For AJAX: echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    // For non-AJAX redirect:
    // header('Location: ../contact.php'); // Redirect back
    // exit();
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}"); // Log the error
    http_response_code(500);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // More detailed error for dev
}

?>