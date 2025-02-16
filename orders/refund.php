<?php
session_start(); // Start the session
include '../includes/db.php';
include '../includes/config.php';

// Get the order ID and refund details from the request
$order_id = $_POST['order_id'];
$refund_amount = $_POST['refund_amount'];
$refund_reason = $_POST['refund_reason'];

// Fetch the order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit();
}

// Prepare the refund request payload
$refund_request_payload = json_encode([
    'order_id' => $order_id,
    'refund_amount' => $refund_amount,
    'refund_reason' => $refund_reason,
]);

// Simulate sending the refund request to PayTabs (replace with actual API call)
$refund_response_payload = json_encode([
    'status' => 'success',
    'refund_id' => uniqid(),
    'message' => 'Refund processed successfully',
]);

// Insert the refund into the database
$stmt = $conn->prepare("INSERT INTO refunds (order_id, refund_request_payload, refund_response_payload) VALUES (?, ?, ?)");
$stmt->execute([$order_id, $refund_request_payload, $refund_response_payload]);

// Update the order status to 'refunded'
$stmt = $conn->prepare("UPDATE orders SET status = 'refunded' WHERE id = ?");
$stmt->execute([$order_id]);

// Respond with success
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Refund processed successfully',
]);
?>