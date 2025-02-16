<?php include '../includes/db.php'; ?>
<?php include '../includes/header.php'; ?>

<h1>My Orders</h1>
<table border="1">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $conn->query("SELECT * FROM orders");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['order_date']}</td>
                    <td>{$row['status']}</td>
                    <td><a href='details.php?id={$row['id']}'>View Details</a></td>
                  </tr>";
        }
        ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>