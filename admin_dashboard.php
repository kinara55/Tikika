<?php
session_start();


if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
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

$users = $db->fetchAll("
    SELECT 
        u.id, u.full_name, u.email, u.phone, r.name AS role_name,
        u.is_verified, u.created_at
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
");

$events = $db->fetchAll("SELECT * FROM events");
$categories = $db->fetchAll("SELECT * FROM categories");
$tickets = $db->fetchAll("SELECT * FROM tickets");
$orders = $db->fetchAll("SELECT * FROM orders");
$order_items = $db->fetchAll("SELECT * FROM order_items");
$sessions = $db->fetchAll("SELECT * FROM sessions");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Tikika</title>

  <!-- Bootstrap & DataTables CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand" href="#">Tikika Admin Dashboard</a>
  <div class="ms-auto">
    <a href="index.php"><button>Home</button></a>
    <a href="logout.php"><button>Logout</button></a>
  </div>
</nav>

<div class="container my-5">

  <!-- Navigation buttons -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <button class="btn btn-outline-danger" onclick="showTable('usersSection')"> Users</button>
    <button class="btn btn-outline-warning" onclick="showTable('eventsSection')">Events</button>
    <button class="btn btn-outline-success" onclick="showTable('categoriesSection')">Categories</button>
    <button class="btn btn-outline-info" onclick="showTable('ticketsSection')"> Tickets</button>
    <button class="btn btn-outline-primary" onclick="showTable('ordersSection')"> Orders</button>
    <button class="btn btn-outline-dark" onclick="showTable('orderItemsSection')"> Order Items</button>
    <button class="btn btn-outline-secondary" onclick="showTable('sessionsSection')"> Sessions</button>
  </div>

  <!-- USERS -->
  <div id="usersSection">
    <h2>All Users</h2>
    <div class="table-responsive">
      <table id="usersTable" class="table table-striped table-bordered">
        <thead class="table-danger">
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Verified</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></td>
            <td><?= $user['is_verified'] ? '✅ Yes' : '❌ No' ?></td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
            <td>
              <a href="edit_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-outline-warning me-1">Edit</a>
              <a href="delete_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($user['full_name']) ?>?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- EVENTS -->
  <div id="eventsSection" class="d-none">
    <h2>All Events</h2>
    <div class="table-responsive">
      <table id="eventsTable" class="table table-striped table-bordered">
        <thead class="table-warning">
          <tr>
            <th>ID</th>
            <th>Organizer</th>
            <th>Title</th>
            <th>Venue</th>
            <th>Start</th>
            <th>End</th>
            <th>Status</th>
            <th>Image</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['id']) ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= htmlspecialchars($event['description']) ?></td>
            
            <td><?= htmlspecialchars($event['date'] ?? 'N/A') ?></td>
            <td><?= $event['id'] ?></td>
            <td><?= $event['organizer_id'] ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= htmlspecialchars($event['venue']) ?></td>
            <td><?= htmlspecialchars($event['start_datetime']) ?></td>
            <td><?= htmlspecialchars($event['end_datetime']) ?></td>
            <td><?= htmlspecialchars($event['status']) ?></td>
            <td>
              <?php if (!empty($event['image_url'])): ?>
                <img src="<?= htmlspecialchars($event['image_url']) ?>" style="width:60px;height:60px;border-radius:6px;">
              <?php else: ?>
                <span class="text-muted">No image</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($event['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CATEGORIES -->
  <div id="categoriesSection" class="d-none">
    <h2>Categories</h2>
    <div class="table-responsive">
      <table id="categoriesTable" class="table table-striped table-bordered">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td><?= htmlspecialchars($cat['description']) ?></td>
            <td><?= $cat['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TICKETS -->
  <div id="ticketsSection" class="d-none">
    <h2>Tickets</h2>
    <div class="table-responsive">
      <table id="ticketsTable" class="table table-striped table-bordered">
        <thead class="table-info">
          <tr>
            <th>ID</th>
            <th>Event ID</th>
            <th>Type</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Sold</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= $t['id'] ?></td>
            <td><?= $t['event_id'] ?></td>
            <td><?= htmlspecialchars($t['type']) ?></td>
            <td><?= $t['price'] ?></td>
            <td><?= $t['quantity'] ?></td>
            <td><?= $t['sold'] ?></td>
            <td><?= $t['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ORDERS -->
  <div id="ordersSection" class="d-none">
    <h2>Orders</h2>
    <div class="table-responsive">
      <table id="ordersTable" class="table table-striped table-bordered">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Total</th>
            <th>Provider</th>
            <th>Reference</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
              <td><?= htmlspecialchars($o['id'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['user_id'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['total_amount'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['payment_provider'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['provider_reference'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['status'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['created_at'] ?? '') ?></td>

          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ORDER ITEMS -->
  <div id="orderItemsSection" class="d-none">
    <h2>Order Items</h2>
    <div class="table-responsive">
      <table id="orderItemsTable" class="table table-striped table-bordered">
        <thead class="table-dark text-white">
          <tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>Event ID</th>
            <th>Ticket Type</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($order_items as $oi): ?>
          <tr>
            <td><?= $oi['id'] ?></td>
            <td><?= $oi['order_id'] ?></td>
            <td><?= $oi['event_id'] ?></td>
            <td><?= htmlspecialchars($oi['ticket_type']) ?></td>
            <td><?= $oi['quantity'] ?></td>
            <td><?= $oi['unit_price'] ?></td>
            <td><?= $oi['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- SESSIONS -->
  <div id="sessionsSection" class="d-none">
    <h2>Sessions</h2>
    <div class="table-responsive">
      <table id="sessionsTable" class="table table-striped table-bordered">
        <thead class="table-secondary">
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Data</th>
            <th>Last Activity</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['id']) ?></td>
            <td><?= htmlspecialchars($s['user_id']) ?></td>
            <td><?= htmlspecialchars(substr($s['data'], 0, 100)) ?>...</td>
            <td><?= $s['last_activity'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CATEGORIES -->
  <div id="categoriesSection" class="d-none">
    <h2>Categories</h2>
    <div class="table-responsive">
      <table id="categoriesTable" class="table table-striped table-bordered">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td><?= htmlspecialchars($cat['description']) ?></td>
            <td><?= $cat['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TICKETS -->
  <div id="ticketsSection" class="d-none">
    <h2>Tickets</h2>
    <div class="table-responsive">
      <table id="ticketsTable" class="table table-striped table-bordered">
        <thead class="table-info">
          <tr>
            <th>ID</th>
            <th>Event ID</th>
            <th>Type</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Sold</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= $t['id'] ?></td>
            <td><?= $t['event_id'] ?></td>
            <td><?= htmlspecialchars($t['type']) ?></td>
            <td><?= $t['price'] ?></td>
            <td><?= $t['quantity'] ?></td>
            <td><?= $t['sold'] ?></td>
            <td><?= $t['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ORDERS -->
  <div id="ordersSection" class="d-none">
    <h2>Orders</h2>
    <div class="table-responsive">
      <table id="ordersTable" class="table table-striped table-bordered">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Total</th>
            <th>Provider</th>
            <th>Reference</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
              <td><?= htmlspecialchars($o['id'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['user_id'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['total_amount'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['payment_provider'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['provider_reference'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['status'] ?? '') ?></td>
    <td><?= htmlspecialchars($o['created_at'] ?? '') ?></td>

          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ORDER ITEMS -->
  <div id="orderItemsSection" class="d-none">
    <h2>Order Items</h2>
    <div class="table-responsive">
      <table id="orderItemsTable" class="table table-striped table-bordered">
        <thead class="table-dark text-white">
          <tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>Event ID</th>
            <th>Ticket Type</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($order_items as $oi): ?>
          <tr>
            <td><?= $oi['id'] ?></td>
            <td><?= $oi['order_id'] ?></td>
            <td><?= $oi['event_id'] ?></td>
            <td><?= htmlspecialchars($oi['ticket_type']) ?></td>
            <td><?= $oi['quantity'] ?></td>
            <td><?= $oi['unit_price'] ?></td>
            <td><?= $oi['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- SESSIONS -->
  <div id="sessionsSection" class="d-none">
    <h2>Sessions</h2>
    <div class="table-responsive">
      <table id="sessionsTable" class="table table-striped table-bordered">
        <thead class="table-secondary">
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Data</th>
            <th>Last Activity</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['id']) ?></td>
            <td><?= htmlspecialchars($s['user_id']) ?></td>
            <td><?= htmlspecialchars(substr($s['data'], 0, 100)) ?>...</td>
            <td><?= $s['last_activity'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
function showTable(id) {
  ['usersSection','eventsSection','categoriesSection','ticketsSection','ordersSection','orderItemsSection','sessionsSection']
    .forEach(sec => document.getElementById(sec).classList.add('d-none'));
  document.getElementById(id).classList.remove('d-none');
}

$(document).ready(function() {
  $('#usersTable, #eventsTable, #categoriesTable, #ticketsTable, #ordersTable, #orderItemsTable, #sessionsTable').DataTable();
  showTable('usersSection');
});
</script>

</body>
</html>
