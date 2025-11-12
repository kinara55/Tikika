<?php
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
require_once 'vendor/autoload.php';
$sessionManager = new SessionManager($conf);
$db = new Database($conf);
// Check if user is logged in
$sessionManager->requireLogin();
$userId = $sessionManager->getUserId();
// Prepare for payment
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cartItems)) {
    $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Your cart is empty. Please add some tickets first.</div>');
    header('Location: events.php');
    exit;
}
//Stripe secret key 
$stripe_secret_key = $conf['stripe_secret_key'];
\Stripe\Stripe::setApiKey($stripe_secret_key);

//  base URL creation instead of manually writting http://localhost/tikika
$base_url = $conf['site_url'];
function createOrder($db, $userId, $cartItems) {
    $status = 'pending';
    $totalAmount = 0;
    // Calculate total amount
    foreach ($cartItems as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }
    // Create individual order record
    $orderData = [
        'user_id' => $userId,
        'total_amount' => $totalAmount,
        'status' => $status
    ];
    //inserting order id into db 
    $orderDbId = $db->insert('orders', $orderData);
    // Creating individual order items
    foreach ($cartItems as $item) {
        $orderItemData = [
            'order_id' => $orderDbId,
            'event_id' => $item['event_id'],
            'ticket_type' => $item['ticket_type'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price']
        ];
        
        $db->insert('order_items', $orderItemData);
    }
    return $orderDbId; // Return the database ID instead of uniqid()
}
// Preparing akk the line items for the checkout session
$line_items = [];

foreach ($cartItems as $item) {
    $line_items[] = [
        "quantity" => $item['quantity'],
        "price_data" => [
            "currency" => "kes",
            "unit_amount" => $item['price'] * 100, // Convert to cents
            "product_data" => [
                "name" => $item['event_title'] . ' - ' . $item['ticket_type'],
                "description" => "Event: " . $item['event_title'] . "\nDate: " . $item['event_date'] . "\nLocation: " . $item['event_location'],
                "images" => [$item['event_image']],
            ],
        ],
    ];
}
// Create the order and store it in the database
$orderId = createOrder($db, $userId, $cartItems);
// Create the checkout session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "{$base_url}payment_success.php?session_id={CHECKOUT_SESSION_ID}&user_id={$userId}&order_id={$orderId}",
        "cancel_url" => "{$base_url}events.php",
        "line_items" => $line_items,
        "metadata" => [
            "order_id" => $orderId,
            "user_id" => $userId
        ],
    ]);
    // Set order details in session
    $_SESSION['order_details'] = [
        'order_id' => $orderId,
        'session_id' => $checkout_session->id
    ];
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Stripe API error
    error_log("Stripe Error: " . $e->getMessage());
    $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Payment error: ' . htmlspecialchars($e->getMessage()) . '</div>');
    header('Location: cart.php');
    exit;
} catch (Exception $e) {
    // General error
    error_log("Payment Process Error: " . $e->getMessage());
    $sessionManager->setMessage('msg', '<div style="color: #e74c3c; margin-bottom: 1rem;">Error creating payment session. Please try again. Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
    header('Location: cart.php');
    exit;
}
// Redirect to the checkout session URL
http_response_code(303);
header("Location: " . $checkout_session->url);
