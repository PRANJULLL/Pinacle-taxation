<?php
// tasks.php
require_once 'includes/db.php';
include 'includes/header.php';
?>

<!-- Tasks CSS / JS context -->
<div class="container-fluid px-0">
    <!-- Filter Row -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-3 align-items-center">
            <!-- Filter: Status -->
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="All">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Stuck">Stuck</option>
                </select>
            </div>
            <!-- Filter: Employee -->
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Task Expert</label>
                <select class="form-select form-select-sm" id="filterEmployee">
                    <option value="All">All Experts</option>
                    <option value="Jay">Jay</option>
                    <option value="Mohan">Mohan</option>
                    <option value="Prem">Prem</option>
                    <option value="Vivek">Vivek</option>
                </select>
            </div>
            <!-- Filter: Plan -->
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Plan</label>
                <select class="form-select form-select-sm" id="filterPlan">
                    <option value="All">All Plans</option>
                    <option value="Basic">Basic (500)</option>
                    <option value="Premium">Premium (1300)</option>
                    <option value="Elite">Elite (1800)</option>
                    <option value="Elite RSU">Elite RSU (2000)</option>
                </select>
            </div>
            <!-- Filter: Date -->
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Date Filter</label>
                <select class="form-select form-select-sm" id="filterDate">
                    <option value="All">All Time</option>
                    <option value="Today">Today</option>
                    <option value="This Week">This Week</option>
                    <option value="This Month">This Month</option>
                </select>
            </div>
            <!-- Reset Button -->
            <div class="col-12 col-md-3 col-lg-2 mt-auto d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm w-100" id="btnResetFilters" type="button">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="card border-0 shadow-sm mb-4">
        <!-- Loader Skeleton -->
        <div id="tableLoader" class="p-5 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2 small">Loading tasks catalog...</p>
        </div>

        <!-- Empty State -->
        <div id="tableEmptyState" class="p-5 text-center d-none">
            <i class="bi bi-folder-x fs-1 text-muted"></i>
            <h4 class="mt-3 fs-6 fw-bold">No tasks found</h4>
            <p class="text-muted small">Try adjusting your filters or search criteria.</p>
        </div>

        <!-- Actual Table Element -->
        <div id="tableContentContainer" class="d-none">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tasksTable">
                    <thead class="table-light">
                        <tr id="tableHeadersRow">
                            <!-- Headers will be injected dynamically -->
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Rows will be injected dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Footer -->
            <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between py-3 px-4">
                <span class="small text-muted" id="paginationStats">Showing 0 of 0 tasks</span>
                <nav aria-label="Page navigation">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary px-3" id="btnPagePrev" disabled>Previous</button>
                        <span class="small mx-2" id="paginationPageNum">Page 1 of 1</span>
                        <button class="btn btn-sm btn-outline-secondary px-3" id="btnPageNext" disabled>Next</button>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT TASK FORM -->
<!-- ========================================================================= -->
<div class="modal fade" id="taskFormModal" tabindex="-1" aria-labelledby="taskFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="taskForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="taskFormModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="formTaskId" name="id">
                    <div class="row g-3">
                        <!-- Client -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Client <span class="text-danger">*</span></label>
                            <select class="form-select" id="formClient" name="client" required>
                                <option value="Pinnacle">Pinnacle</option>
                                <option value="Vishnu">Vishnu</option>
                                <option value="Clear Tax">Clear Tax</option>
                            </select>
                        </div>
                        
                        <!-- Order ID (Clear Tax only) -->
                        <div class="col-12 col-md-6" id="formOrderIdContainer">
                            <label class="form-label small fw-medium">Order ID</label>
                            <input type="text" class="form-control" id="formOrderId" name="orderId" placeholder="Autogenerated if empty">
                        </div>

                        <!-- Customer Name -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="formCustomerName" name="customerName" required placeholder="e.g. John Doe">
                        </div>

                        <!-- PAN (Hidden for Clear Tax) -->
                        <div class="col-12 col-md-6" id="formPanContainer">
                            <label class="form-label small fw-medium">PAN Card</label>
                            <input type="text" class="form-control" id="formPan" name="pan" placeholder="e.g. ABCDE1234F" maxlength="10">
                        </div>

                        <!-- Phone -->
                        <div class="col-12 col-md-6" id="formPhoneContainer">
                            <label class="form-label small fw-medium">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="formPhone" name="phone" required placeholder="e.g. 9876543210">
                        </div>

                        <!-- Email -->
                        <div class="col-12 col-md-6" id="formEmailContainer">
                            <label class="form-label small fw-medium">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="formEmail" name="email" required placeholder="e.g. customer@email.com">
                        </div>

                        <!-- Plan -->
                        <div class="col-12 col-md-6" id="formPlanContainer">
                            <label class="form-label small fw-medium">Filing Plan <span class="text-danger">*</span></label>
                            <select class="form-select" id="formPlan" name="plan" required>
                                <option value="Basic">Basic</option>
                                <option value="Premium">Premium</option>
                                <option value="Elite">Elite</option>
                                <option value="Elite RSU">Elite RSU</option>
                            </select>
                        </div>

                        <!-- Amount (Disabled / fixed for Clear Tax) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Filing Amount (INR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="formAmount" name="amount" required min="0">
                            <small class="text-muted" id="formAmountHelp">Pricing is dynamic for Pinnacle/Vishnu, but locked for Clear Tax.</small>
                        </div>

                        <!-- Tax Expert -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Assigned Expert <span class="text-danger">*</span></label>
                            <select class="form-select" id="formTaxExpert" name="taxExpert" required>
                                <option value="Jay">Jay</option>
                                <option value="Mohan">Mohan</option>
                                <option value="Prem">Prem</option>
                                <option value="Vivek">Vivek</option>
                            </select>
                        </div>

                        <!-- Backdated Registration (Optional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Created Date (Override)</label>
                            <input type="datetime-local" class="form-control" id="formCreatedAt" name="createdAt">
                        </div>

                        <!-- Reference -->
                        <div class="col-12">
                            <label class="form-label small fw-medium">Reference Code / Link</label>
                            <input type="text" class="form-control" id="formReference" name="reference" placeholder="e.g. Ref-123">
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label class="form-label small fw-medium">Filing Remarks / Notes</label>
                            <textarea class="form-control" id="formRemarks" name="remarks" rows="2" placeholder="Include special request notes here..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitForm">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VIEW TASK DETAILS -->
<!-- ========================================================================= -->
<div class="modal fade" id="taskViewModal" tabindex="-1" aria-labelledby="taskViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="taskViewModalLabel">Filing Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="taskViewContent">
                    <!-- Contents populated dynamically -->
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm px-3" id="btnViewDelete">Delete Task</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnViewEdit">Edit</button>
                </div>
                <div>
                    <button type="button" class="btn btn-success btn-sm px-3 d-none" id="btnViewComplete">Mark Completed</button>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts implementation -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Enable search bar in the Header
    const searchContainer = document.getElementById('headerSearchContainer');
    const searchInput = document.getElementById('headerSearchInput');
    if (searchContainer) searchContainer.classList.remove('d-none');

    // Add task button and Export Excel in header actions
    const headerActions = document.getElementById('headerActions');
    if (headerActions) {
        headerActions.innerHTML = `
            <button class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1 hover-lift" id="btnExportExcel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </button>
            <button class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1" id="btnAddHeader">
                <i class="bi bi-plus-lg"></i> Add Task
            </button>
        `;
        document.getElementById('btnExportExcel').addEventListener('click', () => {
            const params = new URLSearchParams({
                client: activeClient,
                status: document.getElementById('filterStatus').value,
                employee: document.getElementById('filterEmployee').value,
                plan: document.getElementById('filterPlan').value,
                dateFilter: document.getElementById('filterDate').value,
                search: searchInput.value
            });
            window.open(`api/reports.php?action=excel&${params.toString()}`, '_blank');
        });
    }

    // Modal Objects
    const taskFormModal = new bootstrap.Modal(document.getElementById('taskFormModal'));
    const taskViewModal = new bootstrap.Modal(document.getElementById('taskViewModal'));

    // State Variables
    let activeClient = getCookie('selected_client') || 'Pinnacle';
    let currentPage = 1;
    let totalPages = 1;
    let sortBy = 'createdAt';
    let sortOrder = 'desc';
    let tasksCache = [];
    let activeViewedTask = null;

    // Load initial tasks
    fetchTasks();

    // Event listener: Client selector changes
    window.addEventListener('clientChanged', (e) => {
        activeClient = e.detail.client;
        currentPage = 1;
        fetchTasks();
    });

    // Event listeners: Filter selects
    ['filterStatus', 'filterEmployee', 'filterPlan', 'filterDate'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            currentPage = 1;
            fetchTasks();
        });
    });

    // Event listener: Search inputs
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchTasks();
        }, 300);
    });

    // Reset Filters
    document.getElementById('btnResetFilters').addEventListener('click', () => {
        document.getElementById('filterStatus').value = 'All';
        document.getElementById('filterEmployee').value = 'All';
        document.getElementById('filterPlan').value = 'All';
        document.getElementById('filterDate').value = 'All';
        searchInput.value = '';
        currentPage = 1;
        fetchTasks();
    });

    // Header title setup
    document.getElementById('headerTitle').textContent = 'Filing Tasks';
    document.getElementById('headerSubtitle').textContent = 'Manage all active task experts workflows';

    // Pagination controls
    document.getElementById('btnPagePrev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchTasks();
        }
    });
    document.getElementById('btnPageNext').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            fetchTasks();
        }
    });

    // Add Task Button
    document.getElementById('btnAddHeader').addEventListener('click', () => {
        openFormModal(null);
    });

    // Dynamic Form pricing calculations & Field visibilities
    const formClient = document.getElementById('formClient');
    const formPlan = document.getElementById('formPlan');
    const formPlanContainer = document.getElementById('formPlanContainer');
    const formAmount = document.getElementById('formAmount');
    const formOrderIdContainer = document.getElementById('formOrderIdContainer');
    const formPanContainer = document.getElementById('formPanContainer');
    const formPhone = document.getElementById('formPhone');
    const formPhoneContainer = document.getElementById('formPhoneContainer');
    const formEmail = document.getElementById('formEmail');
    const formEmailContainer = document.getElementById('formEmailContainer');

    const PLAN_PRICING = { Basic: 500, Premium: 1300, Elite: 1800, 'Elite RSU': 2000 };

    function adjustFormFields() {
        const clientVal = formClient.value;
        const planVal = formPlan.value;

        if (clientVal === 'Clear Tax') {
            // Locked pricing
            formAmount.value = PLAN_PRICING[planVal] || 0;
            formAmount.setAttribute('readonly', 'true');
            formAmount.classList.add('bg-light');
            // Show OrderID
            formOrderIdContainer.classList.remove('d-none');
            // Hide PAN
            formPanContainer.classList.add('d-none');
            document.getElementById('formPan').removeAttribute('required');
            // Show Plan
            formPlanContainer.classList.remove('d-none');
            formPlan.setAttribute('required', 'true');
            // Show Phone & Email
            formPhoneContainer.classList.remove('d-none');
            formPhone.setAttribute('required', 'true');
            formEmailContainer.classList.remove('d-none');
            formEmail.setAttribute('required', 'true');
        } else {
            // Editable pricing
            formAmount.removeAttribute('readonly');
            formAmount.classList.remove('bg-light');
            // Hide OrderID (Generated by Server)
            formOrderIdContainer.classList.add('d-none');
            // Show PAN
            formPanContainer.classList.remove('d-none');
            // Hide Plan
            formPlanContainer.classList.add('d-none');
            formPlan.removeAttribute('required');
            // Hide Phone & Email
            formPhoneContainer.classList.add('d-none');
            formPhone.removeAttribute('required');
            formEmailContainer.classList.add('d-none');
            formEmail.removeAttribute('required');
        }
    }

    formClient.addEventListener('change', adjustFormFields);
    formPlan.addEventListener('change', adjustFormFields);

    // Form Submit Handler
    document.getElementById('taskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const taskId = document.getElementById('formTaskId').value;
        const payload = {
            client: formClient.value,
            customerName: document.getElementById('formCustomerName').value,
            pan: document.getElementById('formPan').value,
            phone: formClient.value === 'Clear Tax' ? formPhone.value : '',
            email: formClient.value === 'Clear Tax' ? formEmail.value : '',
            plan: formClient.value === 'Clear Tax' ? formPlan.value : '',
            amount: parseFloat(formAmount.value) || 0,
            taxExpert: document.getElementById('formTaxExpert').value,
            remarks: document.getElementById('formRemarks').value,
            reference: document.getElementById('formReference').value,
            createdAt: document.getElementById('formCreatedAt').value || null
        };

        if (formClient.value === 'Clear Tax' && document.getElementById('formOrderId').value) {
            payload.orderId = document.getElementById('formOrderId').value;
        }

        const url = taskId ? `api/tasks.php?id=${taskId}` : 'api/tasks.php';
        const method = taskId ? 'PUT' : 'POST';

        document.getElementById('btnSubmitForm').setAttribute('disabled', 'true');

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: taskId ? 'Task updated successfully' : 'Task assigned successfully',
                    showConfirmButton: false,
                    timer: 3000
                });
                taskFormModal.hide();
                fetchTasks();
            } else {
                throw new Error(result.message || 'Failed to submit task');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        } finally {
            document.getElementById('btnSubmitForm').removeAttribute('disabled');
        }
    });

    // Detailed Modal Actions
    document.getElementById('btnViewEdit').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            // Need slight delay to allow backdrop fade
            setTimeout(() => {
                openFormModal(activeViewedTask);
            }, 300);
        }
    });

    document.getElementById('btnViewDelete').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            setTimeout(() => {
                confirmDelete(activeViewedTask.id);
            }, 300);
        }
    });

    document.getElementById('btnViewComplete').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            setTimeout(() => {
                completeTask(activeViewedTask);
            }, 300);
        }
    });

    // -------------------------------------------------------------
    // Core API Requests
    // -------------------------------------------------------------
    async function fetchTasks() {
        const tableLoader = document.getElementById('tableLoader');
        const tableEmptyState = document.getElementById('tableEmptyState');
        const tableContentContainer = document.getElementById('tableContentContainer');

        tableLoader.classList.remove('d-none');
        tableEmptyState.classList.add('d-none');
        tableContentContainer.classList.add('d-none');

        const params = new URLSearchParams({
            client: activeClient,
            status: document.getElementById('filterStatus').value,
            employee: document.getElementById('filterEmployee').value,
            plan: document.getElementById('filterPlan').value,
            dateFilter: document.getElementById('filterDate').value,
            search: searchInput.value,
            page: currentPage,
            limit: 10,
            sortBy: sortBy,
            sortOrder: sortOrder
        });

        try {
            const res = await fetch(`api/tasks.php?${params.toString()}`);
            const data = await res.json();

            tasksCache = data.tasks;
            totalPages = data.totalPages;

            if (tasksCache.length === 0) {
                tableEmptyState.classList.remove('d-none');
                tableLoader.classList.add('d-none');
                return;
            }

            renderHeaders();
            renderRows();

            // Setup pagination controls
            document.getElementById('paginationStats').textContent = `Showing ${tasksCache.length} of ${data.total} tasks`;
            document.getElementById('paginationPageNum').textContent = `Page ${currentPage} of ${totalPages || 1}`;
            document.getElementById('btnPagePrev').disabled = currentPage <= 1;
            document.getElementById('btnPageNext').disabled = currentPage >= totalPages;

            tableContentContainer.classList.remove('d-none');
            tableLoader.classList.add('d-none');
        } catch (err) {
            console.error('Failed loading tasks list', err);
            tableLoader.classList.add('d-none');
            Swal.fire('Error', 'Failed to load tasks', 'error');
        }
    }

    // Render columns headers dynamically according to client rules
    function renderHeaders() {
        const tr = document.getElementById('tableHeadersRow');
        let html = '';

        // Sorting icon helper
        function sortIcon(field) {
            if (sortBy === field) {
                return `<i class="bi bi-arrow-${sortOrder === 'asc' ? 'up' : 'down'} small ms-1"></i>`;
            }
            return `<i class="bi bi-arrow-down-up small ms-1 text-muted opacity-50"></i>`;
        }

        // 1. Pinnacle/Vishnu has Serial Number
        if (activeClient === 'Pinnacle' || activeClient === 'Vishnu') {
            html += `<th class="text-nowrap" style="width: 60px;">Sr No</th>`;
        } else {
            // Others show Order ID
            html += `<th class="text-nowrap" style="cursor: pointer;" onclick="window.setSort('orderId')">Order ID ${sortIcon('orderId')}</th>`;
        }

        // 2. Client Name (Always visible)
        html += `<th class="text-nowrap">Client</th>`;

        // 3. Customer Name
        html += `<th class="text-nowrap" style="cursor: pointer;" onclick="window.setSort('customerName')">Customer Name ${sortIcon('customerName')}</th>`;

        // 4. PAN Card (hidden for Clear Tax)
        if (activeClient !== 'Clear Tax') {
            html += `<th class="text-nowrap">PAN Card</th>`;
        }

        // 5. Phone, Email & Plan (hidden for Pinnacle/Vishnu)
        if (activeClient !== 'Pinnacle' && activeClient !== 'Vishnu') {
            html += `<th class="text-nowrap">Phone</th>`;
            html += `<th class="text-nowrap">Email</th>`;
            html += `<th class="text-nowrap">Plan</th>`;
        }

        // 7. Amount & Expert & Status & Created At
        html += `<th class="text-nowrap" style="cursor: pointer;" onclick="window.setSort('amount')">Amount ${sortIcon('amount')}</th>`;
        html += `<th class="text-nowrap" style="cursor: pointer;" onclick="window.setSort('taxExpert')">Task Expert ${sortIcon('taxExpert')}</th>`;
        html += `<th class="text-nowrap" style="cursor: pointer;" onclick="window.setSort('status')">Status ${sortIcon('status')}</th>`;
        html += `<th class="text-nowrap">Remarks</th>`;
        html += `<th class="text-nowrap text-end" style="width: 120px;">Actions</th>`;

        tr.innerHTML = html;
    }

    // Expose sorting globally to simplify column header onclick binding
    window.setSort = (field) => {
        if (sortBy === field) {
            sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy = field;
            sortOrder = 'desc';
        }
        currentPage = 1;
        fetchTasks();
    };

    // Render table rows dynamically
    function renderRows() {
        const tbody = document.getElementById('tableBody');
        let html = '';

        tasksCache.forEach((task, index) => {
            const statusClass = `badge-status-${task.status.toLowerCase()}`;
            const rowClass = `task-row-${task.status}`;

            html += `<tr class="${rowClass} transition-all">`;

            // 1. Sr No or Order ID
            if (activeClient === 'Pinnacle' || activeClient === 'Vishnu') {
                const srNo = (currentPage - 1) * 10 + index + 1;
                html += `<td class="fw-bold">${srNo}</td>`;
            } else {
                html += `<td class="text-nowrap font-monospace text-muted small">${task.orderId}</td>`;
            }

            // 2. Client (Always visible)
            html += `<td class="text-nowrap fw-semibold">${task.client}</td>`;

            // 3. Customer Name
            html += `<td class="text-nowrap fw-bold">${task.customerName}</td>`;

            // 4. PAN Card
            if (activeClient !== 'Clear Tax') {
                html += `<td class="text-nowrap font-monospace">${task.pan || '—'}</td>`;
            }

            // 5. Phone, Email & Plan (hidden for Pinnacle/Vishnu)
            if (activeClient !== 'Pinnacle' && activeClient !== 'Vishnu') {
                html += `<td class="text-nowrap">${task.phone}</td>`;
                html += `<td class="text-nowrap small text-muted">${task.email}</td>`;
                html += `<td class="text-nowrap">${task.plan}</td>`;
            }

            // 7. Amount, Expert, Status, Created At, Remarks
            html += `<td class="text-nowrap fw-bold">${formatCurrency(task.amount)}</td>`;
            html += `<td class="text-nowrap"><span class="badge bg-secondary-subtle text-secondary">${task.taxExpert}</span></td>`;
            html += `<td class="text-nowrap">
                <span class="badge-status ${statusClass}">
                    <span class="spinner-grow spinner-grow-sm d-none" role="status"></span>
                    ${task.status}
                </span>
            </td>`;
            html += `<td class="text-nowrap text-truncate small text-muted" style="max-width: 140px;" title="${task.remarks || ''}">${task.remarks || '—'}</td>`;

            // Actions dropdown menu & button
            html += `<td class="text-nowrap text-end">
                <div class="d-flex align-items-center justify-content-end gap-1">
                    ${task.status !== 'Completed' ? `
                        <button class="btn btn-outline-success btn-xs py-1 px-2 border-0" onclick="window.completeTaskById(${task.id})" title="Complete Task">
                            <i class="bi bi-check-circle-fill"></i>
                        </button>
                    ` : ''}
                    <div class="dropdown">
                        <button class="btn btn-light btn-xs py-1 px-2 border-0" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border border-light">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.viewTaskById(${task.id})"><i class="bi bi-eye me-2 text-primary"></i> View Details</a></li>
                            ${task.status !== 'Completed' ? `
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.completeTaskById(${task.id})"><i class="bi bi-check2-circle me-2 text-success"></i> Complete Task</a></li>
                            ` : ''}
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.editTaskById(${task.id})"><i class="bi bi-pencil me-2 text-warning"></i> Edit Details</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.generateInvoice(${task.id})"><i class="bi bi-file-earmark-pdf me-2 text-info"></i> Generate Invoice</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="window.deleteTaskById(${task.id})"><i class="bi bi-trash3 me-2"></i> Delete Task</a></li>
                        </ul>
                    </div>
                </div>
            </td>`;

            html += `</tr>`;
        });

        tbody.innerHTML = html;
    }

    // Form Dialog Open Helper
    function openFormModal(task = null) {
        const form = document.getElementById('taskForm');
        form.reset();
        form.classList.remove('was-validated');

        if (task) {
            // EDIT Mode
            document.getElementById('taskFormModalLabel').textContent = 'Edit Task Details';
            document.getElementById('formTaskId').value = task.id;
            
            formClient.value = task.client;
            formPlan.value = task.plan;
            document.getElementById('formCustomerName').value = task.customerName;
            document.getElementById('formPan').value = task.pan || '';
            document.getElementById('formPhone').value = task.phone;
            document.getElementById('formEmail').value = task.email;
            document.getElementById('formTaxExpert').value = task.taxExpert;
            formAmount.value = task.amount;
            document.getElementById('formRemarks').value = task.remarks || '';
            document.getElementById('formReference').value = task.reference || '';
            
            // Format datetime local format
            if (task.createdAt) {
                const date = new Date(task.createdAt);
                const isoStr = date.toISOString().slice(0, 16);
                document.getElementById('formCreatedAt').value = isoStr;
            }

            document.getElementById('btnSubmitForm').textContent = 'Save Changes';
        } else {
            // ADD Mode
            document.getElementById('taskFormModalLabel').textContent = 'Assign New Task';
            document.getElementById('formTaskId').value = '';
            
            formClient.value = activeClient === 'All' ? 'Pinnacle' : activeClient;
            formPlan.value = 'Basic';
            formAmount.value = 500;
            document.getElementById('formCreatedAt').value = '';

            document.getElementById('btnSubmitForm').textContent = 'Assign Task';
        }

        adjustFormFields();
        taskFormModal.show();
    }

    // Expose CRUD bindings globally for table events
    window.viewTaskById = (id) => {
        const task = tasksCache.find(t => t.id == id);
        if (!task) return;
        activeViewedTask = task;

        const content = document.getElementById('taskViewContent');
        let html = `
            <div class="col-12 col-md-6"><small class="text-muted d-block">Client Organization</small><strong class="fs-6">${task.client}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Order ID</small><strong class="fs-6 font-monospace">${task.orderId}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Customer Name</small><strong class="fs-6">${task.customerName}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">PAN Card</small><strong class="fs-6 font-monospace">${task.pan || '—'}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Phone Number</small><strong class="fs-6">${task.phone}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Email Address</small><strong class="fs-6">${task.email}</strong></div>
            ${task.client === 'Clear Tax' ? `<div class="col-12 col-md-6"><small class="text-muted d-block">Filing Plan</small><strong class="fs-6">${task.plan}</strong></div>` : ''}
            <div class="col-12 col-md-6"><small class="text-muted d-block">Amount Charged</small><strong class="fs-6">${formatCurrency(task.amount)}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Task Expert Assigned</small><span class="badge bg-secondary-subtle text-secondary fs-7 mt-1">${task.taxExpert}</span></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Current Status</small><span class="badge-status badge-status-${task.status.toLowerCase()} mt-1">${task.status}</span></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Assigned Date</small><strong class="fs-7 text-muted">${formatDate(task.createdAt)}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Completed Date</small><strong class="fs-7 text-muted">${formatDate(task.completedAt)}</strong></div>
            <div class="col-12 col-md-6"><small class="text-muted d-block">Reference Details</small><span class="font-monospace small text-secondary">${task.reference || '—'}</span></div>
            ${task.invoiceId ? `
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block">Generated Invoice</small>
                    <a href="api/invoices.php?action=download&invoiceNumber=INV-${new Date(task.createdAt).getFullYear()}-${String(task.invoiceId).padStart(5, '0')}" target="_blank" class="btn btn-outline-primary btn-xs mt-1 py-1 px-2">
                        <i class="bi bi-file-earmark-pdf"></i> Download PDF
                    </a>
                </div>
            ` : ''}
            <div class="col-12"><small class="text-muted d-block">Remarks / Notes</small><p class="mb-0 bg-light-subtle border p-3 rounded text-body fs-6" style="white-space: pre-wrap; word-break: break-word; line-height: 1.5;">${task.remarks || '—'}</p></div>
        `;

        content.innerHTML = html;

        // Toggle Complete Button inside View Dialog
        const compBtn = document.getElementById('btnViewComplete');
        if (task.status !== 'Completed') {
            compBtn.classList.remove('d-none');
        } else {
            compBtn.classList.add('d-none');
        }

        taskViewModal.show();
    };

    window.editTaskById = (id) => {
        const task = tasksCache.find(t => t.id == id);
        if (task) openFormModal(task);
    };

    window.deleteTaskById = (id) => {
        confirmDelete(id);
    };

    window.completeTaskById = (id) => {
        const task = tasksCache.find(t => t.id == id);
        if (task) completeTask(task);
    };

    // Invoice Generation Action
    window.generateInvoice = async (id) => {
        try {
            Swal.showLoading();
            const res = await fetch(`api/invoices.php?action=generate&taskId=${id}`, { method: 'POST' });
            const invoice = await res.json();
            
            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: `Invoice ${invoice.invoiceNumber} Generated`,
                    text: 'Opening download link in a new tab...',
                    timer: 2000,
                    showConfirmButton: false
                });
                fetchTasks();
                // Download PDF
                window.open(`api/invoices.php?action=download&invoiceNumber=${invoice.invoiceNumber}`, '_blank');
            } else {
                throw new Error(invoice.message || 'Failed to generate invoice');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    };

    // Delete confirmation prompt
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Task?',
            text: 'Are you sure you want to delete this filing assignment? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete Task',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`api/tasks.php?id=${id}`, { method: 'DELETE' });
                    if (res.ok) {
                        Swal.fire('Deleted!', 'Task deleted successfully.', 'success');
                        fetchTasks();
                    } else {
                        const err = await res.json();
                        throw new Error(err.message || 'Failed to delete task');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            }
        });
    }

    // Set task to Completed
    async function completeTask(task) {
        try {
            const res = await fetch(`api/tasks.php?id=${task.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: 'Completed' })
            });

            if (res.ok) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Task marked Completed',
                    showConfirmButton: false,
                    timer: 3000
                });
                fetchTasks();
            } else {
                const err = await res.json();
                throw new Error(err.message || 'Failed to update task status');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
