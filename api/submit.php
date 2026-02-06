<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Configuration
// URL to the deployed Google Apps Script (Web App)
$googleScriptUrl = getenv('GOOGLE_SCRIPT_URL') ?: 'https://script.google.com/macros/s/AKfycbzr9uM9i2dN846_ppW-QKxb30cVqJzy1qJ5ZRJH48KMhQz0p05rsxZMf_-K-jfETkie/exec';

// URL to the actual Google Sheet (for the Admin button)
// USER: Update this link to your actual Google Sheet
$googleSheetLink = 'https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID_HERE/edit';

$adminEmail = 'admin@fixrhino.com';

// Set JSON header for all responses
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $devices = $_POST['devices'] ?? '';

    // 1. Send Data to Google Sheets (Keep existing logic)
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
    // We log but don't stop execution if sheet fails, usually
    curl_close($ch);

    // 2. Prepare Emails using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST') ?: 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER') ?: 'user@example.com';
        $mail->Password = getenv('SMTP_PASS') ?: 'secret';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT') ?: 587;

        // Common styles for emails
        $emailStyles = "
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        ";

        $buttonStyle = "
            display: inline-block;
            background-color: #21C1A5;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
        ";

        // ==========================================
        // Email 1: To the Admin (Notification)
        // ==========================================
        $mail->setFrom('noreply@fixrhino.com', 'FixRhino System');
        $mail->addAddress($adminEmail);

        $mail->isHTML(true);
        $mail->Subject = "New Waitlist Joiner: $fullname";

        $adminBody = "
        <div style='$emailStyles'>
            <h2 style='color: #21C1A5; border-bottom: 2px solid #21C1A5; padding-bottom: 10px;'>New Waitlist Submission</h2>
            <p><strong>Name:</strong> $fullname</p>
            <p><strong>Email:</strong> <a href='mailto:$email'>$email</a></p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Interested Devices:</strong></p>
            <div style='background: #f5f5f5; padding: 15px; border-radius: 8px;'>$devices</div>
            
            <br>
            <a href='$googleSheetLink' style='$buttonStyle'>View in Google Sheets</a>
        </div>";

        $mail->Body = $adminBody;
        $mail->send();

        // ==========================================
        // Email 2: To the User (Confirmation)
        // ==========================================
        $mail->clearAddresses(); // Clear previous recipient
        $mail->addAddress($email);

        $mail->Subject = "Welcome to the FixRhino Waitlist!";

        $userBody = "
        <div style='$emailStyles'>
            <h1 style='color: #21C1A5;'>You're on the list!</h1>
            <p>Hi $fullname,</p>
            <p>Thanks so much for joining the FixRhino waitlist. We're thrilled to have you with us as we revolutionize the repair experience.</p>
            <p>We're working hard to get everything ready. You'll be the first to know when we launch updates or have early access available.</p>
            
            <p>In the meantime, if you have any questions, feel free to reply to this email.</p>
            
            <br>
            <p>Best regards,<br><strong>The FixRhino Team</strong></p>
        </div>";

        $mail->Body = $userBody;
        $mail->send();

        // Return Success JSON
        echo json_encode(['success' => true, 'message' => 'Emails sent and logged successfully.']);

    } catch (Exception $e) {
        // Return Error JSON
        echo json_encode([
            'success' => false,
            'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
        ]);
    }

} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>