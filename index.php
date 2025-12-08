<?php
session_start();
require "config/database.php";
require "config/config.php";
require "config/auth.php";
require "includes/functions.php";

checkLogin();

$current_user = getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : '';
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : '';
$stock = isset($_GET['stock']) && $_GET['stock'] !== '' ? $_GET['stock'] : '';
$reportPeriod = isset($_GET['period']) ? $_GET['period'] : 'daily';

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
$reportSalesData = [];
$reportSalesLabels = [];

if ($page === 'reports') {
    $whereClause = '';
    $labels = [];
    
    if ($reportPeriod === 'daily') {
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayLabel = date('M d', strtotime("-$i days"));
            $reportSalesLabels[] = $dayLabel;
            
            $daily_result = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as period_total FROM sales WHERE sale_date = '$date'");
            if ($daily_result) {
                $daily_row = mysqli_fetch_assoc($daily_result);
                $reportSalesData[] = floatval($daily_row['period_total']);
            } else {
                $reportSalesData[] = 0;
            }
        }
        
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
        $sales_result = mysqli_query($conn, "SELECT s.*, s.qty as quantity, p.name as product_name, u.username as sold_by_username FROM sales s LEFT JOIN products p ON s.product_id = p.id LEFT JOIN users u ON s.sold_by = u.id WHERE s.sale_date BETWEEN '$startDate' AND '$endDate' ORDER BY s.sale_date DESC, s.created_at DESC LIMIT 100");
        
    } elseif ($reportPeriod === 'weekly') {
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = date('Y-m-d', strtotime("-$i weeks monday"));
            $weekEnd = date('Y-m-d', strtotime("-$i weeks sunday"));
            $weekLabel = date('M d', strtotime($weekStart));
            $reportSalesLabels[] = $weekLabel;
            
            $weekly_result = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as period_total FROM sales WHERE sale_date BETWEEN '$weekStart' AND '$weekEnd'");
            if ($weekly_result) {
                $weekly_row = mysqli_fetch_assoc($weekly_result);
                $reportSalesData[] = floatval($weekly_row['period_total']);
            } else {
                $reportSalesData[] = 0;
            }
        }
        
        $startDate = date('Y-m-d', strtotime('-7 weeks monday'));
        $endDate = date('Y-m-d');
        $sales_result = mysqli_query($conn, "SELECT s.*, s.qty as quantity, p.name as product_name, u.username as sold_by_username FROM sales s LEFT JOIN products p ON s.product_id = p.id LEFT JOIN users u ON s.sold_by = u.id WHERE s.sale_date BETWEEN '$startDate' AND '$endDate' ORDER BY s.sale_date DESC, s.created_at DESC LIMIT 100");
        
    } elseif ($reportPeriod === 'monthly') {
        for ($i = 5; $i >= 0; $i--) {
            $monthLabel = date('M Y', strtotime("-$i months"));
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $reportSalesLabels[] = $monthLabel;
            
            $monthly_result = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as period_total FROM sales WHERE sale_date BETWEEN '$monthStart' AND '$monthEnd'");
            if ($monthly_result) {
                $monthly_row = mysqli_fetch_assoc($monthly_result);
                $reportSalesData[] = floatval($monthly_row['period_total']);
            } else {
                $reportSalesData[] = 0;
            }
        }
        
        $startDate = date('Y-m-01', strtotime('-5 months'));
        $endDate = date('Y-m-d');
        $sales_result = mysqli_query($conn, "SELECT s.*, s.qty as quantity, p.name as product_name, u.username as sold_by_username FROM sales s LEFT JOIN products p ON s.product_id = p.id LEFT JOIN users u ON s.sold_by = u.id WHERE s.sale_date BETWEEN '$startDate' AND '$endDate' ORDER BY s.sale_date DESC, s.created_at DESC LIMIT 100");
    }
    
    if ($sales_result) {
        $salesData = mysqli_fetch_all($sales_result, MYSQLI_ASSOC);
    }
} else {
    $sales_result = mysqli_query($conn, "SELECT s.*, s.qty as quantity, p.name as product_name, u.username as sold_by_username FROM sales s LEFT JOIN products p ON s.product_id = p.id LEFT JOIN users u ON s.sold_by = u.id ORDER BY s.created_at DESC LIMIT 100");
    if ($sales_result) {
        $salesData = mysqli_fetch_all($sales_result, MYSQLI_ASSOC);
    }
}


$totalSales = 0;
$sales_sum_result = mysqli_query($conn, "SELECT SUM(total) as total_sales FROM sales");
if ($sales_sum_result) {
    $sales_sum = mysqli_fetch_assoc($sales_sum_result);
    $totalSales = $sales_sum['total_sales'] ?? 0;
}

$dailySalesData = [];
$dailySalesLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayLabel = date('D', strtotime("-$i days"));
    $dailySalesLabels[] = $dayLabel;
    
    $daily_result = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as daily_total FROM sales WHERE sale_date = '$date'");
    if ($daily_result) {
        $daily_row = mysqli_fetch_assoc($daily_result);
        $dailySalesData[] = floatval($daily_row['daily_total']);
    } else {
        $dailySalesData[] = 0;
    }
}

$monthlySalesData = [];
$monthlySalesLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthLabel = date('M', strtotime("-$i months"));
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthlySalesLabels[] = $monthLabel;
    
    $monthly_result = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) as monthly_total FROM sales WHERE sale_date BETWEEN '$monthStart' AND '$monthEnd'");
    if ($monthly_result) {
        $monthly_row = mysqli_fetch_assoc($monthly_result);
        $monthlySalesData[] = floatval($monthly_row['monthly_total']);
    } else {
        $monthlySalesData[] = 0;
    }
}

$categorySalesData = [];
$categorySalesLabels = [];
$category_sales_result = mysqli_query($conn, "SELECT c.name, COALESCE(SUM(s.total), 0) as category_total FROM categories c LEFT JOIN sales s ON c.id = s.category_id GROUP BY c.id, c.name ORDER BY category_total DESC");
if ($category_sales_result) {
    while ($cat_row = mysqli_fetch_assoc($category_sales_result)) {
        if ($cat_row['category_total'] > 0) {
            $categorySalesLabels[] = $cat_row['name'];
            $categorySalesData[] = floatval($cat_row['category_total']);
        }
    }
}

$topSellingProducts = [];
$topSellingLabels = [];
$topSellingData = [];
$top_products_result = mysqli_query($conn, "SELECT p.name, COALESCE(SUM(s.qty), 0) as total_qty, COALESCE(SUM(s.total), 0) as total_sales FROM products p LEFT JOIN sales s ON p.id = s.product_id GROUP BY p.id, p.name ORDER BY total_sales DESC LIMIT 5");
if ($top_products_result) {
    while ($top_row = mysqli_fetch_assoc($top_products_result)) {
        if ($top_row['total_sales'] > 0) {
            $topSellingLabels[] = $top_row['name'];
            $topSellingData[] = floatval($top_row['total_sales']);
        }
    }
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
                <?php if (canViewCategories()): ?>
                <li class="nav-item">
                    <a href="?page=categories" class="nav-link <?php echo $page === 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (canViewUsers()): ?>
                <li class="nav-item">
                    <a href="?page=users" class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (canViewRoles()): ?>
                <li class="nav-item">
                    <a href="?page=roles" class="nav-link <?php echo $page === 'roles' ? 'active' : ''; ?>">
                        <i class="fas fa-user-tag"></i>
                        <span>Roles & Permissions</span>
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
                        'users' => 'User Management',
                        'roles' => 'Roles & Permissions'
                    ];
                    echo $titles[$page] ?? 'Dashboard';
                    ?>
                </div>
                <div class="header-actions">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo strtoupper(substr($current_user['full_name'], 0, 2)); ?></div>
                        <div>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($current_user['role_display'] ?? 'User'); ?></div>
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
                    <?php 
                    if (isset($_GET['checkout_success']) && isset($_GET['change'])) {
                        $change = floatval($_GET['change']);
                        $_SESSION['notification'] = [
                            'message' => 'Transaction successful! Change: ₱' . number_format($change, 2),
                            'type' => 'success'
                        ];
                        header('Location: ?page=pos');
                        exit;
                    }
                    include 'views/pos.php'; 
                    ?>
                <?php elseif ($page === 'reports'): ?>
                    <?php include 'views/reports.php'; ?>
                <?php elseif ($page === 'categories' && canViewCategories()): ?>
                    <?php include 'views/categories.php'; ?>
                <?php elseif ($page === 'users' && canViewUsers()): ?>
                    <?php include 'views/users.php'; ?>
                <?php elseif ($page === 'roles' && canViewRoles()): ?>
                    <?php include 'views/roles.php'; ?>
                <?php else: ?>
                    <?php include 'views/dashboard.php'; ?>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <div id="itemModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Item</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="post" action="<?php echo API_URL; ?>/actions.php">
                <div style="padding: 20px;">
                    <input type="hidden" name="action" value="save" id="formAction">
                    <input type="hidden" name="id" id="formId">
                    <div class="form-group">
                        <label for="formSku">SKU</label>
                        <input type="text" name="sku" id="formSku" placeholder="e.g., SKU001" required>
                    </div>
                    <div class="form-group">
                        <label for="formName">Product Name</label>
                        <input type="text" name="name" id="formName" placeholder="Enter product name" required>
                    </div>
                    <div class="form-group">
                        <label for="formBarcode">Barcode</label>
                        <input type="text" name="barcode" id="formBarcode" placeholder="Enter barcode" required>
                    </div>
                    <div class="form-group">
                        <label for="formCategory">Category</label>
                        <select name="category" id="formCategory" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
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
            <form method="post" action="<?php echo API_URL; ?>/actions.php">
                <div style="padding: 20px;">
                    <p style="color: #64748b; margin-bottom: 0;">Are you sure you want to delete this item? This action cannot be undone.</p>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
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
            document.getElementById('formSku').value = '';
            document.getElementById('formName').value = '';
            document.getElementById('formBarcode').value = '';
            document.getElementById('formCategory').value = '';
            document.getElementById('formStock').value = '';
            document.getElementById('formPrice').value = '';
            document.getElementById('formMinStock').value = '';
            document.getElementById('itemModal').style.display = 'flex';
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
            document.getElementById('itemModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('itemModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        <?php if ($page === 'dashboard'): ?>
        const dailySalesData = <?php echo json_encode($dailySalesData); ?>;
        const dailySalesLabels = <?php echo json_encode($dailySalesLabels); ?>;
        const monthlySalesData = <?php echo json_encode($monthlySalesData); ?>;
        const monthlySalesLabels = <?php echo json_encode($monthlySalesLabels); ?>;
        const categorySalesData = <?php echo json_encode($categorySalesData); ?>;
        const categorySalesLabels = <?php echo json_encode($categorySalesLabels); ?>;
        const topSellingLabels = <?php echo json_encode($topSellingLabels); ?>;
        const topSellingData = <?php echo json_encode($topSellingData); ?>;

        const dailySales = new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: dailySalesLabels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: dailySalesData,
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
                labels: categorySalesLabels,
                datasets: [{
                    data: categorySalesData,
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const topProducts = new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topSellingLabels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: topSellingData,
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
        });

        const monthlySales = new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: {
                labels: monthlySalesLabels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: monthlySalesData,
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
                labels: <?php echo json_encode($reportSalesLabels); ?>,
                datasets: [{
                    label: 'Sales (₱)',
                    data: <?php echo json_encode($reportSalesData); ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        const reportPie = new Chart(document.getElementById('reportPieChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categorySalesLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($categorySalesData); ?>,
                    backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316']
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += '₱' + context.parsed.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
