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
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h3 class="h6 fw-bold mb-3 border-bottom pb-2">Tax Expert Work Performance</h3>
            <div id="chart-performance" style="min-height: 320px;"></div>
        </div>

        <!-- Detailed Breakdown Row -->
        <div class="row g-4">
            <!-- Revenue by Source / Client -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h3 class="h6 fw-bold mb-3 border-bottom pb-2">Revenue by Source (Client)</h3>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Source</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Stuck</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="rep-by-source-body">
                                <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Revenue by Filing Plan -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h3 class="h6 fw-bold mb-3 border-bottom pb-2">Revenue by Filing Plan</h3>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Plan</th>
                                    <th class="text-center">Total Tasks</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="rep-by-plan-body">
                                <tr><td colspan="4" class="text-center text-muted py-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detailed Tax Expert Performance -->
            <div class="col-12">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="h6 fw-bold mb-3 border-bottom pb-2">Tax Expert Detailed Performance</h3>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tax Expert</th>
                                    <th class="text-center">Assigned</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Stuck</th>
                                    <th class="text-center">Completion Rate</th>
                                    <th class="text-end">Revenue Generated</th>
                                </tr>
                            </thead>
                            <tbody id="rep-by-expert-body">
                                <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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

            // Populate Revenue by Source (Client) table
            const sourceBody = document.getElementById('rep-by-source-body');
            if (data.revenueByClient && data.revenueByClient.length) {
                sourceBody.innerHTML = data.revenueByClient.map(src => `
                    <tr>
                        <td class="fw-semibold">${src.client}</td>
                        <td class="text-center">${src.total}</td>
                        <td class="text-center text-success">${src.completed}</td>
                        <td class="text-center text-warning">${src.pending}</td>
                        <td class="text-center text-danger">${src.stuck}</td>
                        <td class="text-end fw-bold">${formatCurrency(src.revenue)}</td>
                    </tr>
                `).join('');
            } else {
                sourceBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No data available</td></tr>';
            }

            // Populate Revenue by Filing Plan table
            const planBody = document.getElementById('rep-by-plan-body');
            if (data.revenueByPlan && data.revenueByPlan.length) {
                planBody.innerHTML = data.revenueByPlan.map(pl => `
                    <tr>
                        <td class="fw-semibold">${pl.plan}</td>
                        <td class="text-center">${pl.total}</td>
                        <td class="text-center text-success">${pl.completed}</td>
                        <td class="text-end fw-bold">${formatCurrency(pl.revenue)}</td>
                    </tr>
                `).join('');
            } else {
                planBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No data available</td></tr>';
            }

            // Populate Detailed Tax Expert Performance table
            const expertBody = document.getElementById('rep-by-expert-body');
            if (data.employeePerformance && data.employeePerformance.length) {
                expertBody.innerHTML = data.employeePerformance.map(emp => {
                    const rate = emp.total > 0 ? Math.round((emp.completed / emp.total) * 100) : 0;
                    return `
                        <tr>
                            <td class="fw-semibold">${emp.name}</td>
                            <td class="text-center">${emp.total}</td>
                            <td class="text-center text-success">${emp.completed}</td>
                            <td class="text-center text-warning">${emp.pending}</td>
                            <td class="text-center text-danger">${emp.stuck}</td>
                            <td class="text-center">
                                <div class="progress" style="height: 6px; min-width: 80px;">
                                    <div class="progress-bar bg-primary" style="width: ${rate}%;"></div>
                                </div>
                                <small class="text-muted">${rate}%</small>
                            </td>
                            <td class="text-end fw-bold">${formatCurrency(emp.revenue)}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                expertBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No data available</td></tr>';
            }

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
