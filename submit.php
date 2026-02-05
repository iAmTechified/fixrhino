<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
// NOTE: You must run 'composer require phpmailer/phpmailer' in this directory for this to work.
require 'vendor/autoload.php';

// Configuration
$googleScriptUrl = getenv('GOOGLE_SCRIPT_URL') ?: 'https://script.google.com/macros/s/AKfycbzr9uM9i2dN846_ppW-QKxb30cVqJzy1qJ5ZRJH48KMhQz0p05rsxZMf_-K-jfETkie/exec'; // Replace with your deployed Google Script URL
$recipientEmail = 'admin@fixrhino.com'; // Replace with the email to notify

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $devices = $_POST['devices'] ?? '';

    // 1. Send Data to Google Sheets
    // We send a POST request to the Google Apps Script
    $ch = curl_init($googleScriptUrl);
    $postData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'fullname' => $fullname,
        'email' => $email,
        'phone' => $phone,
        'devices' => $devices
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $sheetResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    // 2. Send Email Notification using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        // $mail->SMTPDebug = 2;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host = getenv('SMTP_HOST') ?: 'smtp.example.com';                     // Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   // Enable SMTP authentication
        $mail->Username = getenv('SMTP_USER') ?: 'user@example.com';                     // SMTP username
        $mail->Password = getenv('SMTP_PASS') ?: 'secret';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
        $mail->Port = getenv('SMTP_PORT') ?: 587;                                    // TCP port to connect to

        // Recipients
        $mail->setFrom('noreply@fixrhino.com', 'FixRhino Waitlist');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New FixRhino Waitlist Submission';
        $mail->Body = "
            <h1>New Waitlist Joiner</h1>
            <p><strong>Name:</strong> $fullname</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Interested Devices:</strong> $devices</p>
        ";
        $mail->AltBody = "New Waitlist Joiner\nName: $fullname\nEmail: $email\nPhone: $phone\nDevices: $devices";

        $mail->send();

        // Success
        echo "<h1>Thank you! You have been added to the waitlist.</h1>";
        echo "<p><a href='index.html'>Go back</a></p>";

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

} else {
    // Not a post request
    header("Location: index.html");
    exit();
}
?>