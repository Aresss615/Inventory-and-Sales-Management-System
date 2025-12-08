<div class="pos-container">
    <div class="pos-products">
        <div class="pos-search">
            <input type="text" id="posSearch" class="input-field" placeholder="Search products or scan barcode...">
        </div>
        <div class="pos-grid" id="posGrid">
            <?php foreach ($products as $product): ?>
                <?php if ($product['stock'] > 0): ?>
                    <div class="pos-card" onclick="addToCart(<?php echo $product['id']; ?>, <?php echo htmlspecialchars(json_encode($product['name'])); ?>, <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">
                        <div class="pos-card-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="pos-card-sku"><?php echo htmlspecialchars($product['sku']); ?></div>
                        <div class="pos-card-stock">Stock: <?php echo $product['stock']; ?></div>
                        <div class="pos-card-price">₱<?php echo number_format($product['price'], 2); ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="pos-cart">
        <div class="pos-cart-header">
            <h3>Cart</h3>
            <button class="btn btn-secondary btn-sm" onclick="clearCart()">Clear</button>
        </div>
        <div class="pos-cart-items" id="cartItems">
            <div class="pos-empty">Cart is empty</div>
        </div>
        <div class="pos-cart-footer">
            <div class="pos-total">
                <span>Total:</span>
                <span id="cartTotal">₱0.00</span>
            </div>
            <button class="btn btn-primary btn-block" onclick="checkout()" id="checkoutBtn" disabled>Checkout</button>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, name, price, maxStock) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < maxStock) {
            existing.qty++;
        } else {
            alert('Maximum stock reached');
            return;
        }
    } else {
        cart.push({ id, name, price, qty: 1, maxStock });
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

function updateQty(id, qty) {
    const item = cart.find(item => item.id === id);
    if (item) {
        if (qty > 0 && qty <= item.maxStock) {
            item.qty = qty;
        } else if (qty > item.maxStock) {
            item.qty = item.maxStock;
            alert('Maximum stock reached');
        } else {
            removeFromCart(id);
        }
        renderCart();
    }
}

function clearCart() {
    if (cart.length > 0 && confirm('Clear all items from cart?')) {
        cart = [];
        renderCart();
    }
}

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="pos-empty">Cart is empty</div>';
        cartTotal.textContent = '₱0.00';
        checkoutBtn.disabled = true;
        return;
    }
    
    let total = 0;
    let html = '';
    
    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        html += `
            <div class="cart-item">
                <div class="cart-item-details">
                    <div class="cart-item-name">${escapeHtml(item.name)}</div>
                    <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                </div>
                <div class="cart-item-actions">
                    <input type="number" value="${item.qty}" min="1" max="${item.maxStock}" 
                           class="cart-qty-input" onchange="updateQty(${item.id}, parseInt(this.value))">
                    <button class="btn-icon" onclick="removeFromCart(${item.id})" type="button">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="cart-item-subtotal">₱${subtotal.toFixed(2)}</div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    cartTotal.textContent = '₱' + total.toFixed(2);
    checkoutBtn.disabled = false;
}

function checkout() {
    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const confirmation = prompt(`Total: ₱${total.toFixed(2)}\n\nType "YES" to confirm checkout:`);
    
    if (confirmation === null || confirmation.toUpperCase() !== 'YES') {
        return;
    }
    
    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.textContent = 'Processing...';
    
    const items = cart.map(item => ({
        product_id: item.id,
        qty: item.qty,
        price: item.price
    }));
    
    fetch('<?php echo BASE_URL; ?>/api/checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            alert(data.message);
            cart = [];
            renderCart();
            window.location.href = '?page=pos';
        } else {
            alert(data.message || 'Checkout failed');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Checkout';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing checkout');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Checkout';
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('posSearch').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.pos-card');
    
    cards.forEach(card => {
        const name = card.querySelector('.pos-card-name').textContent.toLowerCase();
        const sku = card.querySelector('.pos-card-sku').textContent.toLowerCase();
        
        if (name.includes(search) || sku.includes(search)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
