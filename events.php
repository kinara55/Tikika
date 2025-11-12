<?php
// Start session and check login status
require_once 'conf.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$isLoggedIn = $sessionManager->isLoggedIn();
$userName = $isLoggedIn ? $sessionManager->getUsername() : '';
$userRole = $isLoggedIn ? $sessionManager->getRoleId() : 0;
$currentPage = 'events'; // Set current page for active nav highlighting

require_once 'DB/database.php';
$db = new Database($conf);

// Fetch events from DB
$events = $db->fetchAll("SELECT id, title, description, venue, start_datetime, end_datetime, status, capacity, category_id, image_url, price FROM events ORDER BY start_datetime ASC");

// For each event, check if tickets are sold out
foreach ($events as &$event) {
    $ticket = $db->fetchOne("SELECT SUM(quantity - sold) AS available FROM tickets WHERE event_id = ?", [$event['id']]);
    $event['sold_out'] = ($ticket['available'] ?? 0) <= 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events - Tikika</title>
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

    /* Headings */
    h1 {
      color: #ff416c;
      font-weight: bold;
    }

    h3 {
      color: #ff4b2b;
      font-weight: 600;
    }

    /* Lead Paragraph */
    .lead {
      color: #333;
      font-size: 1.2rem;
      max-width: 800px;
      margin: 0 auto;
    }

    /* Cards / Info Section */
    .info-box {
      background: #fff5f5;
      border: 2px solid #ff416c;
      border-radius: 15px;
      padding: 20px;
      margin-top: 15px;
      transition: 0.3s;
    }

    .info-box:hover {
      background: #ffe5e5;
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(255, 65, 108, 0.2);
    }

    /* Event Cards */
    .event-card {
      background: #fff5f5;
      border: 2px solid #ff416c;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      transition: 0.3s;
    }

    .event-card:hover {
      background: #ffe5e5;
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(255, 65, 108, 0.2);
    }

    .event-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
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

  <!-- Events Section -->
  <section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-center mb-0">Upcoming Events</h1>
      <?php if ($isLoggedIn && $userRole == 2): ?>
      <a href="create_event.php" class="btn btn-danger btn-lg">
        <i class="fas fa-plus me-2"></i>Create Event
      </a>
      <?php endif; ?>
    </div>
    
    <p class="lead text-center mb-5">
      Discover amazing events happening near you. From concerts to workshops, find your next unforgettable experience.
    </p>

    <!-- Events Grid -->
    <div class="row">
      <?php foreach($events as $event): ?>
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="event-card">
          <img src="<?php echo htmlspecialchars($event['image_url'] ?? 'images/default.jpg'); ?>" class="event-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
          <h3><?php echo htmlspecialchars($event['title']); ?></h3>
          <p class="text-muted"><?php echo htmlspecialchars($event['description']); ?></p>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">
              <i class="fas fa-calendar me-1"></i><?php echo date('F d, Y', strtotime($event['start_datetime'])); ?>
            </small>
            <small class="text-muted">
              <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($event['venue'] ?? ''); ?>
            </small>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="h4 text-danger mb-0">Ksh <?php echo number_format($event['price'], 2); ?></span>
            <?php if ($event['sold_out']): ?>
              <span class="badge bg-danger">Sold Out</span>
            <?php endif; ?>
            <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-danger">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-5">
      <button class="btn btn-outline-danger btn-lg">
        <i class="fas fa-plus me-2"></i>Load More Events
      </button>
    </div>
  </section>

  <!-- Footer -->
  <footer class="text-center py-3">
    <p>&copy; 2025 Tikika. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
