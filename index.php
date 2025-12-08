<?php
session_start();
require "config/database.php";
require "config/config.php";
require "config/auth.php";

checkLogin();

$current_user = getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : '';
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : '';
$stock = isset($_GET['stock']) && $_GET['stock'] !== '' ? $_GET['stock'] : '';

$categories = [];
$cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if ($cat_result) {
    $categories = mysqli_fetch_all($cat_result, MYSQLI_ASSOC);
}

$products = [];
$result = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$filtered_products = $products;
if ($search !== '' || $category !== '' || $stock !== '') {
    $filtered_products = array_filter($products, function($item) use ($search, $category, $stock) {
        $matchSearch = $search === '' || 
                      stripos($item['name'], $search) !== false || 
                      stripos($item['sku'], $search) !== false || 
                      stripos($item['barcode'], $search) !== false;
        $matchCategory = $category === '' || $item['category_id'] == $category;
        $matchStock = true;
        if ($stock === 'in-stock') {
            $matchStock = $item['stock'] > $item['minStock'];
        } elseif ($stock === 'low-stock') {
            $matchStock = $item['stock'] > 0 && $item['stock'] <= $item['minStock'];
        } elseif ($stock === 'out-stock') {
            $matchStock = $item['stock'] == 0;
        }
        return $matchSearch && $matchCategory && $matchStock;
    });
}

$totalInventory = array_sum(array_column($products, 'stock'));
$lowStockItems = count(array_filter($products, function($item) {
    return $item['stock'] > 0 && $item['stock'] <= $item['minStock'];
}));
$outOfStock = count(array_filter($products, function($item) {
    return $item['stock'] == 0;
}));

$categoryStats = [];
foreach ($products as $item) {
    $catName = $item['category_name'] ?? 'Uncategorized';
    if (!isset($categoryStats[$catName])) {
        $categoryStats[$catName] = 0;
    }
    $categoryStats[$catName] += $item['stock'];
}

$topProducts = $products;
usort($topProducts, function($a, $b) {
    return $b['stock'] - $a['stock'];
});
$topProducts = array_slice($topProducts, 0, 4);

$salesData = [];
$sales_result = mysqli_query($conn, "SELECT s.*, p.name as product_name FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC LIMIT 100");
if ($sales_result) {
    $salesData = mysqli_fetch_all($sales_result, MYSQLI_ASSOC);
}

$totalSales = 0;
$sales_sum_result = mysqli_query($conn, "SELECT SUM(total) as total_sales FROM sales");
if ($sales_sum_result) {
    $sales_sum = mysqli_fetch_assoc($sales_sum_result);
    $totalSales = $sales_sum['total_sales'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory & Sales Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-box"></i>
                <span>InvSys</span>
            </div>
            <ul class="nav-items">
                <li class="nav-item">
                    <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=inventory" class="nav-link <?php echo $page === 'inventory' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=scanner" class="nav-link <?php echo $page === 'scanner' ? 'active' : ''; ?>">
                        <i class="fas fa-barcode"></i>
                        <span>Barcode Scanner</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=pos" class="nav-link <?php echo $page === 'pos' ? 'active' : ''; ?>">
                        <i class="fas fa-cash-register"></i>
                        <span>POS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=reports" class="nav-link <?php echo $page === 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Sales Reports</span>
                    </a>
                </li>
                <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'manager'): ?>
                <li class="nav-item">
                    <a href="?page=categories" class="nav-link <?php echo $page === 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($current_user['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="?page=users" class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <div class="main">
            <div class="header">
                <div class="header-title">
                    <?php
                    $titles = [
                        'dashboard' => 'Dashboard',
                        'inventory' => 'Inventory Management',
                        'scanner' => 'Barcode Scanner',
                        'pos' => 'Point of Sale',
                        'reports' => 'Sales Reports',
                        'categories' => 'Category Management',
                        'users' => 'User Management'
                    ];
                    echo $titles[$page] ?? 'Dashboard';
                    ?>
                </div>
                <div class="header-actions">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo strtoupper(substr($current_user['full_name'], 0, 2)); ?></div>
                        <div>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?php echo ucfirst($current_user['role']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <?php if ($page === 'dashboard'): ?>
                    <?php include 'views/dashboard.php'; ?>
                <?php elseif ($page === 'inventory'): ?>
                    <?php include 'views/inventory.php'; ?>
                <?php elseif ($page === 'scanner'): ?>
                    <?php include 'views/scanner.php'; ?>
                <?php elseif ($page === 'pos'): ?>
                    <?php include 'views/pos.php'; ?>
                <?php elseif ($page === 'reports'): ?>
                    <?php include 'views/reports.php'; ?>
                <?php elseif ($page === 'categories' && in_array($current_user['role'], ['admin', 'manager'])): ?>
                    <?php include 'views/categories.php'; ?>
                <?php elseif ($page === 'users' && $current_user['role'] === 'admin'): ?>
                    <?php include 'views/users.php'; ?>
                <?php else: ?>
                    <?php include 'views/dashboard.php'; ?>
                <?php endif; ?>
            </div>php endif; ?>
            </div>
        </div>
    </div>

    <div id="itemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Item</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="post" action="<?php echo API_URL; ?>/actions.php">
                <input type="hidden" name="action" value="save" id="formAction">
                <input type="hidden" name="id" id="formId">
                <div class="form-group">
                    <label for="formSku">SKU</label>
                    <input type="text" name="sku" id="formSku" placeholder="e.g., SKU001" required>
                </div>
                <div class="form-group">
                <div class="form-group">
                    <label for="formCategory">Category</label>
                    <select name="category" id="formCategory" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>elect name="category" id="formCategory" required>
                        <option value="">Select Category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Home">Home & Garden</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="formStock">Stock Quantity</label>
                    <input type="number" name="stock" id="formStock" placeholder="0" min="0" required>
                </div>
                <div class="form-group">
                    <label for="formPrice">Unit Price</label>
                    <input type="number" name="price" id="formPrice" placeholder="0.00" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="formMinStock">Minimum Stock Level</label>
                    <input type="number" name="minStock" id="formMinStock" placeholder="10" min="0" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Delete Item</h2>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <p style="color: #64748b; margin-bottom: 24px;">Are you sure you want to delete this item? This action cannot be undone.</p>
            <form method="post" action="<?php echo API_URL; ?>/actions.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('formAction').value = 'save';
            document.getElementById('formId').value = '';
            document.getElementById('itemModal').classList.add('active');
        }

        function openEditModal(id, sku, name, barcode, category, stock, price, minStock) {
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('formAction').value = 'save';
            document.getElementById('formId').value = id;
            document.getElementById('formSku').value = sku;
            document.getElementById('formName').value = name;
            document.getElementById('formBarcode').value = barcode;
            document.getElementById('formCategory').value = category;
            document.getElementById('formStock').value = stock;
            document.getElementById('formPrice').value = price;
            document.getElementById('formMinStock').value = minStock;
            document.getElementById('itemModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.remove('active');
            document.getElementById('itemForm').reset();
        }

        function openDeleteModal(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        <?php if ($page === 'dashboard'): ?>
        const categoryData = <?php echo json_encode(array_values($categoryStats)); ?>;
        const categoryLabels = <?php echo json_encode(array_keys($categoryStats)); ?>;
        const topProductNames = <?php echo json_encode(array_column($topProducts, 'name')); ?>;
        const topProductStock = <?php echo json_encode(array_column($topProducts, 'stock')); ?>;

        const dailySales = new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales (₱)',
                    data: [250000, 290000, 270000, 340000, 400000, 495000, 365000],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const categorySales = new Chart(document.getElementById('categorySalesChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const topProducts = new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProductNames,
                datasets: [{
                    label: 'Stock Units',
                    data: topProductStock,
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
        });

        const monthlySales = new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales (₱)',
                    data: [1950000, 2280000, 2120000, 2510000, 2900000, 3010000],
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        <?php endif; ?>

        <?php if ($page === 'reports'): ?>
        const reportLine = new Chart(document.getElementById('reportLineChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [250000, 290000, 270000, 340000, 400000, 495000, 365000],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const reportPie = new Chart(document.getElementById('reportPieChart'), {
            type: 'pie',
            data: {
                labels: ['Electronics', 'Clothing', 'Home & Garden', 'Sports'],
                datasets: [{
                    data: [1560000, 670000, 450000, 365000],
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        <?php endif; ?>
    </script>
</body>
</html>
