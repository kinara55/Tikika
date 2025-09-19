<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'conf.php';

/**
 * Send 2FA email
 * 
 * @param string $toEmail
 * @param string $toName
 * @param string|int $code
 * @return bool
 */
function send2FACode($toEmail, $toName, $code) {
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

        $mail->setFrom($conf['smtp_user'], 'Tikika 2FA');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your Tikika 2FA Code';
        $mail->Body    = "<p>Your 2FA code is: <b>{$code}</b></p><p>This code expires in 5 minutes.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
