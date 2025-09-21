<?php
require_once 'conf.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$sessionManager->logout();

header('Location: forms.html?logged_out=1');
exit;
