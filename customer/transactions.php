<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="main clean-container">

    <h1 class="section-title">My Transactions</h1>

    <div id="transactionsContainer" class="transactions-grid">
        <!-- Loaded via AJAX -->
    </div>

</div>

<script>
function loadTransactions(page = 1) {
    fetch('/rholance_pms/modules/transactions/fetch_transactions.php?page=' + page)
        .then(res => res.text())
        .then(data => {
            document.getElementById('transactionsContainer').innerHTML = data;
        });
}

loadTransactions();
</script>