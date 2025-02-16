<?php
//session_start(); // Ensure session is started
include '../includes/db.php';

// Log the raw incoming request
$raw_input = file_get_contents('php://input');
file_put_contents('callback_log.txt', "Raw Input:\n" . $raw_input . PHP_EOL, FILE_APPEND);

$response = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('callback_log.txt', "JSON Decoding Error: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
    http_response_code(400); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON data',
    ]);
    exit();
}

file_put_contents('callback_log.txt', "Decoded Response:\n" . print_r($response, true) . PHP_EOL, FILE_APPEND);

if (isset($response['tran_ref']) && $response['payment_result']['response_status'] === 'A') {
    $order_id = $response['cart_id'];
    $payment_request_payload = json_encode($response);
    $payment_response_payload = json_encode($response['payment_result']);

    // Update the order 
    $stmt = $conn->prepare("UPDATE orders SET payment_response_payload = ?, status = 'completed' WHERE id = ?");
    $stmt->execute([$payment_response_payload, $order_id]);

    if ($order_id) {
        // Retrieve order items from payment_sessions
        $stmt = $conn->prepare("SELECT order_items FROM payment_sessions WHERE cart_id = ?");
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchColumn();
    
        if ($orderItems) {
            $orderItemsArray = json_decode($orderItems, true);
    
            // Process the order items
            foreach ($orderItemsArray as $productId => $quantity) {
                if ($quantity > 0) {
                    $insert = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
                    $insert->execute([$order_id, $productId, $quantity]);
                }
            }
    
            // Clean up after successful processing
            $delete = $conn->prepare("DELETE FROM payment_sessions WHERE cart_id = ?");
            $delete->execute([$order_id]);
        } else {
            file_put_contents('callback_log.txt', "No order items found for cart_id: $order_id\n", FILE_APPEND);
        }
    }
    // Respond with HTTP 200 and a JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment recorded',
        'redirect_url' => 'success.php' 
    ]);
} else {
    // Payment failed
    http_response_code(200);
    echo json_encode([
        'status' => 'failed',
        'message' => 'Payment failed',
        'redirect_url' => 'error.php' 
    ]);
}
?>
