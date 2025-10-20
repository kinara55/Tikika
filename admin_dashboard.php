<?php
session_start();

// ✅ Check if admin is logged in
if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: index.php");
    exit();
}

// Include Database class
include 'DB/database.php';

// ✅ DB configuration
$db_conf = [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',  // your password
    'DB_NAME' => 'tikika_db'
];

// Instantiate Database
$db = new Database($db_conf);

// ✅ Fetch users
$users = $db->fetchAll("SELECT id, full_name, email, phone FROM users");

// ✅ Fetch events
$events = $db->fetchAll("SELECT * FROM events");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Tikika</title>

  <!-- ✅ Bootstrap and DataTables -->
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
    <a href="logout.php"><button>Logout</button></a>
  </div>
</nav>

<div class="container my-5">
  <div class="d-flex gap-3 mb-4">
    <button class="btn btn-outline-danger" onclick="showTable('usersSection')">👥 Users</button>
    <button class="btn btn-outline-warning" onclick="showTable('eventsSection')">🎫 Events</button>
  </div>

  <!-- Users Table -->
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
            <td>
              <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Events Table -->
  <div id="eventsSection" class="d-none">
    <h2>All Events</h2>
    <div class="table-responsive">
      <table id="eventsTable" class="table table-striped table-bordered">
        <thead class="table-warning">
          <tr>
            <th>ID</th>
            <th>Event Name</th>
            <th>Description</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['id']) ?></td>
            <td><?= htmlspecialchars($event['name']) ?></td>
            <td><?= htmlspecialchars($event['details']) ?></td>
            <td><?= htmlspecialchars($event['date'] ?? 'N/A') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let usersTable, eventsTable;

function showTable(id) {
  document.getElementById('usersSection').classList.add('d-none');
  document.getElementById('eventsSection').classList.add('d-none');
  document.getElementById(id).classList.remove('d-none');

  // Initialize DataTable if not already initialized
  if (id === 'usersSection' && !$.fn.DataTable.isDataTable('#usersTable')) {
    usersTable = $('#usersTable').DataTable();
  }
  if (id === 'eventsSection' && !$.fn.DataTable.isDataTable('#eventsTable')) {
    eventsTable = $('#eventsTable').DataTable();
  }
}

// Initialize first visible table on page load
$(document).ready(function() {
  showTable('usersSection');
});
</script>

</body>
</html>
