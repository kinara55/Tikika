<?php
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
 // initializing  session manager and db
$sessionManager = new SessionManager($conf);
$db = new Database($conf);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forms.html');
    exit;
}
function sanitize_input($value) {
    return trim($value);
}
$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];
          if ($username === '') {
    $errors[] = 'Username is required';
} else {
    // Check length
    if (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Username must be 3-30 characters';
    } else {
        // Checking each character 
        $valid = true;
        for ($i = 0; $i < strlen($username); $i++) {
            $char = $username[$i];
            if (!(($char >= 'a' && $char <= 'z') || 
                  ($char >= 'A' && $char <= 'Z') || 
                  ($char >= '0' && $char <= '9') || 
                  $char == '_')) {
                $valid = false;
                break;
            }
        }
        if (!$valid) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }
    }
}
if ($password === '') {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
} else {
    // Check for at least one letter
    $has_letter = false;
    $has_number = false;
    for ($i = 0; $i < strlen($password); $i++) {
        $char = $password[$i];
        if (($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z')) {
            $has_letter = true;
        }
        if ($char >= '0' && $char <= '9') {
            $has_number = true;
        }
    }
    if (!$has_letter || !$has_number) {
        $errors[] = 'Password must contain at least one letter and one number';
    }
}

if (!empty($errors)) {
    $sessionManager->setErrors($errors);
    $sessionManager->setFormData(['username' => $username]);
    header('Location: forms.html');
    exit;
}

if ($sessionManager->isAccountLocked()) {
    $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Account temporarily locked due to too many failed attempts. Please try again later.</div>');
    header('Location: forms.html');
    exit;
}

try {
    $user = $db->fetchOne(
        "SELECT id, full_name, email, password_hash, role_id, is_active FROM users WHERE email = ? OR full_name = ?",
        [$username, $username]
    );
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $sessionManager->incrementLoginAttempts();
        $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Invalid username or password.</div>');
        $sessionManager->setFormData(['username' => $username]);
        header('Location: forms.html');
        exit;
    }
    
    if (!$user['is_active']) {
        $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Account is deactivated. Please contact support.</div>');
        header('Location: forms.html');
        exit;
    }
    
    $sessionManager->resetLoginAttempts();
    $sessionManager->login($user['id'], $user['full_name'], $user['role_id']);
    $sessionManager->setMessage('msg', '<div style="color: #27ae60; margin-bottom: 1rem;">Login successful! Welcome back, ' . htmlspecialchars($user['full_name']) . '.</div>');
    
    header('Location: index.html');
    exit;
    
} catch (Exception $e) {
    $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Login failed. Please try again.</div>');
    $sessionManager->setFormData(['username' => $username]);
    header('Location: forms.html');
    exit;
}
