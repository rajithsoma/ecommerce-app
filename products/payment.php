<?php
session_start(); // Start the session
include '../includes/db.php';
include '../includes/config.php';

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve the total amount and delivery details from the request
$total_amount = $data['total'];
$delivery_option = $data['deliveryOption'];
$shipping_address = $data['address'];
$order_items = $data['orderItems'];

 // Store order items in the session for later use in callback.php
 $_SESSION['order_items'] = $order_items;

$stmt = $conn->prepare("INSERT INTO orders (delivery_option, shipping_address, payment_request_payload, status) VALUES (?, ?, ?, 'pending')");
$stmt->execute([$delivery_option, $shipping_address, json_encode($data)]);

// Retrieve the last inserted order ID
$order_id = $conn->lastInsertId();

// Store order items in the database
$stmt = $conn->prepare("INSERT INTO payment_sessions (cart_id, order_items) VALUES (?, ?)");
$stmt->execute([$order_id, json_encode($order_items)]);

// Prepare order details
$currency = PAYTABS_CURRENCY;
$customer_details = [
    'name' => $data['name'],
    'email' => $data['email'],
    'phone' => $data['phone'],
    'address' => $shipping_address,
];

// PayTabs API request
$payload = [
    'profile_id' => PAYTABS_PROFILE_ID,
    'tran_type' => 'sale',
    'tran_class' => 'ecom',
    'cart_id' => $order_id,
    'cart_amount' => $total_amount,
    'cart_description' => 'Order Payment',
    'cart_currency' => $currency,
    'customer_details' => $customer_details,
    'callback' => 'https://7173-2402-4000-13e1-8bfa-c59-d729-5529-e258.ngrok-free.app/ecommerce-app/products/callback.php',
    'return' => 'https://7173-2402-4000-13e1-8bfa-c59-d729-5529-e258.ngrok-free.app/ecommerce-app/products/success.php',
    'hide_shipping' => true, // Hide unnecessary fields
    'hide_billing' => true,
    'framed' => true
];

$ch = curl_init('https://secure-egypt.paytabs.com/payment/request');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . PAYTABS_SERVER_KEY,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['redirect_url']) {
    echo json_encode(['payment_url' => $result['redirect_url']]);
} else {
    echo json_encode(['error' => 'Payment initialization failed.']);
}
?>
