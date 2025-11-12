<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'conf.php';

/**
 * Send email verification code
 *
 * @param string $toEmail
 * @param string $toName
 * @param string|int $code
 * @return bool
 */
function sendVerificationCode($toEmail, $toName, $code) {
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

        $mail->setFrom($conf['site_email'], 'Tikika Verification');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your Tikika account';
        $mail->Body    = "<p>Welcome to Tikika! Use this code to verify your account: <b>{$code}</b></p><p>The code expires in 15 minutes. If you did not create this account, you can ignore this email.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}



