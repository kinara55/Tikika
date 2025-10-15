<?php
session_start();
require_once 'conf.php';
require_once 'session/session_manager.php';
require_once 'DB/database.php';
$sessionManager = new SessionManager($conf);
$db = new Database($conf);
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // Use switch case to dynamically excecute and action based on input 
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'add':
                addToCart($data['item']);
                break;
            case 'remove':
                removeFromCart($data['index']);
                break;
            case 'clear':
                clearCart();
                break;
            case 'load':
                loadCart();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
}
function addToCart($item) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $cartItems = &$_SESSION['cart'];
    $found = false;

    // Check if same event and ticket type already exists
    foreach ($cartItems as &$cartItem) {
        if ($cartItem['event_id'] === $item['event_id'] && $cartItem['ticket_type'] === $item['ticket_type']) {
            $cartItem['quantity'] += $item['quantity'];
            $found = true;
            break;
        }
    }
      //is it there ,if not add it 
    if (!$found) {
        $cartItems[] = $item;
    }
    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
}
// remove a siingle item basically popping it out
 function removeFromCart($index) {
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
    }
    echo json_encode(['success' => true, 'cartItems' => $_SESSION['cart']]);
}
// Wiping the cart out
function clearCart() {
    $_SESSION['cart'] = [];
    echo json_encode(['success' => true, 'cartItems' => []]);
}
// Loading the cart out 
function loadCart() {
    $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
}
