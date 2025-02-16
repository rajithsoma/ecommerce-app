

<h1>Payment Successful!</h1>
<p>Thank you for your purchase. Your order has been successfully processed.</p>

<?php
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>Order Details</h2>";
    echo "<p><strong>Order ID:</strong> {$order['id']}</p>";
    echo "<p><strong>Order Date:</strong> {$order['order_date']}</p>";
    echo "<p><strong>Status:</strong> {$order['status']}</p>";

    echo "<h2>Payment Response</h2>";
    echo "<pre>" . json_encode(json_decode($order['payment_response_payload']), JSON_PRETTY_PRINT) . "</pre>";
}
?>

