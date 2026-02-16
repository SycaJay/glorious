<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require '../vendor/autoload.php'; 

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'sycajessie@gmail.com'; 
        $mail->Password = 'kasu hmjb uagc xyte';         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Common SMTP port (TLS)

        // Email settings
        $mail->setFrom('sycajessie@gmail.com', 'New Jerusalem Generation'); 
        $mail->addAddress($to);                                 
        $mail->isHTML(true);                                   
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true; 
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false; 
    }
}
