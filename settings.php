<?php
// settings.php
require_once 'includes/db.php';
$hide_client_filter = true;
include 'includes/header.php';
?>

<!-- Settings HTML -->
<div class="container-fluid px-0 mx-auto" style="max-width: 720px;">
    <!-- Section 1: Appearance -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
            <i class="bi bi-palette text-primary"></i> Appearance
        </h3>
        <p class="text-muted small">Customize how the application looks.</p>
        <div class="d-flex align-items-center justify-content-between pt-2">
            <div>
                <strong class="d-block small">Dark Mode</strong>
                <span class="text-muted small">Toggle between light and dark themes.</span>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="settingsDarkModeToggle" style="width: 44px; height: 22px;">
            </div>
        </div>
    </div>

    <!-- Section 2: Clients -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
            <i class="bi bi-building text-primary"></i> Configured Clients
        </h3>
        <p class="text-muted small">Available client organizations configured in the system.</p>
        <div class="d-flex gap-2 flex-wrap pt-2">
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Pinnacle</span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Vishnu</span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Clear Tax</span>
        </div>
    </div>

    <!-- Section 3: Tax Experts -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
            <i class="bi bi-people text-primary"></i> Tax Experts (Employees)
        </h3>
        <p class="text-muted small">Active task experts inside the system to receive filing workloads.</p>
        <div class="d-flex gap-2 flex-wrap pt-2">
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Jay</span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Mohan</span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Prem</span>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">Vivek</span>
        </div>
    </div>

    <!-- Section 4: Plans -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h3 class="h6 fw-bold mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
            <i class="bi bi-file-earmark-text text-primary"></i> Plans & Pricing
        </h3>
        <p class="text-muted small">Filing service rates configured for client organizations.</p>
        <div class="list-group list-group-flush pt-2">
            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Basic Filing</span>
                <strong class="text-primary small">₹500</strong>
            </div>
            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Premium Filing</span>
                <strong class="text-primary small">₹1,300</strong>
            </div>
            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Elite Filing</span>
                <strong class="text-primary small">₹1,800</strong>
            </div>
            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Elite RSU Filing</span>
                <strong class="text-primary small">₹1,800</strong>
            </div>
        </div>
    </div>

    <!-- Section 5: About -->
    <div class="card border-0 shadow-sm p-4">
        <h3 class="h6 fw-bold mb-3 border-bottom pb-2">About System</h3>
        <p class="text-muted small mb-1">Pinnacle Accounting & Taxation v1.0.0 (PHP/MySQL Port)</p>
        <p class="text-muted small mb-1">Internal tool for office task and invoice management.</p>
        <p class="text-muted small mb-0">No authentication required — office local computer access only.</p>
    </div>
</div>

<!-- Settings Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Header title setup
    document.getElementById('headerTitle').textContent = 'Settings';
    document.getElementById('headerSubtitle').textContent = 'Application configurations and settings';

    const switchToggle = document.getElementById('settingsDarkModeToggle');
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    // Sync switch check state
    switchToggle.checked = isDark;

    // Switch handler triggers click on global header button
    switchToggle.addEventListener('change', () => {
        const themeBtn = document.getElementById('themeToggleBtn');
        if (themeBtn) {
            themeBtn.click();
        }
    });

    // Listen to themeChanged event to keep switch in sync
    window.addEventListener('themeChanged', () => {
        const isDarkNow = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        switchToggle.checked = isDarkNow;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
