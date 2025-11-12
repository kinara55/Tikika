<?php
session_start();
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
require_once 'vendor/autoload.php';
$sessionManager = new SessionManager($conf);
$db = new Database($conf);
// Ensure order_id and session_id are set in the URL parameters
if (!isset($_GET['order_id']) || !isset($_GET['session_id'])) {
    die("Order ID or session ID not specified.");
}
$orderId = $_GET['order_id'];
$sessionId = $_GET['session_id'];
$userId = $sessionManager->getUserId();
// stripe key from configuration
$stripe_secret_key = $conf['stripe_secret_key'];
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Verify payment status with Stripe
try {
    $stripe_session = \Stripe\Checkout\Session::retrieve($sessionId);
      // Status is an object in stripe api that has multiple statuses 
    if ($stripe_session->payment_status === 'paid') {
        $db->update('orders', ['status' => 'paid'], 'id = ?', [$orderId]);
        
        $itemsToUpdate = $db->fetchAll("SELECT event_id, ticket_type, quantity FROM order_items WHERE order_id = ?", [$orderId]);
        foreach ($itemsToUpdate as $item) {
            $db->query("UPDATE tickets SET sold = sold + ? WHERE event_id = ? AND LOWER(type) = LOWER(?)", 
                [$item['quantity'], $item['event_id'], $item['ticket_type']]);
        }
        
        unset($_SESSION['cart']);
        unset($_SESSION['cartItems']);
        
        $paymentSuccess = true;
        $statusMessage = "Payment successful! Your tickets have been confirmed.";
        $statusColor = "green";
    } else {
        // Update order status in the database as unsuccessful
        $db->update('orders', ['status' => 'cancelled'], 'id = ?', [$orderId]);
        
        $paymentSuccess = false;
        $statusMessage = "Payment was not successful.";
        $statusColor = "red";
    }
} catch (Exception $e) {
    $paymentSuccess = false;
    $statusMessage = "Error verifying payment: " . $e->getMessage();
    $statusColor = "red";
}
// Getting your order details from the db to be used in  receipt
$order = $db->fetchOne(
    "SELECT o.*, u.full_name, u.email FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = ? AND o.user_id = ?",
    [$orderId, $userId]
);

if (!$order) {
    $paymentSuccess = false;
    $statusMessage = "Order not found. Please contact support if you believe this is an error.";
    $statusColor = "red";
    $order = null; // Set to null to prevent further errors
}
// Get order items
$orderItems = $db->fetchAll(
    "SELECT oi.*, e.title as event_title, e.start_datetime, e.end_datetime, e.venue as venue_name
     FROM order_items oi 
     LEFT JOIN events e ON oi.event_id = e.id 
     WHERE oi.order_id = ?",
    [$orderId]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Tikika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .receipt-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 800px;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(45deg, #ff6b81, #ff914d);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .receipt-body {
            padding: 2rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .btn-download {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            color: white;
        }
        .btn-home {
            background: linear-gradient(45deg, #ff6b81, #ff914d);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="receipt">
        <div class="receipt-header">
            <h1><i class="fas fa-ticket-alt"></i> Tikika</h1>
            <h2>Payment Confirmation</h2>
            <div class="status-badge status-<?php echo $paymentSuccess ? 'success' : 'failed'; ?>">
                <?php echo $statusMessage; ?>
            </div>
        </div>

        <div class="receipt-body">
            <?php if ($order): ?>
            <div class="row">
                <div class="col-md-6">
                    <h5><strong>Order Details</strong></h5>
                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="col-md-6">
                    <h5><strong>Payment Summary</strong></h5>
                    <p><strong>Total Amount:</strong> KSh <?php echo number_format($order['total_amount'], 2); ?></p>
                    <p><strong>Status:</strong> <span style="color: <?php echo $statusColor; ?>;"><?php echo ucfirst($order['status']); ?></span></p>
                </div>
            </div>
            <hr>  
            <h5><strong>Event Tickets</strong></h5>
            <?php foreach ($orderItems as $item): ?>
            <div class="order-item">
                <div class="row">
                    <div class="col-md-8">
                        <h6><?php echo htmlspecialchars($item['event_title']); ?></h6>
                        <p class="mb-1"><strong>Ticket Type:</strong> <?php echo htmlspecialchars($item['ticket_type']); ?></p>
                        <p class="mb-1"><strong>Quantity:</strong> <?php echo $item['quantity']; ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($item['start_datetime'])); ?></p>
                        <p class="mb-0"><strong>Venue:</strong> <?php echo htmlspecialchars($item['venue_name']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <p class="mb-0"><strong>KSh <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></strong></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="text-center py-4">
                <h5>Order Not Found</h5>
                <p>The order you're looking for could not be found. This might be because:</p>
                <ul class="text-start">
                    <li>The order ID is incorrect</li>
                    <li>The order belongs to a different user</li>
                    <li>The order has been deleted</li>
                </ul>
                <p>Please contact support if you believe this is an error.</p>
            </div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-home me-3">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <button id="download-btn" class="btn btn-download">
                    <i class="fas fa-download"></i> Download Receipt
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script>
        document.getElementById('download-btn').addEventListener('click', function() {
            html2canvas(document.getElementById('receipt'), {
                scale: 2,
                useCORS: true
            }).then(function(canvas) {
                var link = document.createElement('a');
                link.href = canvas.toDataURL();
                link.download = 'tikika-receipt-<?php echo $orderId; ?>.png';
                link.click();
            });
        });
    </script>
</body>
</html>
