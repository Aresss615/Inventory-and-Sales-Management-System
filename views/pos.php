<?php displayNotification(); ?>
<?php
$outOfStockBarcodes = [];
foreach ($products as $p) {
    if (isset($p['stock']) && $p['stock'] <= 0) {
        $outOfStockBarcodes[] = $p['barcode'];
    }
}
?>
<div class="pos-container">
    <div class="pos-products">
        <div class="pos-search">
            <input type="text" id="posSearch" class="input-field" placeholder="Search products or scan barcode...">
        </div>
        <div class="pos-grid" id="posGrid">
            <?php foreach ($products as $product): ?>
                <?php if ($product['stock'] > 0): ?>
                    <div class="pos-card" 
                         data-barcode="<?php echo htmlspecialchars($product['barcode']); ?>"
                         onclick="addToCart(<?php echo $product['id']; ?>, <?php echo htmlspecialchars(json_encode($product['name'])); ?>, <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">
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

<div class="modal" id="clearCartModal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3 class="modal-title">Clear Cart</h3>
            <button class="modal-close" onclick="closeClearCartModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to clear all items from the cart?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeClearCartModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmClearCart()">Clear Cart</button>
        </div>
    </div>
</div>

<div class="modal" id="checkoutModal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3 class="modal-title">Checkout</h3>
            <button class="modal-close" onclick="closeCheckoutModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Subtotal</label>
                <div style="font-size: 20px; font-weight: 600; margin: 5px 0;">
                    <span id="checkoutSubtotal">₱0.00</span>
                </div>
            </div>
            
            <div class="form-group">
                <label>Discount Type</label>
                <select id="discountType" class="input-field" onchange="calculateCheckout()">
                    <option value="">No Discount</option>
                    <option value="PWD">PWD (10%)</option>
                    <option value="SENIOR">Senior Citizen (10%)</option>
                </select>
                <small id="discountAmount" style="margin-top: 5px; color: var(--success); font-weight: 600;"></small>
            </div>
            
            <div class="form-group">
                <label>Coupon Code</label>
                <input type="text" id="couponCode" class="input-field" placeholder="Enter coupon code" oninput="calculateCheckout()">
                <small id="couponDiscount" style="margin-top: 5px; color: var(--success); font-weight: 600;"></small>
            </div>
            
            <div class="form-group">
                <label>Payment Method</label>
                <select id="paymentMethod" class="input-field" onchange="calculateCheckout()">
                    <option value="cash">Cash</option>
                    <option value="card">Card (+1% fee)</option>
                    <option value="e-wallet">E-Wallet</option>
                </select>
                <small id="cardFee" style="margin-top: 5px; color: var(--danger); font-weight: 600;"></small>
            </div>
            
            <div class="form-group">
                <label>Total Amount</label>
                <div style="font-size: 24px; font-weight: 700; color: var(--primary); margin: 10px 0;">
                    <span id="checkoutTotal">₱0.00</span>
                </div>
            </div>
            <div class="form-group">
                <label>Payment Amount</label>
                <input type="number" id="paymentAmount" class="input-field" placeholder="Enter payment amount" step="0.01" min="0" oninput="calculateChange()">
                <small id="changeAmount" style="margin-top: 5px; color: var(--success); font-weight: 600;"></small>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeCheckoutModal()">Cancel</button>
            <button class="btn btn-primary" onclick="confirmCheckout()" id="confirmCheckoutBtn">Confirm Checkout</button>
        </div>
    </div>
</div>

<script>
let cart = [];
const outOfStockBarcodes = <?php echo json_encode($outOfStockBarcodes); ?> || [];

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'pos-toast';
    toast.textContent = message;
    Object.assign(toast.style, {
        position: 'fixed',
        right: '20px',
        top: '20px',
        background: 'rgba(0,0,0,0.8)',
        color: '#fff',
        padding: '10px 14px',
        borderRadius: '6px',
        zIndex: 9999,
        boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
        fontSize: '14px'
    });
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s ease';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

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
    if (cart.length > 0) {
        document.getElementById('clearCartModal').classList.add('active');
    }
}

function closeClearCartModal() {
    document.getElementById('clearCartModal').classList.remove('active');
}

function confirmClearCart() {
    cart = [];
    renderCart();
    closeClearCartModal();
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
        return;
    }
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('checkoutSubtotal').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('checkoutTotal').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('discountType').value = '';
    document.getElementById('couponCode').value = '';
    document.getElementById('paymentMethod').value = 'cash';
    document.getElementById('paymentAmount').value = '';
    document.getElementById('discountAmount').textContent = '';
    document.getElementById('couponDiscount').textContent = '';
    document.getElementById('cardFee').textContent = '';
    document.getElementById('changeAmount').textContent = '';
    document.getElementById('checkoutModal').classList.add('active');
    document.getElementById('paymentAmount').focus();
}

function calculateCheckout() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    let total = subtotal;
    
    const discountType = document.getElementById('discountType').value;
    let discountAmount = 0;
    if (discountType === 'PWD' || discountType === 'SENIOR') {
        discountAmount = subtotal * 0.10;
        total -= discountAmount;
        document.getElementById('discountAmount').textContent = `Discount: -₱${discountAmount.toFixed(2)}`;
    } else {
        document.getElementById('discountAmount').textContent = '';
    }
    
    const couponCode = document.getElementById('couponCode').value.trim();
    let couponDiscount = 0;
    if (couponCode) {
        const coupons = {
            'WELCOME10': { type: 'percentage', value: 10, min: 500, max: 500 },
            'SAVE50': { type: 'fixed', value: 50, min: 200 },
            'SAVE100': { type: 'fixed', value: 100, min: 500 },
            'MEGA20': { type: 'percentage', value: 20, min: 1000, max: 1000 },
            'NEWYEAR15': { type: 'percentage', value: 15, min: 300, max: 750 }
        };
        
        const coupon = coupons[couponCode.toUpperCase()];
        if (coupon && subtotal >= coupon.min) {
            if (coupon.type === 'percentage') {
                couponDiscount = total * (coupon.value / 100);
                if (coupon.max && couponDiscount > coupon.max) {
                    couponDiscount = coupon.max;
                }
            } else {
                couponDiscount = coupon.value;
            }
            total -= couponDiscount;
            document.getElementById('couponDiscount').textContent = `Coupon: -₱${couponDiscount.toFixed(2)}`;
        } else if (coupon && subtotal < coupon.min) {
            document.getElementById('couponDiscount').textContent = `Min. purchase: ₱${coupon.min.toFixed(2)}`;
            document.getElementById('couponDiscount').style.color = 'var(--danger)';
        } else {
            document.getElementById('couponDiscount').textContent = 'Invalid coupon code';
            document.getElementById('couponDiscount').style.color = 'var(--danger)';
        }
    } else {
        document.getElementById('couponDiscount').textContent = '';
    }
    
    const paymentMethod = document.getElementById('paymentMethod').value;
    let cardFee = 0;
    if (paymentMethod === 'card') {
        cardFee = total * 0.01;
        total += cardFee;
        document.getElementById('cardFee').textContent = `Card Fee: +₱${cardFee.toFixed(2)}`;
    } else {
        document.getElementById('cardFee').textContent = '';
    }
    
    document.getElementById('checkoutTotal').textContent = '₱' + total.toFixed(2);
    calculateChange();
}

function calculateChange() {
    const total = parseFloat(document.getElementById('checkoutTotal').textContent.replace('₱', '').replace(',', ''));
    const payment = parseFloat(document.getElementById('paymentAmount').value);
    const changeDisplay = document.getElementById('changeAmount');
    
    if (payment && payment > 0) {
        const change = payment - total;
        if (change >= 0) {
            changeDisplay.textContent = `Change: ₱${change.toFixed(2)}`;
            changeDisplay.style.color = 'var(--success)';
        } else {
            changeDisplay.textContent = `Insufficient: ₱${Math.abs(change).toFixed(2)} short`;
            changeDisplay.style.color = 'var(--danger)';
        }
    } else {
        changeDisplay.textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const paymentInput = document.getElementById('paymentAmount');
    if (paymentInput) {
        paymentInput.addEventListener('input', calculateChange);
    }
});

function closeCheckoutModal() {
    document.getElementById('checkoutModal').classList.remove('active');
}

function confirmCheckout() {
    const total = parseFloat(document.getElementById('checkoutTotal').textContent.replace('₱', '').replace(',', ''));
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value);
    
    if (!paymentAmount || paymentAmount <= 0) {
        alert('Please enter a valid payment amount');
        return;
    }
    
    if (paymentAmount < total) {
        alert('Payment amount is less than total');
        return;
    }
    
    const discountType = document.getElementById('discountType').value || null;
    const couponCode = document.getElementById('couponCode').value.trim() || null;
    const paymentMethod = document.getElementById('paymentMethod').value;
    
    const checkoutBtn = document.getElementById('confirmCheckoutBtn');
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
        body: JSON.stringify({ 
            items: items,
            discount_type: discountType,
            coupon_code: couponCode,
            payment_method: paymentMethod,
            payment_received: paymentAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            cart = [];
            renderCart();
            closeCheckoutModal();
            window.location.href = '?page=pos&checkout_success=1&change=' + data.change_amount.toFixed(2);
        } else {
            alert(data.message || 'Checkout failed');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Confirm Checkout';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing checkout');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Confirm Checkout';
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
    
    let foundByBarcode = false;
    
    cards.forEach(card => {
        const name = card.querySelector('.pos-card-name').textContent.toLowerCase();
        const sku = card.querySelector('.pos-card-sku').textContent.toLowerCase();
        const barcode = card.dataset.barcode.toLowerCase();
        
        if (name.includes(search) || sku.includes(search) || barcode.includes(search)) {
            card.style.display = 'block';
            
            if (barcode === search && search.length > 5) {
                foundByBarcode = true;
                setTimeout(() => {
                    card.click();
                    document.getElementById('posSearch').value = '';
                    document.querySelectorAll('.pos-card').forEach(c => c.style.display = 'block');
                }, 100);
            }
        } else {
            card.style.display = 'none';
        }
    });

    if (!foundByBarcode && search.length > 5) {
        const normalized = search.toLowerCase();
        const outLower = outOfStockBarcodes.map(b => String(b).toLowerCase());
        if (outLower.includes(normalized)) {
            showToast('Product is out of stock');
            e.target.value = '';
            document.querySelectorAll('.pos-card').forEach(c => c.style.display = 'block');
        }
    }
});
</script>
