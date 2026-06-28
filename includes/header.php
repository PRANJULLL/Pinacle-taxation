<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get theme from cookie or default to light
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Get sidebar collapsed state from cookie
$sidebar_collapsed = isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === '1';

// Get current filename to highlight active sidebar item and apply custom page filters
$current_page = basename($_SERVER['PHP_SELF']);
$is_tasks_page = ($current_page === 'tasks.php');

// Get selected client from cookie or default to All (Pinnacle for tasks)
$selected_client = isset($_COOKIE['selected_client']) ? $_COOKIE['selected_client'] : 'All';
if ($is_tasks_page && $selected_client === 'All') {
    $selected_client = 'Pinnacle';
    setcookie('selected_client', 'Pinnacle', time() + (86400 * 30), "/"); // Save for 30 days
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo htmlspecialchars($theme); ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinnacle Accounting & Taxation - CA Office Management</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3+ CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- ApexCharts for Charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- SweetAlert2 for Toast and Confirm notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full bg-light-subtle text-body <?php echo $sidebar_collapsed ? 'sidebar-collapsed' : ''; ?>">

<div class="d-flex h-full min-h-screen">
    <!-- Sidebar Include -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Workspace Container -->
    <div class="flex-grow-1 d-flex flex-column min-h-screen content-wrapper">
        <!-- Top Sticky Header -->
        <header class="sticky-top border-b border-light bg-body bg-opacity-75 backdrop-blur py-3 px-4 shadow-sm z-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 md-menu-btn d-md-none">
                        <button class="btn btn-outline-secondary btn-sm me-2" id="sidebarToggle" aria-label="Toggle Sidebar">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <img src="assets/logo.png" alt="Logo" class="rounded" style="height: 32px; width: 32px; object-fit: contain;">
                        <div>
                            <p class="mb-0 fw-bold fs-6 lh-1">Pinnacle Accounting<br>& Taxation</p>
                        </div>
                    </div>
                    
                    <div class="d-none d-md-flex align-items-center gap-3">
                        <button class="btn btn-outline-secondary btn-sm" id="sidebarToggleDesktop" aria-label="Toggle Sidebar" title="Collapse/Expand Sidebar">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <div>
                            <h1 class="h5 fw-bold mb-0 text-foreground" id="headerTitle">Dashboard</h1>
                            <p class="text-muted small mb-0" id="headerSubtitle">Overview of office task management</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <!-- Dynamic Search Box (Conditional, populated by JS) -->
                        <div class="position-relative d-none" id="headerSearchContainer" style="width: 280px;">
                            <i class="bi bi-search position-absolute top-50 start-3 translate-middle-y text-muted"></i>
                            <input type="search" class="form-control ps-5 rounded-3 form-control-sm" placeholder="Search name, PAN, phone, email..." id="headerSearchInput">
                        </div>

                        <!-- Theme Toggle Button -->
                        <button class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" id="themeToggleBtn" style="width: 38px; height: 38px;" title="Toggle dark mode" aria-label="Toggle theme">
                            <i class="bi <?php echo ($theme === 'dark') ? 'bi-sun-fill text-warning' : 'bi-moon-fill'; ?>"></i>
                        </button>

                        <!-- Extra Actions placeholder (e.g. exports, add tasks) -->
                        <div id="headerActions" class="d-flex gap-2"></div>
                    </div>
                </div>

                <!-- Client Filter Bar (shown if showClientFilter is true, handled by page variables) -->
                <?php if (!isset($hide_client_filter) || !$hide_client_filter): ?>
                <div class="d-flex align-items-center gap-2 border-top pt-2 mt-1" id="clientFilterBar">
                    <span class="text-muted small fw-medium text-uppercase">Client:</span>
                    <div class="d-flex gap-2 flex-wrap" id="clientButtonsContainer">
                        <?php 
                        $clients = ['All', 'Pinnacle', 'Vishnu', 'Clear Tax'];
                        if ($is_tasks_page) {
                            $clients = ['Pinnacle', 'Vishnu', 'Clear Tax'];
                        }
                        foreach ($clients as $c): 
                            $activeClass = ($selected_client === $c) ? 'btn-primary' : 'btn-outline-secondary';
                        ?>
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 client-filter-btn <?php echo $activeClass; ?>" data-client="<?php echo htmlspecialchars($c); ?>">
                                <?php echo htmlspecialchars($c); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- Main Body Workspace -->
        <main class="p-4 flex-grow-1">
