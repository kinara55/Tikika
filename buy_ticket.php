<?php
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
$sessionManager = new SessionManager($conf);
$db = new Database($conf);

// Check if user is logged in
$sessionManager->requireLogin('forms.html');
$events = [
  1 => [
    "title" => "AfroBeats Night",
    "price" => 1500,
    "date" => "October 15, 2025",
    "location" => "Nairobi Arena",
    "image" => "images/image1.jpg"
  ],
  2 => [
    "title" => "Jazz Festival",
    "price" => 2000,
    "date" => "November 2, 2025", 
    "location" => "Uhuru Gardens",
    "image" => "images/image2.jpg"
  ],
  3 => [
    "title" => "Bambika na 3 men Army",
    "price" => 1000,
    "date" => "December 10, 2025",
    "location" => "Beer District", 
    "image" => "images/image3.jpg"
  ]
];
// Get the event ID (default to 1 if not found)
$id = isset($_GET['id']) && isset($events[$_GET['id']]) ? (int)$_GET['id'] : 1;
$event = $events[$id];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketType = $_POST['ticketType'] ?? 'regular';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Calculate price based on ticket type
    $basePrice = $event['price'];
    $multiplier = 1;
    switch ($ticketType) {
        case 'vip':
            $multiplier = 2;
            break;
        case 'vvip':
            $multiplier = 3;
            break;
    }
    $totalPrice = $basePrice * $multiplier;
    // Prepare cart item
    $cartItem = [
        'event_id' => $id,
        'event_title' => $event['title'],
        'event_date' => $event['date'],
        'event_location' => $event['location'],
        'event_image' => $event['image'],
        'ticket_type' => $ticketType,
        'quantity' => $quantity,
        'price' => $totalPrice
    ];
    // Add to cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item already exists in cart
    $itemExists = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['event_id'] == $id && $item['ticket_type'] == $ticketType) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $itemExists = true;
            break;
        }
    }
    
    // Add new item if it doesn't exist
    if (!$itemExists) {
        $_SESSION['cart'][] = $cartItem;
    }
    
    echo json_encode(['success' => true, 'message' => 'Added to cart!', 'cartItem' => $cartItem]);
    exit;
}
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
      <a class="navbar-brand" href="index.php">Tikika</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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
        <form id="ticketForm">
          <div class="mb-3">
            <label for="ticketType" class="form-label">Ticket Type</label>
            <select id="ticketType" name="ticketType" class="form-select" onchange="updatePrice()">
              <option value="regular">Regular - Ksh <?php echo $event['price']; ?></option>
              <option value="vip">VIP - Ksh <?php echo $event['price'] * 2; ?></option>
              <option value="vvip">VVIP - Ksh <?php echo $event['price'] * 3; ?></option>
            </select>
          </div>

<div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" max="10" value="1" onchange="updatePrice()">
          </div>

          <div class="mb-3">
            <label class="form-label">Total Price</label>
            <div id="totalPrice" class="form-control-plaintext fw-bold text-primary">Ksh <?php echo $event['price']; ?></div>
          </div>

          <button type="submit" class="btn-gradient w-100">Add to Cart</button>
        </form>
        
        <div class="text-center mt-3">
          <a href="cart.php" class="btn btn-outline-primary">View Cart</a>
        </div>
      </div>
    </div>
  </section>
  <!-- Footer -->
  <footer class="text-center py-3">
    <p>&copy; 2025 Tikika. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    const basePrice = <?php echo $event['price']; ?>;
    function updatePrice() {
      const ticketType = document.getElementById('ticketType').value;
      const quantity = parseInt(document.getElementById('quantity').value);
      
      let multiplier = 1;
      switch (ticketType) {
        case 'vip':
          multiplier = 2;
          break;
        case 'vvip':
          multiplier = 3;
          break;
      }
      const totalPrice = basePrice * multiplier * quantity;
      document.getElementById('totalPrice').textContent = 'Ksh ' + totalPrice.toLocaleString();
    }
    document.getElementById('ticketForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      formData.append('event_id', <?php echo $id; ?>);
      
      fetch('buy_ticket.php?id=<?php echo $id; ?>', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Ticket added to cart successfully!');
          // Adding  to cart logic 
           // pass as an argument to addTocart  Function 
          addToCart(data.cartItem);
        } else {
          alert('Failed to add ticket to cart');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    });
    // Adding to cart logic     
    function addToCart(cartItem) {
      fetch('cart_manager.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', item: cartItem })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Added to cart:', data.cartItems);
        }
      })
      .catch(error => console.error('Error:', error));
    }
  </script>
</body>
</html>
