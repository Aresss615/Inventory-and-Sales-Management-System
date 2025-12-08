<div class="metrics-grid">
    <div class="metric-card <?php echo $totalSales > 0 ? 'success' : 'danger'; ?>">
        <div class="metric-label">Total Sales</div>
        <div class="metric-value">₱<?php echo number_format($totalSales, 2); ?></div>
        <div class="metric-change">
            <i class="fas fa-<?php echo $totalSales > 0 ? 'arrow-up' : 'arrow-down'; ?>"></i> 
            <?php echo $totalSales > 0 ? 'Revenue generated' : 'No sales yet'; ?>
        </div>
    </div>
    <div class="metric-card <?php 
        if ($totalInventory == 0) echo 'danger';
        elseif ($totalInventory < 100) echo 'warning';
        else echo 'success';
    ?>">
        <div class="metric-label">Total Inventory</div>
        <div class="metric-value"><?php echo $totalInventory; ?></div>
        <div class="metric-change">
            <i class="fas fa-boxes"></i> 
            <?php 
                if ($totalInventory == 0) echo 'No inventory';
                elseif ($totalInventory < 100) echo 'Low inventory';
                else echo 'Good stock level';
            ?>
        </div>
    </div>
    <div class="metric-card <?php 
        if ($lowStockItems == 0) echo 'success';
        elseif ($lowStockItems < 5) echo 'warning';
        else echo 'danger';
    ?>">
        <div class="metric-label">Low Stock Items</div>
        <div class="metric-value"><?php echo $lowStockItems; ?></div>
        <div class="metric-change">
            <i class="fas fa-<?php echo $lowStockItems == 0 ? 'check-circle' : 'exclamation-triangle'; ?>"></i> 
            <?php 
                if ($lowStockItems == 0) echo 'All items stocked';
                elseif ($lowStockItems < 5) echo 'Some items low';
                else echo 'Needs attention';
            ?>
        </div>
    </div>
    <div class="metric-card <?php echo $outOfStock == 0 ? 'success' : 'danger'; ?>">
        <div class="metric-label">Out of Stock</div>
        <div class="metric-value"><?php echo $outOfStock; ?></div>
        <div class="metric-change">
            <i class="fas fa-<?php echo $outOfStock == 0 ? 'check-circle' : 'ban'; ?>"></i> 
            <?php echo $outOfStock == 0 ? 'All items in stock' : 'Requires reorder'; ?>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-title">Daily Sales (This Week)</div>
        <div class="chart-container">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Sales by Category</div>
        <div class="chart-container">
            <canvas id="categorySalesChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Top Products</div>
        <div class="chart-container">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Monthly Sales Trend</div>
        <div class="chart-container">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>
</div>
