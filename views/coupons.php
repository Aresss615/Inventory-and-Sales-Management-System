<?php
$coupons_result = mysqli_query($conn, "SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = [];
if ($coupons_result) {
    $coupons = mysqli_fetch_all($coupons_result, MYSQLI_ASSOC);
}
?>

<div class="controls">
    <button type="button" class="btn btn-primary" onclick="openCouponModal()">
        <i class="fas fa-plus"></i> Add New Coupon
    </button>
    <input type="text" id="couponSearch" class="input-field" placeholder="Search coupons..." oninput="filterCoupons()">
</div>

<div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Purchase</th>
                    <th>Max Discount</th>
                    <th>Valid From</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="couponsTableBody">
                <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td><span class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></span></td>
                    <td>
                        <span class="badge badge-<?php echo $coupon['discount_type'] === 'percentage' ? 'info' : 'warning'; ?>">
                            <?php echo ucfirst($coupon['discount_type']); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        if ($coupon['discount_type'] === 'percentage') {
                            echo $coupon['discount_value'] . '%';
                        } else {
                            echo '₱' . number_format($coupon['discount_value'], 2);
                        }
                        ?>
                    </td>
                    <td>₱<?php echo number_format($coupon['min_purchase'], 2); ?></td>
                    <td>
                        <?php 
                        echo $coupon['max_discount'] ? '₱' . number_format($coupon['max_discount'], 2) : 'No Limit';
                        ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($coupon['valid_from'])); ?></td>
                    <td>
                        <?php 
                        if ($coupon['valid_until']) {
                            $expiry = strtotime($coupon['valid_until']);
                            $now = time();
                            echo date('M d, Y', $expiry);
                            if ($expiry < $now) {
                                echo ' <span class="badge badge-danger">Expired</span>';
                            }
                        } else {
                            echo 'No Expiry';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $coupon['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick='editCoupon(<?php echo json_encode($coupon); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-delete" onclick="openDeleteCouponModal(<?php echo $coupon['id']; ?>, '<?php echo htmlspecialchars($coupon['code']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($coupons)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No coupons found. <a href="#" onclick="openCouponModal(); return false;" style="color: var(--primary);">Create one now</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="couponModal" class="modal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h2 class="modal-title" id="couponModalTitle">Add New Coupon</h2>
            <button class="modal-close" onclick="closeCouponModal()">&times;</button>
        </div>
        <form id="couponForm" onsubmit="saveCoupon(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <div style="padding: 20px; overflow-y: auto; flex: 1;">
                <input type="hidden" id="couponId" name="id">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="couponCode">Coupon Code</label>
                    <input type="text" id="couponCode" name="code" placeholder="e.g., SAVE10, WELCOME20" required style="text-transform: uppercase;">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="discountType">Discount Type</label>
                    <select id="discountType" name="discount_type" required onchange="updateDiscountLabel()">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (₱)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="discountValue"><span id="discountLabel">Discount Percentage</span></label>
                    <input type="number" id="discountValue" name="discount_value" placeholder="0" step="0.01" min="0" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="minPurchase">Minimum Purchase (₱)</label>
                    <input type="number" id="minPurchase" name="min_purchase" placeholder="0.00" step="0.01" min="0" value="0">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="maxDiscount">Max Discount (₱)</label>
                    <input type="number" id="maxDiscount" name="max_discount" placeholder="Leave empty for no limit" step="0.01" min="0">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="validFrom">Valid From</label>
                    <input type="datetime-local" id="validFrom" name="valid_from" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="validUntil">Valid Until</label>
                    <input type="datetime-local" id="validUntil" name="valid_until">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="checkbox-label">
                        <input type="checkbox" id="isActive" name="is_active" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeCouponModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCouponModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Delete Coupon</h2>
            <button class="modal-close" onclick="closeDeleteCouponModal()">&times;</button>
        </div>
        <div style="padding: 20px;">
            <p style="color: #64748b; margin-bottom: 0;">Are you sure you want to delete the coupon "<strong id="deleteCouponCode"></strong>"? This action cannot be undone.</p>
            <input type="hidden" id="deleteCouponId">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteCouponModal()">Cancel</button>
            <button type="button" class="btn btn-delete" onclick="confirmDeleteCoupon()">Delete</button>
        </div>
    </div>
</div>

<style>
.coupon-code {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #3b82f6;
    background: #eff6ff;
    padding: 4px 8px;
    border-radius: 4px;
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-danger { background: #fee2e2; color: #dc2626; }
.badge-warning { background: #fef3c7; color: #d97706; }
.badge-info { background: #dbeafe; color: #2563eb; }
.badge-secondary { background: #f1f5f9; color: #64748b; }
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
.checkbox-label input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}
</style>

<script>
function openCouponModal() {
    document.getElementById('couponModalTitle').textContent = 'Add New Coupon';
    document.getElementById('couponForm').reset();
    document.getElementById('couponId').value = '';
    document.getElementById('isActive').checked = true;
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('validFrom').value = now.toISOString().slice(0, 16);
    document.getElementById('couponModal').classList.add('active');
}

function closeCouponModal() {
    document.getElementById('couponModal').classList.remove('active');
}

function updateDiscountLabel() {
    const type = document.getElementById('discountType').value;
    const label = document.getElementById('discountLabel');
    label.textContent = type === 'percentage' ? 'Discount Percentage' : 'Discount Amount (₱)';
}

function editCoupon(coupon) {
    document.getElementById('couponModalTitle').textContent = 'Edit Coupon';
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('couponCode').value = coupon.code;
    document.getElementById('discountType').value = coupon.discount_type;
    document.getElementById('discountValue').value = coupon.discount_value;
    document.getElementById('minPurchase').value = coupon.min_purchase;
    document.getElementById('maxDiscount').value = coupon.max_discount || '';
    
    const validFrom = new Date(coupon.valid_from);
    validFrom.setMinutes(validFrom.getMinutes() - validFrom.getTimezoneOffset());
    document.getElementById('validFrom').value = validFrom.toISOString().slice(0, 16);
    
    if (coupon.valid_until) {
        const validUntil = new Date(coupon.valid_until);
        validUntil.setMinutes(validUntil.getMinutes() - validUntil.getTimezoneOffset());
        document.getElementById('validUntil').value = validUntil.toISOString().slice(0, 16);
    } else {
        document.getElementById('validUntil').value = '';
    }
    
    document.getElementById('isActive').checked = coupon.is_active == 1;
    updateDiscountLabel();
    document.getElementById('couponModal').classList.add('active');
}

async function saveCoupon(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = formData.get('id');
    const action = id ? 'update' : 'create';
    formData.append('action', action);
    
    try {
        const response = await fetch('api/coupons.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeCouponModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(result.message || 'Failed to save coupon', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function openDeleteCouponModal(id, code) {
    document.getElementById('deleteCouponId').value = id;
    document.getElementById('deleteCouponCode').textContent = code;
    document.getElementById('deleteCouponModal').classList.add('active');
}

function closeDeleteCouponModal() {
    document.getElementById('deleteCouponModal').classList.remove('active');
}

async function confirmDeleteCoupon() {
    const id = document.getElementById('deleteCouponId').value;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api/coupons.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeDeleteCouponModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(result.message || 'Failed to delete coupon', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function filterCoupons() {
    const search = document.getElementById('couponSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#couponsTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>
