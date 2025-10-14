<?php
require_once 'conf.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$sessionManager->logout();

// Redirect to home page with logout message
header('Location: index.php?logout=1');
exit;
?>