<?php
// Start session and check login status
require_once 'conf.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$isLoggedIn = $sessionManager->isLoggedIn();
$userName = $isLoggedIn ? $sessionManager->getUsername() : '';
$userRole = $isLoggedIn ? $sessionManager->getRoleId() : 0;
$currentPage = 'about'; // Set current page for active nav highlighting
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - Tikika</title>
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

  <!-- About Section -->
  <section class="container my-5">
    <h1 class="text-center mb-4">About Tikika</h1>
    <p class="lead text-center">
      Tikika is your one-stop platform to discover, explore, and book tickets for the best concerts and events in town. 
      Our mission is to connect event organizers with music lovers, making the process of finding and attending concerts seamless and fun.
    </p>
    <div class="row text-center mt-4">
      <div class="col-md-4">
        <div class="info-box">
          <h3>🎶 Discover</h3>
          <p>Browse through exciting concerts and events happening near you.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box">
          <h3>🎟 Book</h3> 
          <p>Secure your tickets easily with our user-friendly booking system.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box">
          <h3>🎉 Enjoy</h3>
          <p>Experience unforgettable moments with your favorite artists and friends.</p>
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




