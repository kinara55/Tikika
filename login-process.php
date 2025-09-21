<?php

require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
require_once 'sendmail.php'; 

$sessionManager = new SessionManager($conf);
$db = new Database($conf);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forms.html');
    exit;
}

// ------------------ VALIDATION ------------------
function sanitize_input($value) {
    return trim($value);
}

$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Username validation
if ($username === '') $errors[] = 'Username is required';
elseif (strlen($username) < 3 || strlen($username) > 30) $errors[] = 'Username must be 3-30 chars';
elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username can only contain letters, numbers, and underscores';

// Password validation
if ($password === '') $errors[] = 'Password is required';
elseif (strlen($password) < 8) $errors[] = 'Password must be at least 8 chars';
elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))
    $errors[] = 'Password must contain at least one letter and one number';

if (!empty($errors)) {
    $sessionManager->setErrors($errors);
    $sessionManager->setFormData(['username' => $username]);
    header('Location: forms.html');
    exit;
}

// ------------------ AUTHENTICATION ------------------
if ($sessionManager->isAccountLocked()) {
    $sessionManager->setMessage('msg', 'Account temporarily locked. Try again later.');
    header('Location: forms.html');
    exit;
}

try {
    $user = $db->fetchOne(
        "SELECT id, full_name, email, password_hash, role_id, is_active 
         FROM users 
         WHERE email = ? OR full_name = ?",
        [$username, $username]
    );

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $sessionManager->incrementLoginAttempts();
        $sessionManager->setMessage('msg', 'Invalid username or password.');
        $sessionManager->setFormData(['username' => $username]);
        header('Location: forms.html');
        exit;
    }

    if (!$user['is_active']) {
        $sessionManager->setMessage('msg', 'Account deactivated. Contact support.');
        header('Location: forms.html');
        exit;
    }

    $sessionManager->resetLoginAttempts();

    // ------------------ 2FA ------------------
    $code = random_int(100000, 999999);
    $_SESSION['2fa_user'] = [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'role_id' => $user['role_id'],
        'email' => $user['email'],
        'code' => $code,
        'expires_at' => time() + 300
    ];

    // Send 2FA email
    if (!send2FACode($user['email'], $user['full_name'], $code)) {
        $sessionManager->setMessage('msg', 'Could not send 2FA email. Try again later.');
        header('Location: forms.html');
        exit;
    }

    header('Location: verify_2FA.php');
    exit;

} catch (Exception $e) {
    $sessionManager->setMessage('msg', 'Login failed. Please try again.');
    $sessionManager->setFormData(['username' => $username]);
    header('Location: forms.html');
    exit;
}
