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
$phone=


$errors = [];

// Username: 3-30 chars, letters, numbers, underscore only
if ($full_name === '') {
    $errors[] = 'Full name is required';
} elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $full_name)) {
    $errors[] = 'Username must be 3-30 characters (letters, numbers, underscore)';
}

// Email format
if ($email === '') {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Password: at least 8 chars, at least one letter and one number
if ($password === '') {
    $errors[] = 'Password is required';
} else {
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number';
    }
}

// If no errors, prepare hashed password (not saving to DB in this step)
$passwordHash = null;
if (empty($errors)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
}


if (empty($errors)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
    try {
        $db->query($sql, [$full_name, $email, $passwordHash]);
    } catch (Exception $e) {
        $errors[] = "Database error: " . $e->getMessage();
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
            <h2 class="success">Signup validation passed</h2>
            <p>Username: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
            <p>Email: <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
            <!-- Password hash generated for demonstration only; not stored here. -->
        <?php endif; ?>
        <a class="button" href="forms.html">Back to forms</a>
    </div>
</body>
</html>


