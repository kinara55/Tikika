<?php

$events = [
  1 => [
    "title" => "AfroBeats Night",
    "price" => 1500
  ],
  2 => [
    "title" => "Jazz Festival",
    "price" => 2000
  ],
  3 => [
    "title" => "Bambika na 3 men Army",
    "price" => 1000
  ]
];

// Get the event ID (default to 1 if not found)
$id = isset($_GET['id']) && isset($events[$_GET['id']]) ? (int)$_GET['id'] : 1;
$event = $events[$id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buy Ticket - <?php echo $event['title']; ?> | Tikika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Gradient Navbar */
    .navbar {
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
    }

    .navbar-brand, .nav-link {
      color: #fff !important;
      font-weight: 500;
    }

    .navbar-brand:hover, .nav-link:hover {
      color: #ffd6d6 !important;
    }

    /* Form Styling */
    h1 {
      color: #ff416c;
      font-weight: bold;
    }

    .form-label {
      color: #333;
      font-weight: 500;
    }

    .form-control, .form-select {
      border: 2px solid #ff4b2b;
      border-radius: 10px;
    }

    .form-control:focus, .form-select:focus {
      border-color: #ff416c;
      box-shadow: 0 0 8px rgba(255, 65, 108, 0.4);
    }

    /* Button Styling */
    .btn-gradient {
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
      border: none;
      color: #fff;
      font-weight: bold;
      padding: 12px;
      border-radius: 10px;
      transition: 0.3s;
    }

    .btn-gradient:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }

    /* Footer */
    footer {
      background: linear-gradient(90deg, #ff416c, #ff4b2b);
      color: #fff;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html">Tikika</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="events.html">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Ticket Purchase Form -->
  <section class="container my-5">
    <h1 class="text-center mb-4">Buy Ticket</h1>
    <p class="text-center">Fill in your details to book your ticket for <strong><?php echo $event['title']; ?></strong>.</p>

    <div class="row justify-content-center mt-4">
      <div class="col-md-6">
        <form>
          <div class="mb-3">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" id="fullname" class="form-control" placeholder="Enter your full name">
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" class="form-control" placeholder="Enter your email">
          </div>

          <div class="mb-3">
            <label for="ticketType" class="form-label">Ticket Type</label>
            <select id="ticketType" class="form-select">
              <option value="regular">Regular - Ksh <?php echo $event['price']; ?></option>
              <option value="vip">VIP - Ksh <?php echo $event['price'] * 2; ?></option>
              <option value="vvip">VVIP - Ksh <?php echo $event['price'] * 3; ?></option>
            </select>
          </div>

          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" id="quantity" class="form-control" min="1" max="10" value="1">
          </div>

          <button type="submit" class="btn-gradient w-100">Proceed to Payment</button>
        </form>
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
