<?php if (function_exists('displayNotification')) displayNotification(); ?>

<div class="controls">
    <button type="button" class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add New Item
    </button>
    <input type="text" id="searchFilter" class="input-field" placeholder="Search by name or SKU..." oninput="applyFilters()">
    <select id="categoryFilter" class="select" onchange="applyFilters()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="stockFilter" class="select" onchange="applyFilters()">
        <option value="">All Items</option>
        <option value="in-stock">In Stock</option>
        <option value="low-stock">Low Stock</option>
        <option value="out-stock">Out of Stock</option>
    </select>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Unit Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filtered_products)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No items in inventory. <a href="#" onclick="openAddModal(); return false;" style="color: var(--primary);">Add one now</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($filtered_products as $item): ?>
                    <?php
                    if ($item['stock'] == 0) {
                        $status = 'Out of Stock';
                        $badge = 'badge-danger';
                    } elseif ($item['stock'] <= $item['minStock']) {
                        $status = 'Low Stock';
                        $badge = 'badge-warning';
                    } else {
                        $status = 'In Stock';
                        $badge = 'badge-success';
                    }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['sku']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo htmlspecialchars($item['barcode']); ?></code></td>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                        <td><strong><?php echo $item['stock']; ?></strong></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-edit" onclick="openEditModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['sku']); ?>', '<?php echo htmlspecialchars($item['name']); ?>', '<?php echo htmlspecialchars($item['barcode']); ?>', <?php echo $item['category_id']; ?>, <?php echo $item['stock']; ?>, <?php echo $item['price']; ?>, <?php echo $item['minStock']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'manager'): ?>
                                <button class="btn btn-sm btn-delete" onclick="openDeleteModal(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const allProducts = <?php echo json_encode($products); ?>;

function applyFilters() {
    const searchValue = document.getElementById('searchFilter').value.toLowerCase();
    const categoryValue = document.getElementById('categoryFilter').value;
    const stockValue = document.getElementById('stockFilter').value;

    const filtered = allProducts.filter(item => {
        const matchSearch = searchValue === '' || 
                          (item.name || '').toLowerCase().includes(searchValue) || 
                          (item.sku || '').toLowerCase().includes(searchValue) || 
                          (item.barcode || '').toLowerCase().includes(searchValue);

        const matchCategory = categoryValue === '' || item.category_id == categoryValue;

        const stock = Number(item.stock) || 0;
        const minStock = Number(item.minStock) || 0;

        let matchStock = true;
        if (stockValue === 'in-stock') {
            matchStock = stock > minStock;
        } else if (stockValue === 'low-stock') {
            matchStock = stock > 0 && stock <= minStock;
        } else if (stockValue === 'out-stock') {
            matchStock = stock == 0;
        }

        return matchSearch && matchCategory && matchStock;
    });

    renderTable(filtered);
}

function renderTable(products) {
    const tbody = document.querySelector('.table-container tbody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">No items in inventory. <a href="#" onclick="openAddModal(); return false;" style="color: var(--primary);">Add one now</a></td></tr>';
        return;
    }
    
    let html = '';
    products.forEach(item => {
        const stock = Number(item.stock) || 0;
        const minStock = Number(item.minStock) || 0;
        let status, badge;
        if (stock == 0) {
            status = 'Out of Stock';
            badge = 'badge-danger';
        } else if (stock <= minStock) {
            status = 'Low Stock';
            badge = 'badge-warning';
        } else {
            status = 'In Stock';
            badge = 'badge-success';
        }

        html += `
            <tr>
                <td><strong>${escapeHtml(item.sku)}</strong></td>
                <td>${escapeHtml(item.name)}</td>
                <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 12px;">${escapeHtml(item.barcode)}</code></td>
                <td>${escapeHtml(item.category_name || 'N/A')}</td>
                <td><strong>${stock}</strong></td>
                <td>₱${parseFloat(item.price).toFixed(2)}</td>
                <td><span class="badge ${badge}">${status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-edit" onclick="openEditModal(${item.id}, '${escapeHtml(item.sku)}', '${escapeHtml(item.name)}', '${escapeHtml(item.barcode)}', ${item.category_id}, ${stock}, ${item.price}, ${minStock})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'manager'): ?>
                        <button class="btn btn-sm btn-delete" onclick="openDeleteModal(${item.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
