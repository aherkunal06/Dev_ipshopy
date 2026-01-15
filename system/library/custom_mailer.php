<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(DIR_SYSTEM . 'library/phpmailer/Exception.php');
require_once(DIR_SYSTEM . 'library/phpmailer/PHPMailer.php');
require_once(DIR_SYSTEM . 'library/phpmailer/SMTP.php');

function sendMailViaSMTP($to, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'mail.ipshopy.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@ipshopy.com';
        $mail->Password = 'sair ggui jwuy rsem';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email content
        $mail->setFrom('info@ipshopy.com', 'Ipshopy');
        $mail->addAddress($to);
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}
