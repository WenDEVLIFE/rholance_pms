<?php
include '../../includes/auth_check.php';
include '../../config/database.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../../index.php"); exit; }

$branch = $_SESSION['branch_id'];

$products = $conn->query("
    SELECT 
        inventory.item_id,
        inventory.current_stock,
        items.name,
        items.category,
        COALESCE(items.price, 0) AS price,
        items.image
    FROM inventory
    JOIN items ON items.id = inventory.item_id
    WHERE inventory.current_stock > 0
    AND inventory.branch_id = $branch
    ORDER BY items.category ASC, items.name ASC
");
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>POS Terminal</h1>
        <p>Branch: <strong><?= $branch == 1 ? 'Dasmariñas' : 'Biñan' ?></strong></p>
    </div>

    <div class="row g-4">
        <!-- LEFT: PRODUCT SELECTION -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-boxes me-2 text-amber"></i>Available Materials</span>
                    <div style="width:200px;">
                        <input type="text" id="posSearch" class="form-control form-control-sm" placeholder="Search material...">
                    </div>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="row g-3" id="productList">
                        <?php while ($p = $products->fetch_assoc()): ?>
                        <div class="col-6 col-sm-4 pos-item" data-id="<?= $p['item_id'] ?>" 
                             data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>">
                            <div class="card h-100 rh-proj-card cursor-pointer hover-shadow" style="cursor:pointer;">
                                <div class="rh-proj-thumb" style="height:120px;">
                                    <img src="<?= BASE_URL ?><?= $p['image'] ?? 'assets/images/no-image.png' ?>" 
                                         onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'" alt="">
                                    <span class="badge bg-dark status-float">₱<?= number_format($p['price'],0) ?></span>
                                </div>
                                <div class="p-2">
                                    <div class="fw-700 small text-truncate"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.7rem;">Stock: <?= $p['current_stock'] ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: CART & CHECKOUT -->
        <div class="col-12 col-lg-5">
            <div class="card h-100 shadow-sm border-amber-light">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-shopping-cart me-2"></i>Current Order
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="min-height: 300px;">
                        <table class="table table-sm align-middle" id="cartTable">
                            <thead class="bg-light">
                                <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Total</th><th></th></tr>
                            </thead>
                            <tbody>
                                <!-- JS Populated -->
                            </tbody>
                        </table>
                        <div id="emptyCart" class="text-center py-5 text-muted">
                            <i class="fas fa-cart-plus fs-2 mb-2 d-block opacity-25"></i>
                            Select items to start order
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-600">Subtotal</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h4 class="fw-800 text-amber">Total Due</h4>
                        <h4 class="fw-800 text-amber" id="total">₱0.00</h4>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Payment Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="paymentInput" class="form-control form-control-lg fw-700" placeholder="0.00">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-success d-none" id="changeRow">
                        <span class="fw-600">Change</span>
                        <span class="fw-800" id="changeAmount">₱0.00</span>
                    </div>
                    <button class="btn btn-primary btn-lg w-100 py-3 fw-800" id="checkoutBtn" disabled>
                        <i class="fas fa-check-circle me-2"></i>Complete Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

document.querySelectorAll('.pos-item').forEach(item => {
    item.addEventListener('click', () => {
        const id = item.dataset.id;
        const name = item.dataset.name;
        const price = parseFloat(item.dataset.price);

        let existing = cart.find(i => i.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, name, price, qty: 1 });
        }
        updateUI();
    });
});

function updateUI() {
    const tbody = document.querySelector('#cartTable tbody');
    const empty = document.getElementById('emptyCart');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (cart.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('d-none');
        checkoutBtn.disabled = true;
        subtotalEl.textContent = totalEl.textContent = '₱0.00';
        return;
    }

    empty.classList.add('d-none');
    checkoutBtn.disabled = false;
    tbody.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const rowTotal = item.price * item.qty;
        total += rowTotal;
        tbody.innerHTML += `
            <tr>
                <td class="small fw-600">${item.name}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary py-0" onclick="updateQty(${index},-1)">-</button>
                        <span class="px-2 bg-light border-top border-bottom small">${item.qty}</span>
                        <button class="btn btn-outline-secondary py-0" onclick="updateQty(${index},1)">+</button>
                    </div>
                </td>
                <td class="text-end small fw-700">₱${rowTotal.toLocaleString()}</td>
                <td class="text-end">
                    <button class="btn btn-link text-danger p-0" onclick="removeItem(${index})"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
    });

    subtotalEl.textContent = totalEl.textContent = '₱' + total.toLocaleString();
    calculateChange();
}

function updateQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    updateUI();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    updateUI();
}

const payInput = document.getElementById('paymentInput');
payInput.addEventListener('input', calculateChange);

function calculateChange() {
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', '').replace(/,/g, ''));
    const payment = parseFloat(payInput.value) || 0;
    const changeRow = document.getElementById('changeRow');
    const changeEl = document.getElementById('changeAmount');

    if (payment >= total && total > 0) {
        changeRow.classList.remove('d-none');
        changeEl.textContent = '₱' + (payment - total).toLocaleString();
    } else {
        changeRow.classList.add('d-none');
    }
}

document.getElementById('posSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.pos-item').forEach(item => {
        const name = item.dataset.name.toLowerCase();
        item.style.display = name.includes(q) ? '' : 'none';
    });
});

document.getElementById('checkoutBtn').addEventListener('click', function() {
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', '').replace(/,/g, ''));
    const payment = parseFloat(payInput.value) || 0;

    if (payment < total) {
        alert("Insufficient payment amount!");
        return;
    }

    if (confirm("Confirm transaction?")) {
        // Here you would normally fetch() to process_sale.php
        alert("Transaction completed successfully!");
        cart = [];
        payInput.value = '';
        updateUI();
    }
});
</script>
</body></html>