<?php

require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php'; 

$sessionManager = new SessionManager($conf);
$db = new Database($conf);

// Clear any old error messages
$sessionManager->clearErrors();

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

// Basic validation
if ($username === '') $errors[] = 'Email or Full Name is required';
if ($password === '') $errors[] = 'Password is required';

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
        "SELECT id, full_name, email, password_hash, role_id, is_verified
         FROM users 
         WHERE email = ? OR full_name = ?",
        [$username, $username]
    );

    if (!$user) {
        $sessionManager->incrementLoginAttempts();
        $sessionManager->setMessage('msg', 'No account found with that email or name.');
        $sessionManager->setFormData(['username' => $username]);
        header('Location: forms.html');
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        $sessionManager->incrementLoginAttempts();
        $sessionManager->setMessage('msg', 'Invalid password.');
        $sessionManager->setFormData(['username' => $username]);
        header('Location: forms.html');
        exit;
    }

    // Check if email is verified
    if (!$user['is_verified']) {
        $sessionManager->setMessage('msg', 'Please verify your email address before logging in. Check your email for the verification code.');
        $sessionManager->setFormData(['username' => $username]);
        header('Location: forms.html');
        exit;
    }

    $sessionManager->resetLoginAttempts();

    // ------------------ LOGIN SUCCESS ------------------
    $sessionManager->login($user['id'], $user['full_name'], $user['role_id']);

    // Redirect based on role
    if ($user['role_id'] == 1) { // Admin
        header('Location: admin_dashboard.php');
        exit;
    } else {
        header('Location: index.php'); // Regular user
        exit;
    }

} catch (Exception $e) {
    $sessionManager->setMessage('msg', 'Login failed. Please try again.');
    $sessionManager->setFormData(['username' => $username]);
    header('Location: forms.html');
    exit;
}
