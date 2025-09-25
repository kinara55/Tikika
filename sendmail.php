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

    $mail = new PHPMailer(true);/*Object instantiation in OOP where PHPMailer is instantiated
    as an oject named $mail*/
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

        $mail->setFrom($conf['smtp_user'], 'Tikika 2FA');//method setFrom defines the sender
        $mail->addAddress($toEmail, $toName);//method addAddress define the recipient

        $mail->isHTML(true);
        $mail->Subject = 'Your Tikika 2FA Code';
        $mail->Body    = "<p>Your 2FA code is: <b>{$code}</b></p><p>This code expires in 5 minutes.</p>";

        $mail->send();//method send executes the sending of the email
        return true;
    } catch (Exception $e) {
        return false;
    }
}
// try and catch(Exception handling to handle errors)