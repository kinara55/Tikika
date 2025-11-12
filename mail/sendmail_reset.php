<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'conf.php';

/**
 * Send password reset code email
 *
 * @param string $toEmail
 * @param string $toName
 * @param string|int $code
 * @return bool
 */
function sendPasswordResetCode($toEmail, $toName, $code) {
    global $conf;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $conf['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $conf['smtp_user'];
        $mail->Password   = $conf['smtp_pass'];
        $mail->SMTPSecure = $conf['smtp_secure'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $conf['smtp_port'];

        $mail->setFrom($conf['site_email'], 'Tikika Password Reset');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your Tikika password';
        $mail->Body    = "<p>Use this code to reset your password: <b>{$code}</b></p><p>The code expires in 10 minutes. If you did not request this, you can ignore this email.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password reset email sending failed: " . $e->getMessage());
        return false;
    }
}




