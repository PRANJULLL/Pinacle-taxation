<?php
// reports.php
require_once 'includes/db.php';
$hide_client_filter = true;
include 'includes/header.php';
?>

<!-- Reports HTML -->
<div class="container-fluid px-0">
    <!-- Loader -->
    <div id="reportsLoader" class="p-5 text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 small">Loading analytical reports...</p>
    </div>

    <!-- Analytics Dashboard Content -->
    <div id="reportsContent" class="d-none">
        <!-- Summary Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Card 1: Total Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Firm Revenue</span>
                        <i class="bi bi-currency-rupee text-primary fs-4"></i>
                    </div>
                    <div class="h3 fw-bold mb-0 text-foreground" id="rep-revenue">—</div>
                </div>
            </div>
            <!-- Card 2: Completed Tasks -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Completed Filings</span>
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div class="h3 fw-bold mb-0 text-success" id="rep-completed">—</div>
                </div>
            </div>
            <!-- Card 3: Pending Tasks -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Pending Work</span>
                        <i class="bi bi-clock text-warning fs-4"></i>
                    </div>
                    <div class="h3 fw-bold mb-0 text-warning" id="rep-pending">—</div>
                </div>
            </div>
            <!-- Card 4: Stuck Tasks -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Stuck Workloads</span>
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <div class="h3 fw-bold mb-0 text-danger" id="rep-stuck">—</div>
                </div>
            </div>
        </div>

        <!-- Performance Chart Row -->
        <div class="card border-0 shadow-sm p-4">
            <h3 class="h6 fw-bold mb-3 border-bottom pb-2">Employee Work Performance</h3>
            <div id="chart-performance" style="min-height: 320px;"></div>
        </div>
    </div>
</div>

<!-- Reports Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Header setup with Titles & Action Export Buttons
    document.getElementById('headerTitle').textContent = 'Reports';
    document.getElementById('headerSubtitle').textContent = 'Business analytics and export tools';

    const headerActions = document.getElementById('headerActions');
    if (headerActions) {
        headerActions.innerHTML = '';
    }

    let chartPerformance;

    fetchReportStats();

    // Redraw charts styling on Theme toggles
    window.addEventListener('themeChanged', () => {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        if (chartPerformance) {
            chartPerformance.updateOptions({
                theme: { mode: isDark ? 'dark' : 'light' }
            });
        }
    });

    // Fetch report stats
    async function fetchReportStats() {
        const loader = document.getElementById('reportsLoader');
        const content = document.getElementById('reportsContent');

        loader.classList.remove('d-none');
        content.classList.add('d-none');

        try {
            const res = await fetch('api/reports.php?action=stats');
            const data = await res.json();

            // Populate cards
            document.getElementById('rep-revenue').textContent = formatCurrency(data.revenue);
            document.getElementById('rep-completed').textContent = data.completedTasks;
            document.getElementById('rep-pending').textContent = data.pendingTasks;
            document.getElementById('rep-stuck').textContent = data.stuckTasks;

            // Render Employee performance chart (Completed vs Total)
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const themeMode = isDark ? 'dark' : 'light';

            const opt = {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [
                    { name: 'Completed Tasks', data: data.employeePerformance.map(x => x.completed) },
                    { name: 'Total Assigned', data: data.employeePerformance.map(x => x.total) }
                ],
                xaxis: { categories: data.employeePerformance.map(x => x.name) },
                colors: ['#16a34a', '#2563eb'],
                theme: { mode: themeMode },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } }
            };

            if (chartPerformance) chartPerformance.destroy();
            chartPerformance = new ApexCharts(document.querySelector("#chart-performance"), opt);
            chartPerformance.render();

            content.classList.remove('d-none');
            loader.classList.add('d-none');
        } catch (err) {
            console.error('Failed loading reports analytical summary', err);
            loader.classList.add('d-none');
            Swal.fire('Error', 'Failed to load reports analytical data', 'error');
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
