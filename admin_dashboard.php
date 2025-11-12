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
    ORDER BY u.created_at DESC
");

$events = $db->fetchAll("
    SELECT e.*, u.full_name AS organizer_name, c.name AS category_name
    FROM events e
    LEFT JOIN users u ON e.organizer_id = u.id
    LEFT JOIN categories c ON e.category_id = c.id
    ORDER BY e.created_at DESC
");

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

$ticketSales = $db->fetchAll("
    SELECT 
        MAX(t.id) AS id,
        t.event_id,
        e.title AS event_title,
        t.type,
        MAX(t.price) AS price,
        SUM(t.quantity) AS total_quantity,
        SUM(t.sold) AS sold,
        COALESCE((
            SELECT SUM(oi.quantity)
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE oi.event_id = t.event_id 
            AND LOWER(oi.ticket_type) = LOWER(t.type)
            AND o.status = 'paid'
        ), 0) AS sold_count,
        (SUM(t.quantity) - SUM(t.sold)) AS available
    FROM tickets t
    LEFT JOIN events e ON t.event_id = e.id
    GROUP BY t.event_id, t.type
    ORDER BY e.title, t.type
");

$orders = $db->fetchAll("
    SELECT o.*, u.full_name AS user_name, u.email AS user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");

$orderItems = $db->fetchAll("
    SELECT 
        oi.*,
        e.title AS event_title,
        o.status AS order_status,
        u.full_name AS user_name
    FROM order_items oi
    LEFT JOIN events e ON oi.event_id = e.id
    LEFT JOIN orders o ON oi.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY oi.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Tikika</title>
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
    .badge-paid { background-color: #28a745; }
    .badge-pending { background-color: #ffc107; }
    .badge-cancelled { background-color: #dc3545; }
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
  <div class="d-flex flex-wrap gap-2 mb-4">
    <button class="btn btn-outline-danger" onclick="showTable('usersSection')">Users</button>
    <button class="btn btn-outline-warning" onclick="showTable('eventsSection')">Events</button>
    <button class="btn btn-outline-success" onclick="showTable('categoriesSection')">Categories</button>
    <button class="btn btn-outline-info" onclick="showTable('ticketsSection')">Ticket Sales</button>
    <button class="btn btn-outline-primary" onclick="showTable('ordersSection')">Orders</button>
    <button class="btn btn-outline-dark" onclick="showTable('orderItemsSection')">Order Items</button>
  </div>

  <div id="usersSection">
    <h2>Users</h2>
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
            <td><?= $user['is_verified'] ? 'Yes' : 'No' ?></td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
            <td>
              <a href="edit_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-outline-warning me-1">Edit</a>
              <a href="delete_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete <?= htmlspecialchars($user['full_name']) ?>?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="eventsSection" class="d-none">
    <h2>Events</h2>
    <div class="table-responsive">
      <table id="eventsTable" class="table table-striped table-bordered">
        <thead class="table-warning">
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Organizer</th>
            <th>Category</th>
            <th>Venue</th>
            <th>Start Date</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['id']) ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= htmlspecialchars($event['organizer_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($event['category_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($event['venue'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($event['start_datetime']) ?></td>
            <td><span class="badge bg-<?= $event['status'] === 'published' ? 'success' : ($event['status'] === 'draft' ? 'secondary' : 'danger') ?>"><?= htmlspecialchars($event['status']) ?></span></td>
            <td><?= htmlspecialchars($event['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

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
            <td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
            <td><?= $cat['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="ticketsSection" class="d-none">
    <h2>Ticket Sales</h2>
    <div class="table-responsive">
      <table id="ticketsTable" class="table table-striped table-bordered">
        <thead class="table-info">
          <tr>
            <th>Event</th>
            <th>Ticket Type</th>
            <th>Price</th>
            <th>Total Quantity</th>
            <th>Sold</th>
            <th>Available</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($ticketSales as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['event_title']) ?></td>
            <td><strong><?= htmlspecialchars($t['type']) ?></strong></td>
            <td>KSh <?= number_format($t['price'], 2) ?></td>
            <td><?= $t['total_quantity'] ?></td>
            <td><span class="badge bg-success"><?= $t['sold_count'] ?></span></td>
            <td><span class="badge bg-<?= $t['available'] > 0 ? 'primary' : 'danger' ?>"><?= $t['available'] ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="ordersSection" class="d-none">
    <h2>Orders</h2>
    <div class="table-responsive">
      <table id="ordersTable" class="table table-striped table-bordered">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= htmlspecialchars($o['id']) ?></td>
            <td><?= htmlspecialchars($o['user_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($o['user_email'] ?? 'N/A') ?></td>
            <td>KSh <?= number_format($o['total_amount'], 2) ?></td>
            <td>
              <span class="badge badge-<?= $o['status'] ?>">
                <?= htmlspecialchars($o['status']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($o['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div id="orderItemsSection" class="d-none">
    <h2>Order Items</h2>
    <div class="table-responsive">
      <table id="orderItemsTable" class="table table-striped table-bordered">
        <thead class="table-dark text-white">
          <tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>User</th>
            <th>Event</th>
            <th>Ticket Type</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orderItems as $oi): ?>
          <tr>
            <td><?= $oi['id'] ?></td>
            <td><?= $oi['order_id'] ?></td>
            <td><?= htmlspecialchars($oi['user_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($oi['event_title'] ?? 'N/A') ?></td>
            <td><strong><?= htmlspecialchars($oi['ticket_type']) ?></strong></td>
            <td><?= $oi['quantity'] ?></td>
            <td>KSh <?= number_format($oi['unit_price'], 2) ?></td>
            <td>KSh <?= number_format($oi['unit_price'] * $oi['quantity'], 2) ?></td>
            <td>
              <span class="badge badge-<?= $oi['order_status'] ?>">
                <?= htmlspecialchars($oi['order_status']) ?>
              </span>
            </td>
            <td><?= $oi['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
function showTable(id) {
  ['usersSection','eventsSection','categoriesSection','ticketsSection','ordersSection','orderItemsSection']
    .forEach(sec => document.getElementById(sec).classList.add('d-none'));
  document.getElementById(id).classList.remove('d-none');
}

$(document).ready(function() {
  $('#usersTable, #eventsTable, #categoriesTable, #ticketsTable, #ordersTable, #orderItemsTable').DataTable();
  showTable('usersSection');
});
</script>

</body>
</html>
