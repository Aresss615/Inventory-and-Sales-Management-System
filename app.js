let inventoryData = JSON.parse(localStorage.getItem('inventoryData')) || [
    { sku: 'SKU001', name: 'Wireless Headphones', barcode: '1234567890123', category: 'Electronics', stock: 45, price: 129.99, minStock: 10 },
    { sku: 'SKU002', name: 'USB-C Cable', barcode: '1234567890124', category: 'Electronics', stock: 3, price: 19.99, minStock: 20 },
    { sku: 'SKU003', name: 'T-Shirt Blue', barcode: '1234567890125', category: 'Clothing', stock: 120, price: 24.99, minStock: 30 },
    { sku: 'SKU004', name: 'Running Shoes', barcode: '1234567890126', category: 'Sports', stock: 32, price: 89.99, minStock: 15 },
    { sku: 'SKU005', name: 'Office Chair', barcode: '1234567890127', category: 'Home', stock: 0, price: 199.99, minStock: 5 },
    { sku: 'SKU006', name: 'Desk Lamp', barcode: '1234567890128', category: 'Home', stock: 18, price: 45.99, minStock: 10 },
    { sku: 'SKU007', name: 'Mouse Pad', barcode: '1234567890129', category: 'Electronics', stock: 156, price: 12.99, minStock: 50 },
    { sku: 'SKU008', name: 'Yoga Mat', barcode: '1234567890130', category: 'Sports', stock: 5, price: 34.99, minStock: 8 },
];

const salesData = [
    { date: '2024-01-22', product: 'Wireless Headphones', qty: 2, price: 129.99, category: 'Electronics' },
    { date: '2024-01-22', product: 'T-Shirt Blue', qty: 5, price: 24.99, category: 'Clothing' },
    { date: '2024-01-23', product: 'Running Shoes', qty: 3, price: 89.99, category: 'Sports' },
    { date: '2024-01-23', product: 'USB-C Cable', qty: 10, price: 19.99, category: 'Electronics' },
    { date: '2024-01-24', product: 'Office Chair', qty: 1, price: 199.99, category: 'Home' },
    { date: '2024-01-25', product: 'Desk Lamp', qty: 4, price: 45.99, category: 'Home' },
    { date: '2024-01-26', product: 'Mouse Pad', qty: 20, price: 12.99, category: 'Electronics' },
];

let charts = {};
let editingIndex = -1;
let deleteIndex = -1;

document.addEventListener('DOMContentLoaded', () => {
    renderInventory(inventoryData);
    updateDashboard();
    initCharts();
    updateReports();
});

function updateDashboard() {
    const totalInventory = inventoryData.reduce((sum, item) => sum + item.stock, 0);
    const lowStockItems = inventoryData.filter(item => item.stock > 0 && item.stock <= item.minStock).length;
    const outOfStock = inventoryData.filter(item => item.stock === 0).length;

    const metrics = document.querySelectorAll('.metric-value');
    if (metrics.length >= 2) {
        metrics[1].textContent = totalInventory;
        metrics[2].textContent = lowStockItems;
        metrics[3].textContent = outOfStock;
    }
}

function showPage(navEvent, pageName) {
    if (navEvent) navEvent.preventDefault();

    document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
    document.getElementById(pageName).classList.add('active');

    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if (navEvent?.currentTarget) navEvent.currentTarget.classList.add('active');

    const titles = {
        dashboard: 'Dashboard',
        inventory: 'Inventory Management',
        scanner: 'Barcode Scanner',
        reports: 'Sales Reports'
    };
    document.getElementById('pageTitle').textContent = titles[pageName];

    if (pageName === 'scanner') {
        document.getElementById('barcodeInput').focus();
    }
}

function openAddModal() {
    editingIndex = -1;
    document.getElementById('modalTitle').textContent = 'Add New Item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemModal').classList.add('active');
}

function openEditModal(index) {
    editingIndex = index;
    const item = inventoryData[index];
    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('formSku').value = item.sku;
    document.getElementById('formName').value = item.name;
    document.getElementById('formBarcode').value = item.barcode;
    document.getElementById('formCategory').value = item.category;
    document.getElementById('formStock').value = item.stock;
    document.getElementById('formPrice').value = item.price;
    document.getElementById('formMinStock').value = item.minStock;
    document.getElementById('itemModal').classList.add('active');
}

function closeModal() {
    document.getElementById('itemModal').classList.remove('active');
    editingIndex = -1;
}

function saveItem(event) {
    event.preventDefault();

    const newItem = {
        sku: document.getElementById('formSku').value,
        name: document.getElementById('formName').value,
        barcode: document.getElementById('formBarcode').value,
        category: document.getElementById('formCategory').value,
        stock: parseInt(document.getElementById('formStock').value, 10),
        price: parseFloat(document.getElementById('formPrice').value),
        minStock: parseInt(document.getElementById('formMinStock').value, 10)
    };

    if (editingIndex === -1) {
        if (inventoryData.some(item => item.barcode === newItem.barcode)) {
            alert('Barcode already exists!');
            return;
        }
        inventoryData.push(newItem);
        showNotification('Item added successfully!', 'success');
    } else {
        if (inventoryData.some((item, idx) => idx !== editingIndex && item.barcode === newItem.barcode)) {
            alert('Barcode already exists!');
            return;
        }
        inventoryData[editingIndex] = newItem;
        showNotification('Item updated successfully!', 'success');
    }

    localStorage.setItem('inventoryData', JSON.stringify(inventoryData));
    renderInventory(inventoryData);
    updateDashboard();
    initCharts();
    closeModal();
}

function openDeleteModal(index) {
    deleteIndex = index;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteIndex = -1;
}

function confirmDelete() {
    if (deleteIndex === -1) return;
    const itemName = inventoryData[deleteIndex].name;
    inventoryData.splice(deleteIndex, 1);
    localStorage.setItem('inventoryData', JSON.stringify(inventoryData));
    renderInventory(inventoryData);
    updateDashboard();
    initCharts();
    showNotification(`${itemName} deleted successfully!`, 'success');
    closeDeleteModal();
}

function showNotification(message, type = 'success') {
    const alertBox = document.getElementById('scannerAlert');
    alertBox.className = `alert active alert-${type}`;
    alertBox.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    setTimeout(() => alertBox.classList.remove('active'), 3000);
}

function renderInventory(data) {
    const tbody = document.getElementById('inventoryBody');
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">No items in inventory. <a href="#" onclick="openAddModal(); return false;" style="color: var(--primary);">Add one now</a></td></tr>';
        return;
    }

    tbody.innerHTML = data.map((item, idx) => {
        let status;
        let badge;
        if (item.stock === 0) {
            status = 'Out of Stock';
            badge = 'badge-danger';
        } else if (item.stock <= item.minStock) {
            status = 'Low Stock';
            badge = 'badge-warning';
        } else {
            status = 'In Stock';
            badge = 'badge-success';
        }
        return `
            <tr>
                <td><strong>${item.sku}</strong></td>
                <td>${item.name}</td>
                <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 12px;">${item.barcode}</code></td>
                <td>${item.category}</td>
                <td><strong>${item.stock}</strong></td>
                <td>$${item.price.toFixed(2)}</td>
                <td><span class="badge ${badge}">${status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-edit" onclick="openEditModal(${idx})"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-delete" onclick="openDeleteModal(${idx})"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function filterInventory() {
    const searchTerm = document.getElementById('inventorySearch').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const stockStatus = document.getElementById('stockFilter').value;

    const filtered = inventoryData.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchTerm) || item.sku.toLowerCase().includes(searchTerm) || item.barcode.includes(searchTerm);
        const matchesCategory = !category || item.category === category;
        let matchesStock = true;

        if (stockStatus === 'in-stock') matchesStock = item.stock > item.minStock;
        else if (stockStatus === 'low-stock') matchesStock = item.stock > 0 && item.stock <= item.minStock;
        else if (stockStatus === 'out-stock') matchesStock = item.stock === 0;

        return matchesSearch && matchesCategory && matchesStock;
    });

    renderInventory(filtered);
}

function sortInventory(by) {
    const sorted = [...inventoryData];
    if (by === 'name') sorted.sort((a, b) => a.name.localeCompare(b.name));
    renderInventory(sorted);
}

document.getElementById('barcodeInput')?.addEventListener('keypress', function (e) {
    if (e.key !== 'Enter') return;
    const barcode = this.value.trim();
    const item = inventoryData.find(i => i.barcode === barcode);

    const alertBox = document.getElementById('scannerAlert');
    const result = document.getElementById('scannerResult');

    if (item) {
        alertBox.className = 'alert active alert-success';
        alertBox.innerHTML = '<i class="fas fa-check-circle"></i> Item found!';
        result.classList.add('active');

        const status = item.stock === 0 ? 'Out of Stock' : (item.stock <= item.minStock ? 'Low Stock' : 'In Stock');
        document.getElementById('scannerDetail').innerHTML = `
            <div class="detail-item">
                <div class="detail-label">Product Name</div>
                <div class="detail-value">${item.name}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">SKU</div>
                <div class="detail-value">${item.sku}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Barcode</div>
                <div class="detail-value">${item.barcode}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Category</div>
                <div class="detail-value">${item.category}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Stock Quantity</div>
                <div class="detail-value">${item.stock}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Unit Price</div>
                <div class="detail-value">$${item.price.toFixed(2)}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">${status}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Min. Stock Level</div>
                <div class="detail-value">${item.minStock}</div>
            </div>
        `;
    } else {
        alertBox.className = 'alert active alert-danger';
        alertBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> Barcode not found in inventory';
        result.classList.remove('active');
    }

    this.value = '';
    setTimeout(() => this.focus(), 100);
});

function initCharts() {
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    font: { size: 12, family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" },
                    color: '#64748b'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#e2e8f0', drawBorder: false },
                ticks: { color: '#64748b', font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#64748b', font: { size: 11 } }
            }
        }
    };

    if (charts.dailySales) charts.dailySales.destroy();
    charts.dailySales = new Chart(document.getElementById('dailySalesChart'), {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales ($)',
                data: [4500, 5200, 4800, 6100, 7200, 8900, 6500],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: { ...chartConfig, plugins: { ...chartConfig.plugins, filler: { propagate: true } } }
    });

    const categoryStats = {};
    inventoryData.forEach(item => {
        categoryStats[item.category] = (categoryStats[item.category] || 0) + item.stock;
    });

    if (charts.categorySales) charts.categorySales.destroy();
    charts.categorySales = new Chart(document.getElementById('categorySalesChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(categoryStats),
            datasets: [{
                data: Object.values(categoryStats),
                backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b'],
                borderColor: 'white',
                borderWidth: 2
            }]
        },
        options: { ...chartConfig }
    });

    const topProducts = [...inventoryData].sort((a, b) => b.stock - a.stock).slice(0, 4);

    if (charts.topProducts) charts.topProducts.destroy();
    charts.topProducts = new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.name),
            datasets: [{
                label: 'Stock Units',
                data: topProducts.map(p => p.stock),
                backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b'],
                borderRadius: 8
            }]
        },
        options: { ...chartConfig, indexAxis: 'y', scales: { x: { ...chartConfig.scales.x }, y: { grid: { display: false } } } }
    });

    if (charts.monthlySales) charts.monthlySales.destroy();
    charts.monthlySales = new Chart(document.getElementById('monthlySalesChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Sales ($)',
                data: [35000, 41000, 38000, 45000, 52000, 54000],
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#7c3aed',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: { ...chartConfig, plugins: { ...chartConfig.plugins, filler: { propagate: true } } }
    });
}

function updateReports() {
    const period = document.getElementById('reportPeriod').value;

    const reportLabels = period === 'daily' ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] :
        period === 'weekly' ? ['Week 1', 'Week 2', 'Week 3', 'Week 4'] :
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

    const reportData = period === 'daily' ? [4500, 5200, 4800, 6100, 7200, 8900, 6500] :
        period === 'weekly' ? [35000, 38000, 42000, 39000] :
            [35000, 41000, 38000, 45000, 52000, 54000];

    if (charts.reportLine) charts.reportLine.destroy();
    charts.reportLine = new Chart(document.getElementById('reportLineChart'), {
        type: 'line',
        data: {
            labels: reportLabels,
            datasets: [{
                label: 'Sales',
                data: reportData,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: 'white',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { size: 12 }, color: '#64748b' } } },
            scales: { y: { grid: { color: '#e2e8f0' }, ticks: { color: '#64748b' } }, x: { grid: { display: false }, ticks: { color: '#64748b' } } }
        }
    });

    if (charts.reportPie) charts.reportPie.destroy();
    charts.reportPie = new Chart(document.getElementById('reportPieChart'), {
        type: 'pie',
        data: {
            labels: ['Electronics', 'Clothing', 'Home & Garden', 'Sports'],
            datasets: [{
                data: [28000, 12000, 8000, 6500],
                backgroundColor: ['#2563eb', '#7c3aed', '#10b981', '#f59e0b'],
                borderColor: 'white',
                borderWidth: 2
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { font: { size: 12 }, color: '#64748b' } } } }
    });

    const tbody = document.getElementById('reportBody');
    tbody.innerHTML = salesData.map(sale => `
        <tr>
            <td>${sale.date}</td>
            <td>${sale.product}</td>
            <td>${sale.qty}</td>
            <td>$${sale.price.toFixed(2)}</td>
            <td><strong>$${(sale.qty * sale.price).toFixed(2)}</strong></td>
        </tr>
    `).join('');
}
