<?php
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
$sessionManager = new SessionManager($conf);
$db = new Database($conf);
//Basically cant see cart if you are not logged in
$sessionManager->requireLogin();
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalAmount = 0;
//iterating through the cart items and adding the price and quantity
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Tikika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .cart-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1000px;
            overflow: hidden;
        }

        .cart-header {
            background: linear-gradient(45deg, #ff6b81, #ff914d);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .cart-body {
            padding: 2rem;
        }

        .cart-item {
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #ff6b81;
        }

        .btn-remove {
            background: #dc3545;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: background 0.3s ease;
        }

        .btn-remove:hover {
            background: #c82333;
            color: white;
        }

        .btn-checkout {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            color: white;
        }

        .btn-clear {
            background: #6c757d;
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 20px;
            transition: background 0.3s ease;
        }

        .btn-clear:hover {
            background: #5a6268;
            color: white;
        }

        .total-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ff6b81;
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
            <p>Review your selected tickets before checkout</p>
        </div>

        <div class="cart-body">
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some event tickets to get started!</p>
                    <a href="events.php" class="btn btn-primary">Browse Events</a>
                </div>
            <?php else: ?>
                <div id="cart-items">
                    <?php foreach ($cartItems as $index => $item): ?>
<div class="cart-item" data-index="<?php echo $index; ?>">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <?php
                                    // Handle image URL - use event image if available, otherwise use a default
                                    $imageUrl = $item['event_image'] ?? '';
                                    if (empty($imageUrl) || !file_exists($imageUrl)) {
                                        // Use a default image based on event ID
                                        $defaultImages = ['images/image1.jpg', 'images/image2.jpg', 'images/image3.jpg'];
                                        $eventId = $item['event_id'] ?? 1;
                                        $imageUrl = $defaultImages[($eventId - 1) % count($defaultImages)];
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>"
                                        alt="<?php echo htmlspecialchars($item['event_title']); ?>"
                                        class="event-image">
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-2"><?php echo htmlspecialchars($item['event_title']); ?></h5>
                                    <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($item['event_date']); ?></p>
                                    <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($item['event_location']); ?></p>
                                    <p class="mb-1"><strong>Ticket Type:</strong> <?php echo ucfirst($item['ticket_type']); ?></p>
                                    <p class="mb-0"><strong>Quantity:</strong> <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h5 class="text-primary">KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></h5>
                                </div>
                                <div class="col-md-2 text-center">
                                    <button class="btn btn-remove" onclick="removeFromCart(<?php echo $index; ?>)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="total-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4><i class="fas fa-receipt"></i> Order Summary</h4>
                            <p class="mb-0">Total Items: <?php echo count($cartItems); ?> | Total Amount</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h2>KSh <?php echo number_format($totalAmount, 2); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button onclick="clearCart()" class="btn btn-clear me-3">
                        <i class="fas fa-broom"></i> Clear Cart
                    </button>
                    <button onclick="proceedToCheckout()" class="btn btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeFromCart(index) {
            fetch('cart_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        index: index
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to remove item from cart');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                fetch('cart_manager.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'clear'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to clear cart');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        function proceedToCheckout() {
            console.log('Proceed to checkout clicked');
            console.log('Current session cart:', <?php echo json_encode($_SESSION['cart'] ?? []); ?>);
            console.log('Cart items count:', <?php echo count($_SESSION['cart'] ?? []); ?>);
            window.location.href = 'payment_process.php';
        }
    </script>
</body>

</html>