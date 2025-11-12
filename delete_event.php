<?php
session_start();

if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 2) {
    header("Location: index.php");
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
$organizer_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$event = $db->fetchOne("SELECT id, title FROM events WHERE id = ? AND organizer_id = ?", [$event_id, $organizer_id]);

if (!$event) {
    header("Location: organizer_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db->query("DELETE FROM tickets WHERE event_id = ?", [$event_id]);
        $db->query("DELETE FROM events WHERE id = ?", [$event_id]);
        header('Location: organizer_dashboard.php?deleted=1');
        exit();
    } catch (Exception $e) {
        $error = 'Error deleting event: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Event - Tikika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Poppins", sans-serif;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header bg-danger text-white">
          <h3 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Delete Event</h3>
        </div>
        <div class="card-body">
          <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          
          <div class="alert alert-warning">
            <strong>Warning!</strong> Are you sure you want to delete this event?
          </div>
          
          <p><strong>Event:</strong> <?= htmlspecialchars($event['title']) ?></p>
          <p class="text-danger">This action cannot be undone. All tickets and related data will be deleted.</p>
          
          <form method="POST">
            <input type="hidden" name="confirm_delete" value="1">
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-trash me-2"></i>Yes, Delete Event
              </button>
              <a href="organizer_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

