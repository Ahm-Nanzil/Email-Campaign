<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

// Check if form is submitted and process email
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email from the form
    $recipientEmail = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $recipientName = filter_var($_POST['name'] ?? 'Subscriber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    try {
        $mail->isSMTP();                           
        $mail->Host       = 'smtp.gmail.com';      
        $mail->SMTPAuth   = true;                  
        $mail->Username   = 'ahmnanzil33@gmail.com'; 
        $mail->Password   = 'hpitjdlzhhmnhurc'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        $mail->setFrom('ahmnanzil@web.service', 'Web Development');

        $emailTemplatePath = __DIR__ . '/emailbody.html';
        $emailBody = file_get_contents($emailTemplatePath);

        if (empty($recipientEmail)) {
            echo "Error: Recipient email address is missing.\n";
            return;
        }

        $mail->addAddress($recipientEmail, $recipientName);

        if (!$emailBody) {
            echo "Error: Could not read email template.\n";
            return;
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = "Boost Your Online Presence with a Professional Website 🌐";
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        $mail->send();
        echo "Email has been sent successfully to $recipientEmail!\n";
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}\n";
    }
}
?>