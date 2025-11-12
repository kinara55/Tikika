<?php
require_once 'conf.php';
require_once 'DB/database.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$isLoggedIn = $sessionManager->isLoggedIn();
$userName = $isLoggedIn ? $sessionManager->getUsername() : '';
$userRole = $isLoggedIn ? $sessionManager->getRoleId() : 0;
$currentPage = 'events';

$db = new Database($conf);
$event = null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $event = $db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
}
if (!$event) {
    // Show error or redirect if event not found
    echo '<div class="container mt-5"><div class="alert alert-danger">Event not found.</div></div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $event['title']; ?> - Tikika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* Navbar Gradient */
    .navbar {
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
    }

    .navbar-brand, .nav-link {
      color: #fff !important;
      font-weight: 500;
    }

    .nav-link.active {
      font-weight: bold;
      text-decoration: underline;
    }

    .nav-link:hover {
      color: #ffd6d6 !important;
    }

    body {
      background-color: #fff7f9; /* soft pink background */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #212529;
    }

    .event-details {
      background: #ffffff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      border-left: 6px solid #ff4b26; /* pink accent */
    }

    .event-details h1 {
      font-weight: bold;
      color: #ff4b2b; /* bright pink heading */
    }

    .event-details p {
      font-size: 1.1rem;
      margin-bottom: 10px;
    }

    .event-image {
      width: 100%;              /* Fill card width */
      height: 350px;            /* Fixed height for consistency */
      object-fit: cover;        /* Crop nicely instead of stretching */
      border-radius: 12px;      /* Smooth corners */
      border: 3px solid #ff0066; /* Pink border */
      box-shadow: 0 6px 15px rgba(255, 102, 0, 0.4); /* Orange glow */
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .event-image:hover {
      transform: scale(1.05); /* Zoom on hover */
      box-shadow: 0 10px 25px rgba(255, 0, 102, 0.6);
      border-color: #ff6600; /* Change to orange on hover */
    }

    .btn-buy {
      background: linear-gradient(45deg, #ff6b81, #ff914d); /* pink → orange */
      border: none;
      color: #fff;
      font-weight: 600;
    }

    .btn-buy:hover {
      background: linear-gradient(45deg, #ff4d6d, #ff7b29);
      color: #fff;
    }

    .btn-back {
      border: 2px solid #ff6b81;
      color: #ff6b81;
      font-weight: 600;
    }

    .btn-back:hover {
      background-color: #ff6b81;
      color: #fff;
    }

    /* Footer Gradient */
    footer {
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
      color: #fff;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <?php include 'components/navbar.php'; ?>

  <!-- Event Details Section -->
  <section class="container my-5">
    <div class="row event-details">
      <div class="col-md-6 mb-4">
        <img src="<?php echo $event['image']; ?>" class="img-fluid event-image" alt="<?php echo $event['title']; ?>">
      </div>
      <div class="col-md-6 d-flex flex-column justify-content-center">
        <h1><?php echo $event['title']; ?></h1>
    <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event['start_datetime'])); ?>
    <?php if (!empty($event['end_datetime'])): ?> - <?php echo date('F j, Y, g:i a', strtotime($event['end_datetime'])); ?><?php endif; ?></p>
  <p><strong>Location:</strong> <?php echo htmlspecialchars($event['venue'] ?? ''); ?></p>
    <?php
    // Fetch ticket types and prices
    $tickets = $db->fetchAll("SELECT type, price, quantity FROM tickets WHERE event_id = ?", [$event['id']]);
    if ($tickets && count($tickets) > 0): ?>
    <p><strong>Ticket Types & Prices:</strong></p>
    <ul>
    <?php foreach ($tickets as $ticket): ?>
      <li><?php echo htmlspecialchars($ticket['type']); ?>: Ksh <?php echo number_format($ticket['price'], 2); ?> (<?php echo $ticket['quantity']; ?> available)</li>
    <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p><strong>Tickets:</strong> Not available</p>
    <?php endif; ?>
        <p><?php echo $event['description']; ?></p>
        <div class="d-flex gap-3 mt-3">
          <a href="buy_ticket.php?id=<?php echo $id; ?>" class="btn btn-buy btn-lg shadow">Buy Ticket</a>
          <a href="events.php" class="btn btn-back btn-lg">Back to Events</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="text-center py-3">
    <p>&copy; 2025 Tikika. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
