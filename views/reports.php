<div class="controls">
    <select class="select">
        <option value="daily">Daily Report</option>
        <option value="weekly">Weekly Report</option>
        <option value="monthly">Monthly Report</option>
    </select>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-title">Sales by Period</div>
        <div class="chart-container">
            <canvas id="reportLineChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-title">Sales Distribution</div>
        <div class="chart-container">
            <canvas id="reportPieChart"></canvas>
        </div>
    </div>
</div>

<div class="chart-card">
    <div class="chart-title">Detailed Sales Report</div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesData as $sale): ?>
                    <tr>
                        <td><?php echo isset($sale['sale_date']) ? date('Y-m-d', strtotime($sale['sale_date'])) : 'N/A'; ?></td>
                        <td><?php echo $sale['product_name'] ?? 'N/A'; ?></td>
                        <td><?php echo $sale['quantity'] ?? 0; ?></td>
                        <td>₱<?php echo number_format($sale['price'] ?? 0, 2); ?></td>
                        <td><strong>₱<?php echo number_format($sale['total'] ?? 0, 2); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
