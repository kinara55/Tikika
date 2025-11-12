<?php
session_start();

if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/conf.php';
require_once __DIR__ . '/DB/database.php';

$db_conf = [
    'DB_HOST' => $conf['DB_HOST'],
    'DB_USER' => $conf['DB_USER'],
    'DB_PASS' => $conf['DB_PASS'],
    'DB_NAME' => $conf['DB_NAME']
];

$db = new Database($db_conf);

$input = json_decode(file_get_contents('php://input'), true);
$event_id = (int)($input['event_id'] ?? 0);
$status = $input['status'] ?? '';

$allowed_statuses = ['draft', 'published', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$organizer_id = $_SESSION['user_id'];

$event = $db->fetchOne("SELECT id FROM events WHERE id = ? AND organizer_id = ?", [$event_id, $organizer_id]);

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Event not found or unauthorized']);
    exit();
}

try {
    $db->update('events', ['status' => $status], 'id = ?', [$event_id]);
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

