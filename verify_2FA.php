<?php
session_start();
require_once 'session/session_manager.php';
require_once 'conf.php';

$sessionManager = new SessionManager($conf); //Object instantiation

// If no user is waiting for 2FA, go back to login
if (!isset($_SESSION['2fa_user'])) {
    header('Location: forms.html');
    exit;
}

// When user submits the 2FA form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = trim($_POST['code'] ?? '');
    $user = $_SESSION['2fa_user'];

    // If code expired (valid for 5 minutes = 300 seconds)
    if (time() > $user['expires_at']) {
        unset($_SESSION['2fa_user']);
        $sessionManager->setMessage('msg', '<div style="color:#e74c3c;">Code expired. Please log in again.</div>');
        header('Location: forms.html');
        exit;
    }

    // If code is correct, complete login
    if ($enteredCode === (string)$user['code']) {
        $sessionManager->login($user['id'], $user['full_name'], $user['role_id']);
        unset($_SESSION['2fa_user']); // Clear 2FA session

        // Redirect based on role
    if ($user['role_id'] == 1) { // Admin
        header('Location: admin_dashboard.php');
        exit;
    } else {
        header('Location: index.php'); // Regular user
        exit;
    }
} else {
    $sessionManager->setMessage('msg', '<div style="color:#e74c3c;">Invalid code. Please try again.</div>');
}
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify 2FA</title>
</head>
<body>
    <h1>Enter Your 2FA Code</h1>

    <?php
        // Show any session messages (invalid code, etc.)
        echo $sessionManager->getMessage('msg');
    ?>

    <form method="post">
        <input type="text" name="code" placeholder="Enter 6-digit code" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>

