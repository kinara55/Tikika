<?php
// Start session and check login status
require_once 'conf.php';
require_once 'session/session_manager.php';

$sessionManager = new SessionManager($conf);
$isLoggedIn = $sessionManager->isLoggedIn();
$userName = $isLoggedIn ? $sessionManager->getUsername() : '';
$userRole = $isLoggedIn ? $sessionManager->getRoleId() : 0;
$currentPage = 'contact'; // Set current page for active nav highlighting
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - Tikika</title>
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

  <!-- Contact Section -->
  <section class="container my-5">
    <h1 class="text-center mb-4">Contact Us</h1>
    <p class="lead text-center">
      Get in touch with the Tikika team. We're here to help you discover amazing events and make your experience unforgettable.
    </p>
    
    <div class="row text-center mt-4">
      <div class="col-md-3">
        <div class="info-box">
          <h3>📧 Email</h3>
          <p>Send us an email and we'll respond within 24 hours.</p>
          <a href="mailto:info@tikika.com" class="btn btn-outline-danger">info@tikika.com</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box">
          <h3>📞 Phone</h3>
          <p>Speak directly with our support team.</p>
          <a href="tel:+254700000000" class="btn btn-outline-danger">+254 700 000 000</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box">
          <h3>📍 Location</h3>
          <p>Come see us at our office in Nairobi.</p>
          <p class="text-muted">Nairobi, Kenya</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box">
          <h3>🕒 Hours</h3>
          <p>We're here to help during business hours.</p>
          <p class="text-muted">Mon - Fri: 9:00 AM - 6:00 PM</p>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="row justify-content-center mt-5">
      <div class="col-lg-8">
        <div class="info-box">
          <h3 class="text-center mb-4">Send us a Message</h3>
          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="subject" class="form-label">Subject</label>
              <input type="text" class="form-control" id="subject" required>
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">Message</label>
              <textarea class="form-control" id="message" rows="5" required></textarea>
            </div>
            <div class="text-center">
              <button type="submit" class="btn btn-danger btn-lg px-5">
                <i class="fas fa-paper-plane me-2"></i>Send Message
              </button>
            </div>
          </form>
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
