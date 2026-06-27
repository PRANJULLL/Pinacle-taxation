// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggler for Mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar-container');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });

        // Close sidebar on document click (outside sidebar)
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Sidebar Toggler for Desktop
    const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
    if (sidebarToggleDesktop) {
        sidebarToggleDesktop.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            setCookie('sidebar_collapsed', isCollapsed ? '1' : '0', 30);
        });
    }

    // 2. Theme Toggle Controller
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const htmlNode = document.documentElement;
            const currentTheme = htmlNode.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Set attributes
            htmlNode.setAttribute('data-bs-theme', newTheme);
            
            // Toggle icon classes
            const iconNode = themeToggleBtn.querySelector('i');
            if (newTheme === 'dark') {
                iconNode.className = 'bi bi-sun-fill text-warning';
            } else {
                iconNode.className = 'bi bi-moon-fill';
            }
            
            // Save theme preference in cookies for 30 days
            setCookie('theme', newTheme, 30);
            
            // Dispatch dynamic event for elements/charts that need to redraw
            window.dispatchEvent(new Event('themeChanged'));
        });
    }

    // 3. Client Filter Buttons Handler
    const clientButtons = document.querySelectorAll('.client-filter-btn');
    if (clientButtons) {
        clientButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const client = btn.getAttribute('data-client');
                
                // Set cookie
                setCookie('selected_client', client, 30);
                
                // Toggle active bootstrap classes
                clientButtons.forEach(b => b.classList.replace('btn-primary', 'btn-outline-secondary'));
                btn.classList.replace('btn-outline-secondary', 'btn-primary');
                
                // Fire custom global event indicating client change
                window.dispatchEvent(new CustomEvent('clientChanged', { detail: { client } }));
            });
        });
    }
});

// Helper: Read a cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Helper: Set a cookie value
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Utility: Format currency in Indian Style (INR)
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

// Utility: Format ISO Datetime string to human-readable date/time
function formatDate(dateString) {
    if (!dateString) return '—';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    
    const dateStr = d.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
    
    const timeStr = d.toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    
    return `${dateStr}, ${timeStr}`;
}
