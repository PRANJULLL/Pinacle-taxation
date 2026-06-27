<?php
// invoices.php
require_once 'includes/db.php';
// Invoices list doesn't filter by client globally
$hide_client_filter = true;
include 'includes/header.php';
?>

<!-- Invoices HTML -->
<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm mb-4">
        <!-- Loader -->
        <div id="invoicesLoader" class="p-5 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2 small">Loading invoices list...</p>
        </div>

        <!-- Empty State -->
        <div id="invoicesEmptyState" class="p-5 text-center d-none text-muted">
            <i class="bi bi-file-earmark-x fs-1"></i>
            <h4 class="mt-3 fs-6 fw-bold">No invoices generated yet</h4>
            <p class="text-muted small">Go to the Tasks page to generate invoices for completed or active assignments.</p>
        </div>

        <!-- Table Container -->
        <div id="invoicesContent" class="d-none">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-uppercase small">
                        <tr>
                            <th>Invoice Number</th>
                            <th>Customer Name</th>
                            <th>Filing Plan</th>
                            <th>Amount</th>
                            <th>Date Generated</th>
                            <th class="text-end" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoicesTableBody">
                        <!-- Content injected dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Header title setup
    document.getElementById('headerTitle').textContent = 'Invoices';
    document.getElementById('headerSubtitle').textContent = 'Catalog of all client tax invoice records';

    fetchInvoices();

    // Fetch invoices list
    async function fetchInvoices() {
        const loader = document.getElementById('invoicesLoader');
        const emptyState = document.getElementById('invoicesEmptyState');
        const content = document.getElementById('invoicesContent');
        const tbody = document.getElementById('invoicesTableBody');

        loader.classList.remove('d-none');
        emptyState.classList.add('d-none');
        content.classList.add('d-none');

        try {
            const res = await fetch('api/invoices.php');
            const data = await res.json();

            if (data.length === 0) {
                emptyState.classList.remove('d-none');
                loader.classList.add('d-none');
                return;
            }

            let html = '';
            data.forEach(inv => {
                html += `
                    <tr>
                        <td class="font-monospace fw-bold text-primary">${inv.invoiceNumber}</td>
                        <td class="fw-bold">${inv.customerName}</td>
                        <td><span class="badge bg-light text-secondary border">${inv.plan}</span></td>
                        <td class="fw-bold">${formatCurrency(inv.amount)}</td>
                        <td class="text-muted small">${formatDate(inv.createdAt)}</td>
                        <td class="text-end">
                            <a href="api/invoices.php?action=download&invoiceNumber=${inv.invoiceNumber}" target="_blank" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center gap-1 hover-lift">
                                <i class="bi bi-file-earmark-pdf"></i> Download
                            </a>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            content.classList.remove('d-none');
            loader.classList.add('d-none');
        } catch (err) {
            console.error('Failed loading invoices list', err);
            loader.classList.add('d-none');
            Swal.fire('Error', 'Failed to load invoices list', 'error');
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
