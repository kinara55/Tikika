<?php
// Basic server-side validation for login form

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forms.html');
    exit;
}

function sanitize_input(string $value): string {
    return trim($value);
}

$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Username validation
if ($username === '') {
    $errors[] = 'Username is required';
} elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
    $errors[] = 'Username must be 3-30 characters (letters, numbers, underscore only)';
}

// Password validation
if ($password === '') {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
    $errors[] = 'Password must contain at least one letter and one number';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Validation</title>
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
            <h2 class="error">Login validation failed</h2>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <h2 class="success">Login validation passed</h2>
            <p>Username: <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <a class="button" href="forms.html">Back to forms</a>
    </div>
</body>
</html>


