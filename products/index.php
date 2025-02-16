<?php include '../includes/db.php'; ?>
<?php include '../includes/header.php'; ?>

<h1>Products</h1>
<form action="checkout.php" method="POST">
    <table border="1">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->query("SELECT * FROM products");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                        <td>{$row['name']}</td>
                        <td class='price' data-price='{$row['price']}'>{$row['price']}</td>
                        <td>
                            <input type='number' name='order_items[{$row['id']}]' value='0' min='0' class='quantity'>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    <p><strong>Total Amount: </strong><span id="totalAmount">0</span></p>
    <input type="hidden" id="totalAmountInput" name="total_amount" value="0">
    <div>
    <button class="submitBtn" type="submit">
        Proceed to Checkout
    </button>
</div>

</form>

<script>
function calculateTotal() {
    let total = 0;
    const prices = document.querySelectorAll('.price');
    const quantities = document.querySelectorAll('.quantity');

    prices.forEach((price, index) => {
        const productPrice = parseFloat(price.getAttribute('data-price'));
        const productQuantity = parseFloat(quantities[index].value);
        total += productPrice * productQuantity;
    });

    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('totalAmountInput').value = total.toFixed(2);
}

document.querySelectorAll('.quantity').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

// Calculate total on page load
calculateTotal();
</script>

<?php include '../includes/footer.php'; ?>
