<?php
require_once 'conf.php';
require_once 'DB/database.php';
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
$code = sanitize_input($_POST['code'] ?? '');
$newPassword = $_POST['new_password'] ?? '';

$errors = [];
if ($identifier === '') {
    $errors[] = 'Email or username is required';
}
if ($code === '') {
    $errors[] = 'Reset code is required';
}

// Only validate password if it's provided (step 3)
if ($newPassword !== '') {
    // Password policy: mimic login/signup rules
    if (strlen($newPassword) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
        $errors[] = 'Password must contain at least one letter and one number';
    }
}

if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    $sessionManager->setErrors($errors);
    $sessionManager->setFormData(['identifier' => $identifier]);
    header('Location: forms.html');
    exit;
}

// Lookup user
$user = $db->fetchOne(
    "SELECT id, email, full_name FROM users WHERE email = ? OR full_name = ?",
    [$identifier, $identifier]
);
if (!$user) {
    // Avoid enumeration; generic message
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid reset request.']);
        exit;
    }
    $sessionManager->setMessage('msg', 'Invalid reset request.');
    header('Location: forms.html');
    exit;
}

// Validate code
$reset = $db->fetchOne(
    "SELECT id, code, expires_at, used_at FROM password_resets 
     WHERE user_id = ? AND code = ? AND used_at IS NULL 
     ORDER BY id DESC LIMIT 1",
    [ (string)$user['id'], $code ]
);

if (!$reset) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or used code.']);
        exit;
    }
    $sessionManager->setMessage('msg', 'Invalid or used code.');
    header('Location: forms.html');
    exit;
}

if (new DateTime() > new DateTime($reset['expires_at'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Code expired. Please request a new one.']);
        exit;
    }
    $sessionManager->setMessage('msg', 'Code expired. Please request a new one.');
    header('Location: forms.html');
    exit;
}

// If no password provided, just verify the code (step 2)
if ($newPassword === '') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Code verified successfully.']);
        exit;
    }
    $sessionManager->setMessage('msg', 'Code verified successfully.');
    header('Location: forms.html');
    exit;
}

// Update password and mark reset as used (step 3)
$db->beginTransaction();
try {
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->update('users', ['password_hash' => $passwordHash], 'id = ?', [ (string)$user['id'] ]);
    $db->update('password_resets', ['used_at' => (new DateTime())->format('Y-m-d H:i:s')], 'id = ?', [ (string)$reset['id'] ]);
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Could not reset password. Try again.']);
        exit;
    }
    $sessionManager->setMessage('msg', 'Could not reset password. Try again.');
    header('Location: forms.html');
    exit;
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Password changed successfully. You can log in now.']);
    exit;
}

$sessionManager->setMessage('msg', 'Password changed successfully. You can log in now.');
header('Location: forms.html');
exit;


