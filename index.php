<?php
// index.php
require_once 'includes/db.php';
include 'includes/header.php';
?>

<!-- Dashboard HTML -->
<div class="container-fluid px-0">
    <!-- Stats Cards Row -->
    <div class="row g-4 mb-5" id="statsCardsContainer">
        <!-- Card 1: Total Tasks -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Tasks</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-folder-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-total">—</div>
            </div>
        </div>
        <!-- Card 2: Pending Tasks -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Pending Tasks</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-clock-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-pending">—</div>
            </div>
        </div>
        <!-- Card 3: Completed Tasks -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Completed Tasks</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-completed">—</div>
            </div>
        </div>
        <!-- Card 4: Stuck Tasks -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Stuck Tasks</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-stuck">—</div>
            </div>
        </div>
        <!-- Card 5: Today's Tasks -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Today's Tasks</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-calendar-event-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-todayTasks">—</div>
            </div>
        </div>
        <!-- Card 6: Today's Completed -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4 hover-lift" style="border-top: 4px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Today's Completed</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-calendar-check-fill fs-5"></i>
                    </div>
                </div>
                <div class="display-5 fw-bold mb-0 text-foreground" id="stat-todayCompleted">—</div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Set Header Titles
    document.getElementById('headerTitle').textContent = 'Dashboard';
    document.getElementById('headerSubtitle').textContent = 'Overview of office task management';
    
    // Read Client cookie
    let activeClient = getCookie('selected_client') || 'All';

    // Load initial stats
    fetchDashboardStats(activeClient);

    // Listen to client selector event
    window.addEventListener('clientChanged', (e) => {
        activeClient = e.detail.client;
        fetchDashboardStats(activeClient);
    });

    // 1. Fetch Stats API
    async function fetchDashboardStats(client) {
        try {
            const res = await fetch(`api/dashboard.php?action=stats&client=${client}`);
            const data = await res.json();
            
            document.getElementById('stat-total').textContent = data.total;
            document.getElementById('stat-pending').textContent = data.pending;
            document.getElementById('stat-completed').textContent = data.completed;
            document.getElementById('stat-stuck').textContent = data.stuck;
            document.getElementById('stat-todayTasks').textContent = data.todayTasks;
            document.getElementById('stat-todayCompleted').textContent = data.todayCompleted;
        } catch (err) {
            console.error('Stats loading failed', err);
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
