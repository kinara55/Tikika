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

$events = $db->fetchAll("
    SELECT 
        e.*,
        c.name AS category_name,
        (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) AS ticket_types_count,
        (SELECT SUM(quantity) FROM tickets WHERE event_id = e.id) AS total_tickets,
        (SELECT SUM(sold) FROM tickets WHERE event_id = e.id) AS total_sold
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    WHERE e.organizer_id = ?
    ORDER BY e.created_at DESC
", [$organizer_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizer Dashboard - Tikika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Poppins", sans-serif;
    }
    .navbar {
      background: linear-gradient(90deg, #ff0066, #ff6600);
    }
    .navbar-brand {
      color: white;
      font-weight: bold;
    }
    .navbar button {
      border: none;
      background: #fff;
      color: #ff0066;
      border-radius: 20px;
      padding: 5px 15px;
      font-weight: 500;
    }
    .navbar button:hover {
      background: #ffe3ea;
    }
    h2 {
      color: #ff0066;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .badge-draft { background-color: #6c757d; }
    .badge-published { background-color: #28a745; }
    .badge-cancelled { background-color: #dc3545; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand" href="#">Tikika Organizer Dashboard</a>
  <div class="ms-auto">
    <a href="index.php"><button>Home</button></a>
    <a href="logout.php"><button>Logout</button></a>
  </div>
</nav>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Events</h2>
    <a href="create_event.php" class="btn btn-danger">
      <i class="fas fa-plus me-2"></i>Create New Event
    </a>
  </div>

  <?php if (empty($events)): ?>
    <div class="alert alert-info text-center">
      <h4>No events yet!</h4>
      <p>Create your first event to get started.</p>
      <a href="create_event.php" class="btn btn-primary">Create Event</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table id="eventsTable" class="table table-striped table-bordered">
        <thead class="table-warning">
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Venue</th>
            <th>Start Date</th>
            <th>Status</th>
            <th>Ticket Types</th>
            <th>Total/Sold</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['id']) ?></td>
            <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
            <td><?= htmlspecialchars($event['category_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($event['venue'] ?? 'TBA') ?></td>
            <td><?= date('M d, Y H:i', strtotime($event['start_datetime'])) ?></td>
            <td>
              <span class="badge badge-<?= $event['status'] ?>">
                <?= htmlspecialchars($event['status']) ?>
              </span>
            </td>
            <td><?= $event['ticket_types_count'] ?? 0 ?></td>
            <td><?= ($event['total_tickets'] ?? 0) ?> / <?= ($event['total_sold'] ?? 0) ?> sold</td>
            <td>
              <div class="btn-group" role="group">
                <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="changeStatus(<?= $event['id'] ?>, '<?= $event['status'] ?>')" title="Change Status">
                  <i class="fas fa-toggle-on"></i>
                </button>
                <a href="delete_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-danger" 
                   onclick="return confirm('Delete event: <?= htmlspecialchars($event['title']) ?>? This cannot be undone!');" title="Delete">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  $('#eventsTable').DataTable();
});

function changeStatus(eventId, currentStatus) {
  const statuses = ['draft', 'published', 'cancelled'];
  const currentIndex = statuses.indexOf(currentStatus);
  const nextIndex = (currentIndex + 1) % statuses.length;
  const newStatus = statuses[nextIndex];
  
  if (confirm(`Change event status from "${currentStatus}" to "${newStatus}"?`)) {
    fetch('update_event_status.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        event_id: eventId,
        status: newStatus
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Failed to update status'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred');
    });
  }
}
</script>

</body>
</html>

