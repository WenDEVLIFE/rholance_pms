<?php
include '../../includes/auth_check.php';
include '../../config/database.php';

$branch = $_SESSION['branch_id'];

/* ✅ FIXED QUERY (JOIN ITEMS + INVENTORY) */
$products = $conn->query("
    SELECT 
        inventory.item_id,
        inventory.current_stock,
        items.name,
        COALESCE(items.price, 100) AS price
    FROM inventory
    JOIN items ON items.id = inventory.item_id
    WHERE inventory.current_stock > 0
    AND inventory.branch_id = $branch
");
?>

<link rel="stylesheet" href="../../assets/css/staff-dashboard.css">

<div class="main-content">

<h2>POS Terminal</h2>

<div class="pos-container">

    <!-- LEFT: PRODUCTS -->
    <div class="pos-products">
        <h3>Services</h3>
        
        <input type="text" id="search" placeholder="Search service...">

        <div class="product-list">
            <?php while ($p = $products->fetch_assoc()): ?>
                
                <div class="product-card"
                     data-id="<?= $p['item_id'] ?>"
                     data-name="<?= htmlspecialchars($p['name']) ?>"
                     data-price="<?= $p['price'] ?>">

                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                    <p>₱<?= number_format($p['price'], 2) ?></p>
                    <small>Stock: <?= $p['current_stock'] ?></small>

                </div>

            <?php endwhile; ?>
        </div>
    </div>

    <!-- RIGHT: CART -->
    <div class="pos-cart">
        <h3>Cart</h3>

        <table id="cart-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <h3>Total: ₱<span id="total">0.00</span></h3>

        <input type="number" id="payment" placeholder="Enter payment">

        <button id="checkout">Checkout</button>
    </div>

</div>

<script>
let cart = [];

/* ADD TO CART */
document.querySelectorAll('.product-card').forEach(card => {
    card.onclick = () => {
        const id = card.dataset.id;
        const name = card.dataset.name;
        const price = parseFloat(card.dataset.price);

        let item = cart.find(i => i.id === id);

        if (item) {
            item.qty++;
        } else {
            cart.push({ id, name, price, qty: 1 });
        }

        renderCart();
    };
});

/* RENDER CART */
function renderCart() {
    let tbody = document.querySelector('#cart-table tbody');
    tbody.innerHTML = '';

    let total = 0;

    cart.forEach(item => {
        let itemTotal = item.qty * item.price;

        let row = `
            <tr>
                <td>${item.name}</td>
                <td>${item.qty}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td>₱${itemTotal.toFixed(2)}</td>
            </tr>
        `;

        total += itemTotal;
        tbody.innerHTML += row;
    });

    document.getElementById('total').innerText = total.toFixed(2);
}

/* CHECKOUT */
document.getElementById('checkout').onclick = () => {

    let payment = parseFloat(document.getElementById('payment').value);
    let total = parseFloat(document.getElementById('total').innerText);

    if (!payment || payment < total) {
        alert("Insufficient payment");
        return;
    }

    fetch('process_sale.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cart, total, payment })
    })
    .then(res => res.json())
    .then(data => {
        alert("Sale completed!");
        location.reload();
    });

};

/* SEARCH FUNCTION */
document.getElementById('search').addEventListener('input', function() {
    let value = this.value.toLowerCase();

    document.querySelectorAll('.product-card').forEach(card => {
        let name = card.dataset.name.toLowerCase();

        card.style.display = name.includes(value) ? "block" : "none";
    });
});
</script>

</div>