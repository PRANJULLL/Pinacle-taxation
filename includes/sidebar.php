<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page === '' || $current_page === '/') {
    $current_page = 'index.php';
}
?>
<aside class="sidebar-container bg-body border-end h-full d-flex flex-column">
    <!-- Logo & Brand Header -->
    <div class="d-flex align-items-center gap-3 py-3 px-4 border-b border-light" style="height: 64px;">
        <img src="assets/logo.png" alt="Logo" class="rounded shadow-sm" style="height: 36px; width: 36px; object-fit: contain;">
        <div>
            <h2 class="h6 fw-bold mb-0 text-foreground">Pinnacle Accounting<br>& Taxation</h2>
        </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-grow-1 p-3 d-flex flex-column gap-1">
        <a href="index.php" class="nav-link-item <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2-fill me-2 fs-5"></i>
            Dashboard
        </a>
        <a href="tasks.php" class="nav-link-item <?php echo ($current_page === 'tasks.php') ? 'active' : ''; ?>">
            <i class="bi bi-check2-square me-2 fs-5"></i>
            Tasks
        </a>
        <a href="employees.php" class="nav-link-item <?php echo ($current_page === 'employees.php') ? 'active' : ''; ?>">
            <i class="bi bi-people me-2 fs-5"></i>
            Tax Experts
        </a>
        <a href="invoices.php" class="nav-link-item <?php echo ($current_page === 'invoices.php') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text me-2 fs-5"></i>
            Invoices
        </a>
        <a href="reports.php" class="nav-link-item <?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart-line me-2 fs-5"></i>
            Reports
        </a>
        <a href="settings.php" class="nav-link-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            <i class="bi bi-gear me-2 fs-5"></i>
            Settings
        </a>
        <a href="logout.php" class="nav-link-item text-danger mt-auto">
            <i class="bi bi-box-arrow-right me-2 fs-5"></i>
            Logout
        </a>
    </nav>

    <!-- Footer metadata -->
    <div class="p-4 border-top">
        <p class="text-muted mb-0" style="font-size: 11px;">Internal Office Tool</p>
    </div>
</aside>
