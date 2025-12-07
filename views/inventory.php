<form method="get" class="controls">
    <input type="hidden" name="page" value="inventory">
    <button type="button" class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add New Item
    </button>
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="input-field" placeholder="Search by name or SKU...">
    <select name="category" class="select">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="stock" class="select">
        <option value="">All Items</option>
        <option value="in-stock" <?php echo $stock === 'in-stock' ? 'selected' : ''; ?>>In Stock</option>
        <option value="low-stock" <?php echo $stock === 'low-stock' ? 'selected' : ''; ?>>Low Stock</option>
        <option value="out-stock" <?php echo $stock === 'out-stock' ? 'selected' : ''; ?>>Out of Stock</option>
    </select>
    <button type="submit" class="btn btn-secondary">
        <i class="fas fa-filter"></i> Filter
    </button>
</form>

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
