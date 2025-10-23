<?php
session_start();
if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: index.php");
    exit();
}
require_once __DIR__ . '/conf.php';
include 'DB/database.php';

$db_conf = [
    'DB_HOST' => $conf['DB_HOST'],
    'DB_USER' => $conf['DB_USER'],
    'DB_PASS' => $conf['DB_PASS'],
    'DB_NAME' => $conf['DB_NAME']
];

$db = new Database($db_conf);

if (!isset($_GET['id'])) {
    die("User ID not specified");
}

$user_id = $_GET['id'];

$db->delete('users', 'id = ?', [$user_id]);

header("Location: admin_dashboard.php");
exit();
