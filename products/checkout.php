<?php
session_start(); // Start the session
include '../includes/db.php';
include '../includes/header.php';

// Retrieve order items and total amount from the POST data
$order_items = $_POST['order_items'] ?? [];
$total_amount = $_POST['total_amount'] ?? 0;

// Store order items in the session for later use in payment.php
$_SESSION['order_items'] = $order_items;
?>

<h1>Checkout</h1>
<form id="checkoutForm">
    <label for="name">Full Name:</label>
    <input type="text" id="name" name="name" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="phone">Phone:</label>
    <input type="text" id="phone" name="phone" required><br><br>

    <!-- Shipping/Pickup Option -->
    <label>Delivery Option:</label><br>
    <input type="radio" id="shipping" name="delivery_option" value="shipping" checked> Ship to Address<br>
    <input type="radio" id="pickup" name="delivery_option" value="pickup"> Pickup in Store<br><br>

    <!-- Shipping Address  -->
    <div id="shippingAddressSection">
        <label for="address">Shipping Address:</label>
        <textarea id="address" name="address" required></textarea><br><br>
    </div>

    <input type="hidden" id="totalAmount" value="<?php echo $total_amount; ?>">

    <button class="submitBtn" type="button" onclick="initiatePayment()">Proceed to Payment</button>
</form>

<script>
// Pass order items to the frontend
const orderItems = <?php echo json_encode($order_items); ?>;

// Show/hide shipping address based on delivery option
document.getElementById('shipping').addEventListener('change', function () {
    document.getElementById('shippingAddressSection').style.display = 'block';
});

document.getElementById('pickup').addEventListener('change', function () {
    document.getElementById('shippingAddressSection').style.display = 'none';
});

function initiatePayment() {
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked').value;
    const address = deliveryOption === 'shipping' ? document.getElementById('address').value : 'Pickup in Store';
    const total = document.getElementById('totalAmount').value;
    const orderItems = <?php echo json_encode($order_items); ?>;

    fetch('payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name, email, phone, deliveryOption, address, total, orderItems }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.payment_url) {
            // Load PayTabs iFrame
            const iframe = document.createElement('iframe');
            iframe.src = data.payment_url;
            iframe.style.width = '100%';
            iframe.style.height = '500px';
            document.body.appendChild(iframe);

            window.addEventListener('message', function (event) {
                if (event.origin !== 'https://secure-egypt.paytabs.com') return; 

                fetch('callback.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(event.data),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                });
            });
        } else {
            alert('Payment initialization failed.');
        }
    });
}
</script>


