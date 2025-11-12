<?php
require_once 'conf.php';
require_once 'DB/database.php';
require_once 'mail/sendmail_reset.php';
require_once 'session/session_manager.php';

$db = new Database($conf);
$sessionManager = new SessionManager($conf);

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    header('Location: forms.html');
    exit;
}

function sanitize_input($v) { return trim($v); }

$identifier = sanitize_input($_POST['identifier'] ?? ''); // email or username

$errors = [];
if ($identifier === '') {
    $errors[] = 'Email or username is required';
}

if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    $sessionManager->setErrors($errors);
    header('Location: forms.html');
    exit;
}

// Ensure password_resets table exists (non-conflicting runtime creation)
$db->query("CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Find user by email or full_name
$user = $db->fetchOne(
    "SELECT id, full_name, email FROM users WHERE email = ? OR full_name = ?",
    [$identifier, $identifier]
);

if ($user) {
    // Generate 6-digit code
    $code = (string)random_int(100000, 999999);
    $expiresAt = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

    // Invalidate prior unused codes for this user (optional cleanup)
    $db->query("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL", [$user['id']]);

    // Store new code
    $db->insert('password_resets', [
        'user_id' => (string)$user['id'],
        'code' => $code,
        'expires_at' => $expiresAt
    ]);

    // Send email
    $emailSent = sendPasswordResetCode($user['email'], $user['full_name'], $code);
    if (!$emailSent) {
        error_log("Failed to send password reset email to: " . $user['email']);
    }
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'If the account exists, a reset code has been sent.']);
    exit;
}

$sessionManager->setMessage('msg', 'If the account exists, a reset code has been sent.');
header('Location: forms.html');
exit;


