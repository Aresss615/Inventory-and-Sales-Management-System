let charts = {};

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Item';
    document.getElementById('formId').value = '';
    document.getElementById('itemModal').classList.add('active');
}

function closeModal() {
    document.getElementById('itemModal').classList.remove('active');
}

function openDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

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

    if (window.dailySalesData && document.getElementById('dailySalesChart')) {
        if (charts.dailySales) charts.dailySales.destroy();
        charts.dailySales = new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: window.dailySalesData,
            options: chartConfig
        });
    }

    if (window.categorySalesData && document.getElementById('categorySalesChart')) {
        if (charts.categorySales) charts.categorySales.destroy();
        charts.categorySales = new Chart(document.getElementById('categorySalesChart'), {
            type: 'doughnut',
            data: window.categorySalesData,
            options: chartConfig
        });
    }

    if (window.topProductsData && document.getElementById('topProductsChart')) {
        if (charts.topProducts) charts.topProducts.destroy();
        charts.topProducts = new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: window.topProductsData,
            options: { ...chartConfig, indexAxis: 'y' }
        });
    }

    if (window.monthlySalesData && document.getElementById('monthlySalesChart')) {
        if (charts.monthlySales) charts.monthlySales.destroy();
        charts.monthlySales = new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: window.monthlySalesData,
            options: chartConfig
        });
    }

    if (window.reportLineData && document.getElementById('reportLineChart')) {
        if (charts.reportLine) charts.reportLine.destroy();
        charts.reportLine = new Chart(document.getElementById('reportLineChart'), {
            type: 'line',
            data: window.reportLineData,
            options: chartConfig
        });
    }

    if (window.reportPieData && document.getElementById('reportPieChart')) {
        if (charts.reportPie) charts.reportPie.destroy();
        charts.reportPie = new Chart(document.getElementById('reportPieChart'), {
            type: 'pie',
            data: window.reportPieData,
            options: chartConfig
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
