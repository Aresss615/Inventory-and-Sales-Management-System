<?php
$scannedProduct = null;
$scanError = false;

if (isset($_GET['barcode']) && !empty($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    foreach ($products as $item) {
        if ($item['barcode'] === $barcode) {
            $scannedProduct = $item;
            break;
        }
    }
    if (!$scannedProduct) {
        $scanError = true;
    }
}
?>

<div class="chart-card" style="max-width: 600px;">
    <div class="chart-title">Barcode Scanner</div>
    <form method="get">
        <input type="hidden" name="page" value="scanner">
        <input type="text" name="barcode" class="barcode-input" placeholder="Scan or enter barcode here..." autofocus>
    </form>
    <p style="font-size: 13px; color: #64748b; text-align: center;">Place cursor here and scan barcode or type manually</p>
</div>

<?php if ($scannedProduct): ?>
    <div class="alert active alert-success">
        <i class="fas fa-check-circle"></i> Item found!
    </div>
    <div class="scanner-result active">
        <div class="item-detail">
            <div class="detail-item">
                <div class="detail-label">Product Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($scannedProduct['name']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">SKU</div>
                <div class="detail-value"><?php echo htmlspecialchars($scannedProduct['sku']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Barcode</div>
                <div class="detail-value"><?php echo htmlspecialchars($scannedProduct['barcode']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Category</div>
                <div class="detail-value"><?php echo htmlspecialchars($scannedProduct['category'] ?? $scannedProduct['category_name'] ?? 'Uncategorized'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Stock Quantity</div>
                <div class="detail-value"><?php echo $scannedProduct['stock']; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Unit Price</div>
                <div class="detail-value">₱<?php echo number_format($scannedProduct['price'], 2); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <?php
                    if ($scannedProduct['stock'] == 0) {
                        echo 'Out of Stock';
                    } elseif ($scannedProduct['stock'] <= $scannedProduct['minStock']) {
                        echo 'Low Stock';
                    } else {
                        echo 'In Stock';
                    }
                    ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Min. Stock Level</div>
                <div class="detail-value"><?php echo $scannedProduct['minStock']; ?></div>
            </div>
        </div>
    </div>
<?php elseif ($scanError): ?>
    <div class="alert active alert-danger">
        <i class="fas fa-exclamation-circle"></i> Barcode not found in inventory
    </div>
<?php endif; ?>
