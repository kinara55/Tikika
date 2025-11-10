<?php
require_once 'conf.php';
require_once 'DB/database.php';
$db=new Database($conf);
// Basic server-side validation for signup form

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forms.html');
    exit;
}

function sanitize_input(string $value): string {
    return trim($value);
}

$full_name = sanitize_input($_POST['full_name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = sanitize_input($_POST['phone'] ?? '');
$role_id = (int)($_POST['role_id'] ?? 3); // Default to Attendee if not selected


$errors = [];

// Full name validation
if ($full_name === '') {
    $errors[] = 'Full name is required';
} elseif (strlen($full_name) < 2) {
    $errors[] = 'Full name must be at least 2 characters long';
}

// Email format
if ($email === '') {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Phone validation
if ($phone === '') {
    $errors[] = 'Phone number is required';
} elseif (strlen($phone) < 10) {
    $errors[] = 'Phone number must be at least 10 digits';
}

// Role validation
if ($role_id !== 2 && $role_id !== 3) {
    $errors[] = 'Please select a valid account type';
}

// Password: at least 8 chars, at least one letter and one number
if ($password === '') {
    $errors[] = 'Password is required';
} else {
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    } else {
        $hasLetter = false;
        $hasNumber = false;
        $passwordLength = strlen($password);
        
        for ($i = 0; $i < $passwordLength; $i++) {
            $char = $password[$i];
            if (($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z')) {
                $hasLetter = true;
            } elseif ($char >= '0' && $char <= '9') {
                $hasNumber = true;
            }
        }
        
        if (!$hasLetter) {
            $errors[] = 'Password must contain at least one letter';
        }
        if (!$hasNumber) {
            $errors[] = 'Password must contain at least one number';
        }
    }
}

// If no errors, save user to database
if (empty($errors)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email already exists
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        $errors[] = "An account with this email already exists.";
    } else {
        // Ensure roles exist in database
        $db->query("INSERT IGNORE INTO roles (id, name, description) VALUES 
            (1, 'admin', 'Full administrative access'),
            (2, 'organizer', 'Can create and manage events'),
            (3, 'attendee', 'Buy tickets and attend events')");
        
        // Insert user with is_verified = 0 (unverified)
        try {
            $userId = $db->insert('users', [
                'role_id' => $role_id,
                'full_name' => $full_name,
                'email' => $email,
                'password_hash' => $passwordHash,
                'phone' => $phone,
                'is_verified' => 0
            ]);
            
            // Generate verification code
            $verificationCode = (string)random_int(100000, 999999);
            $expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
            
            // Create verification_codes table if it doesn't exist
            $db->query("CREATE TABLE IF NOT EXISTS verification_codes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                code VARCHAR(10) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Store verification code
            $db->insert('verification_codes', [
                'user_id' => $userId,
                'code' => $verificationCode,
                'expires_at' => $expiresAt
            ]);
            
            // Send verification email
            require_once 'mail/sendmail_verification.php';
            if (sendVerificationCode($email, $full_name, $verificationCode)) {
                // Store user info in session for verification page
                session_start();
                $_SESSION['pending_verification'] = [
                    'user_id' => $userId,
                    'email' => $email,
                    'full_name' => $full_name
                ];
                
                // Redirect to verification page
                header('Location: verify_email.php');
                exit;
            } else {
                $errors[] = "Account created but verification email failed to send. Check error logs for details.";
            }
            
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
// Output minimal HTML response with results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Validation</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .message { max-width: 600px; margin: 2rem auto; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; }
        .error { color: #b00020; }
        .success { color: #0a7f2e; }
        ul { margin: 0.5rem 0 0 1.25rem; }
        a.button { display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #333; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="message">
        <?php if (!empty($errors)): ?>
            <h2 class="error">Signup validation failed</h2>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <h2 class="success">Signup successful!</h2>
            <p>Welcome, <?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>!</p>
            <p>Username: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Email: <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
            <p>You can now <a href="forms.html">login</a> with your credentials.</p>
        <?php endif; ?>
        <a class="button" href="forms.html">Back to forms</a>
    </div>
</body>
</html>


