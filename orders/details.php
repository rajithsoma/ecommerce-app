<?php include '../includes/db.php'; ?>
<?php include '../includes/header.php'; ?>

<?php
// Get the order ID from the URL
$order_id = $_GET['id'];

// Fetch order details
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch order items
$items_stmt = $conn->prepare("SELECT p.name, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch refund history 
$refunds_stmt = $conn->prepare("SELECT * FROM refunds WHERE order_id = ?");
$refunds_stmt->execute([$order_id]);
$refunds = $refunds_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Order Details</h1>
<p><strong>Order ID:</strong> <?= $order['id'] ?></p>
<p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
<p><strong>Status:</strong> <?= $order['status'] ?></p>

<h2>Products</h2>
<ul>
    <?php foreach ($items as $item): ?>
        <li><?= $item['name'] ?> (Quantity: <?= $item['quantity'] ?>)</li>
    <?php endforeach; ?>
</ul>

<h2>Payment Request Payload</h2>
<pre><?= json_encode(json_decode($order['payment_request_payload']), JSON_PRETTY_PRINT) ?></pre>

<h2>Payment Response Payload</h2>
<pre><?= json_encode(json_decode($order['payment_response_payload']), JSON_PRETTY_PRINT) ?></pre>

<?php if ($order['status'] === 'refunded'): ?>
    <h2>Refund History</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Refund Date</th>
                <th>Refund Request Payload</th>
                <th>Refund Response Payload</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($refunds as $refund): ?>
                <tr>
                    <td><?= $refund['refund_date'] ?></td>
                    <td><pre><?= json_encode(json_decode($refund['refund_request_payload']), JSON_PRETTY_PRINT) ?></pre></td>
                    <td><pre><?= json_encode(json_decode($refund['refund_response_payload']), JSON_PRETTY_PRINT) ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if ($order['status'] === 'completed'): ?>
    <h2>Initiate Refund</h2>
    <form action="refund.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <label for="refund_amount">Refund Amount:</label>
        <input type="number" id="refund_amount" name="refund_amount" required><br><br>
        <label for="refund_reason">Refund Reason:</label>
        <textarea id="refund_reason" name="refund_reason" required></textarea><br><br>
        <button class="submitBtn" type="submit">Process Refund</button>
    </form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>