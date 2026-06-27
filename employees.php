<?php
// employees.php
require_once 'includes/db.php';
// We hide the global client filter because the expert page has its own sub-filters
$hide_client_filter = true;
include 'includes/header.php';
?>

<!-- Experts UI HTML -->
<div class="container-fluid px-0">
    <!-- Loader Skeleton -->
    <div id="expertsLoader" class="p-5 text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 small">Loading task experts workload metrics...</p>
    </div>

    <!-- Active Experts Grid -->
    <div id="expertsGridContainer" class="row g-4 d-none mb-4">
        <!-- Cards injected dynamically -->
    </div>

    <!-- Expert Tasks Table Container -->
    <div id="expertTasksContainer" class="card border-0 shadow-sm p-4 d-none">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
            <div>
                <h3 class="h6 fw-bold mb-0 text-foreground" id="expertTasksTitle">All Assigned Tasks</h3>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <!-- Client Filter -->
                <div class="d-flex gap-2 align-items-center" id="expertSubFilterBar">
                    <span class="small text-muted fw-semibold text-uppercase me-1">Client:</span>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-sub-filter active" data-subfilter="All">All</button>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-sub-filter" data-subfilter="Pinnacle_Vishnu">Pinnacle & Vishnu</button>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-sub-filter" data-subfilter="Clear_Tax">Clear Tax</button>
                </div>
                <!-- Status Filter -->
                <div class="d-flex gap-2 align-items-center" id="expertStatusFilterBar">
                    <span class="small text-muted fw-semibold text-uppercase me-1">Status:</span>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-status-filter active" data-statusfilter="All">All</button>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-status-filter" data-statusfilter="Active">Pending & Stuck</button>
                    <button class="btn btn-sm rounded-pill px-3 py-1 btn-outline-secondary expert-status-filter" data-statusfilter="Completed">Completed</button>
                </div>
            </div>
        </div>

        <!-- Loader inside Table Container -->
        <div id="drawerLoader" class="text-center p-4 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="drawerContent">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0" id="expertTasksTable">
                    <thead class="table-light text-uppercase small">
                        <tr id="expertTableHeadersRow">
                            <!-- Headers will be injected dynamically -->
                        </tr>
                    </thead>
                    <tbody id="drawerTasksBody">
                        <!-- Rows injected dynamically -->
                    </tbody>
                </table>
            </div>

            <div id="drawerEmptyState" class="p-5 text-center d-none text-muted">
                <i class="bi bi-calendar-x fs-2"></i>
                <p class="mt-2 small">No tasks assigned to this expert matching the filter.</p>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT TASK FORM (Copied for inline edits) -->
<!-- ========================================================================= -->
<div class="modal fade" id="taskFormModal" tabindex="-1" aria-labelledby="taskFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="taskForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="taskFormModalLabel">Edit Task</h5>
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

                        <!-- Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-medium">Filing Amount (INR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="formAmount" name="amount" required min="0">
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

                        <!-- Created Date (Override) -->
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
                            <textarea class="form-control" id="formRemarks" name="remarks" rows="2" placeholder="Remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitForm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VIEW TASK DETAILS (Copied for inline views) -->
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

<!-- Experts Page Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Header title setup
    document.getElementById('headerTitle').textContent = 'Task Experts';
    document.getElementById('headerSubtitle').textContent = 'Filing workloads and performance metrics';

    const headerActions = document.getElementById('headerActions');
    if (headerActions) {
        headerActions.innerHTML = `
            <button class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1 hover-lift" id="btnExportExcel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </button>
        `;
        document.getElementById('btnExportExcel').addEventListener('click', () => {
            if (!selectedExpert) {
                Swal.fire('Info', 'Please select a task expert first.', 'info');
                return;
            }
            const params = new URLSearchParams({
                employee: selectedExpert
            });
            if (selectedSubFilter === 'Pinnacle_Vishnu') {
                params.set('client', 'Pinnacle_Vishnu');
            } else if (selectedSubFilter === 'Clear_Tax') {
                params.set('client', 'Clear Tax');
            }
            window.open(`api/reports.php?action=excel&${params.toString()}`, '_blank');
        });
    }

    // Modal Objects
    const taskFormModal = new bootstrap.Modal(document.getElementById('taskFormModal'));
    const taskViewModal = new bootstrap.Modal(document.getElementById('taskViewModal'));

    // State Variables
    let selectedExpert = '';
    let selectedSubFilter = 'All'; // 'All', 'Pinnacle_Vishnu', 'Clear_Tax'
    let selectedStatusFilter = 'All'; // 'All', 'Active', 'Completed'
    let tasksCache = [];
    let activeViewedTask = null;

    fetchExpertsMetrics();

    // Reset Form client fields handler (locked prices on Clear Tax)
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
            formAmount.value = PLAN_PRICING[planVal] || 0;
            formAmount.setAttribute('readonly', 'true');
            formAmount.classList.add('bg-light');
            formOrderIdContainer.classList.remove('d-none');
            formPanContainer.classList.add('d-none');
            formPlanContainer.classList.remove('d-none');
            formPlan.setAttribute('required', 'true');
            // Show Phone & Email
            formPhoneContainer.classList.remove('d-none');
            formPhone.setAttribute('required', 'true');
            formEmailContainer.classList.remove('d-none');
            formEmail.setAttribute('required', 'true');
        } else {
            formAmount.removeAttribute('readonly');
            formAmount.classList.remove('bg-light');
            formOrderIdContainer.classList.add('d-none');
            formPanContainer.classList.remove('d-none');
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

    // Form submission
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

        try {
            document.getElementById('btnSubmitForm').setAttribute('disabled', 'true');
            const res = await fetch(`api/tasks.php?id=${taskId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Task updated successfully',
                    showConfirmButton: false,
                    timer: 3000
                });
                taskFormModal.hide();
                fetchExpertsMetrics(); // Refresh expert workloads
                if (selectedExpert) {
                    loadExpertTasks(selectedExpert); // Refresh tasks table
                }
            } else {
                throw new Error(result.message || 'Failed to update task');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        } finally {
            document.getElementById('btnSubmitForm').removeAttribute('disabled');
        }
    });

    // Sub-Filter Tabs Click handler
    const subFilterButtons = document.querySelectorAll('.expert-sub-filter');
    subFilterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            subFilterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedSubFilter = btn.getAttribute('data-subfilter');
            
            fetchExpertsMetrics();

            if (selectedExpert) {
                renderTasksTable();
            }
        });
    });

    // Status-Filter Tabs Click handler
    const statusFilterButtons = document.querySelectorAll('.expert-status-filter');
    statusFilterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            statusFilterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedStatusFilter = btn.getAttribute('data-statusfilter');
            
            if (selectedExpert) {
                renderTasksTable();
            }
        });
    });

    // 1. Fetch Experts Workload Metrics
    async function fetchExpertsMetrics() {
        const loader = document.getElementById('expertsLoader');
        const grid = document.getElementById('expertsGridContainer');

        loader.classList.remove('d-none');
        grid.classList.add('d-none');

        try {
            const res = await fetch(`api/employees.php?action=stats&subFilter=${selectedSubFilter}`);
            const data = await res.json();

            let html = '';
            data.forEach(expert => {
                html += `
                    <div class="col-12 col-md-3">
                        <div class="card h-100 border shadow-sm hover-lift p-3 cursor-pointer expert-card" id="card-${expert.name}" onclick="window.selectExpert('${expert.name}')">
                            <h4 class="h6 fw-bold mb-3 text-foreground">${expert.name}</h4>
                            <div class="list-group list-group-flush small">
                                <div class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-0">
                                    <span class="text-muted"><i class="bi bi-clock me-1 text-warning"></i> Pending & Stuck</span>
                                    <strong class="text-warning">${expert.pending + expert.stuck}</strong>
                                </div>
                                <div class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-0">
                                    <span class="text-muted"><i class="bi bi-check-circle me-1 text-success"></i> Completed</span>
                                    <strong class="text-success">${expert.completed}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;
            grid.classList.remove('d-none');
            loader.classList.add('d-none');

            // Select first expert by default to match screenshot outline and show the table
            if (data.length > 0) {
                if (!selectedExpert) {
                    window.selectExpert(data[0].name);
                } else {
                    // Restore active outline border
                    const activeCard = document.getElementById(`card-${selectedExpert}`);
                    if (activeCard) {
                        activeCard.classList.add('border-primary', 'border-2');
                    }
                }
            }
        } catch (err) {
            console.error('Failed loading employees stats', err);
            loader.classList.add('d-none');
            Swal.fire('Error', 'Failed to load experts workloads', 'error');
        }
    }

    // 2. Select Expert Handler
    window.selectExpert = (name) => {
        selectedExpert = name;
        
        // Remove border outline from all cards
        document.querySelectorAll('.expert-card').forEach(card => {
            card.classList.remove('border-primary', 'border-2');
        });

        // Add border outline to active card
        const card = document.getElementById(`card-${name}`);
        if (card) {
            card.classList.add('border-primary', 'border-2');
        }

        // Set Table Header
        document.getElementById('expertTasksTitle').textContent = `All Assigned Tasks — ${name}`;

        // Load tasks list for selected expert
        loadExpertTasks(name);
    };

    // 3. Load expert tasks via API
    async function loadExpertTasks(name) {
        const tableContainer = document.getElementById('expertTasksContainer');
        const drawerLoader = document.getElementById('drawerLoader');
        const drawerContent = document.getElementById('drawerContent');

        tableContainer.classList.remove('d-none');
        drawerLoader.classList.remove('d-none');
        drawerContent.classList.add('d-none');

        try {
            const res = await fetch(`api/employees.php?employee=${name}`);
            const data = await res.json();
            tasksCache = data.tasks;

            renderTasksTable();

            drawerLoader.classList.add('d-none');
            drawerContent.classList.remove('d-none');
        } catch (err) {
            console.error('Failed loading tasks list for expert', err);
            drawerLoader.classList.add('d-none');
            Swal.fire('Error', 'Failed to load expert tasks details', 'error');
        }
    }

    // 4. Render tasks table with sub-filters applied
    function renderTasksTable() {
        const tbody = document.getElementById('drawerTasksBody');
        const emptyState = document.getElementById('drawerEmptyState');
        const table = document.getElementById('expertTasksTable');

        tbody.innerHTML = '';
        emptyState.classList.add('d-none');
        table.classList.remove('d-none');

        // Apply Sub-Filter (Client)
        let filteredTasks = tasksCache;
        if (selectedSubFilter === 'Pinnacle_Vishnu') {
            filteredTasks = tasksCache.filter(t => t.client === 'Pinnacle' || t.client === 'Vishnu');
        } else if (selectedSubFilter === 'Clear_Tax') {
            filteredTasks = tasksCache.filter(t => t.client === 'Clear Tax');
        }

        // Apply Status Filter
        if (selectedStatusFilter === 'Active') {
            filteredTasks = filteredTasks.filter(t => t.status === 'Pending' || t.status === 'Stuck');
        } else if (selectedStatusFilter === 'Completed') {
            filteredTasks = filteredTasks.filter(t => t.status === 'Completed');
        }

        if (filteredTasks.length === 0) {
            emptyState.classList.remove('d-none');
            table.classList.add('d-none');
            return;
        }

        // Dynamically build headers
        const trHeaders = document.getElementById('expertTableHeadersRow');
        let headerHtml = '';
        if (selectedSubFilter === 'Pinnacle_Vishnu') {
            headerHtml += `<th class="text-nowrap" style="width: 60px;">Sr No</th>`;
        } else {
            headerHtml += `<th>Order ID</th>`;
        }
        headerHtml += `
            <th>Client</th>
            <th>Customer Name</th>
            <th>PAN</th>
        `;
        if (selectedSubFilter !== 'Pinnacle_Vishnu') {
            headerHtml += `
                <th>Phone</th>
                <th>Email</th>
                <th>Plan</th>
            `;
        }
        headerHtml += `
            <th>Amount</th>
            <th>Status</th>
            <th>Remarks</th>
            <th class="text-end" style="width: 100px;">Actions</th>
        `;
        trHeaders.innerHTML = headerHtml;

        let html = '';
        filteredTasks.forEach((task, index) => {
            const statusClass = `badge-status-${task.status.toLowerCase()}`;
            const rowClass = `task-row-${task.status}`;

            html += `<tr class="${rowClass} transition-all">`;
            
            // Order ID or Sr No
            if (selectedSubFilter === 'Pinnacle_Vishnu') {
                html += `<td class="fw-bold">${index + 1}</td>`;
            } else {
                if (task.client === 'Pinnacle' || task.client === 'Vishnu') {
                    html += `<td>—</td>`;
                } else {
                    html += `<td class="text-nowrap font-monospace text-muted small">${task.orderId}</td>`;
                }
            }

            // Client, Customer Name, PAN
            html += `<td class="text-nowrap fw-semibold">${task.client}</td>`;
            html += `<td class="text-nowrap fw-bold">${task.customerName}</td>`;
            html += `<td class="text-nowrap font-monospace">${task.pan || '—'}</td>`;
            
            // Phone, Email & Plan (hidden for Pinnacle/Vishnu)
            if (selectedSubFilter !== 'Pinnacle_Vishnu') {
                html += `<td class="text-nowrap">${task.phone}</td>`;
                html += `<td class="text-nowrap small text-muted">${task.email}</td>`;
                html += `<td class="text-nowrap">${task.plan || '—'}</td>`;
            }

            html += `<td class="text-nowrap fw-bold">${formatCurrency(task.amount)}</td>`;
            html += `<td class="text-nowrap"><span class="badge-status ${statusClass}">${task.status}</span></td>`;
            html += `<td class="text-nowrap text-truncate small text-muted" style="max-width: 140px;" title="${task.remarks || ''}">${task.remarks || '—'}</td>`;

            // Actions dropdown menu
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

            html += '</tr>';
        });

        tbody.innerHTML = html;
    }

    // Modal view bindings
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
        if (!task) return;

        const form = document.getElementById('taskForm');
        form.reset();
        form.classList.remove('was-validated');

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
        
        if (task.createdAt) {
            const date = new Date(task.createdAt);
            const isoStr = date.toISOString().slice(0, 16);
            document.getElementById('formCreatedAt').value = isoStr;
        }

        adjustFormFields();
        taskFormModal.show();
    };

    // Detailed Modal Actions
    document.getElementById('btnViewEdit').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            setTimeout(() => {
                window.editTaskById(activeViewedTask.id);
            }, 300);
        }
    });

    document.getElementById('btnViewDelete').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            setTimeout(() => {
                window.deleteTaskById(activeViewedTask.id);
            }, 300);
        }
    });

    document.getElementById('btnViewComplete').addEventListener('click', () => {
        if (activeViewedTask) {
            taskViewModal.hide();
            setTimeout(() => {
                window.completeTaskById(activeViewedTask.id);
            }, 300);
        }
    });

    window.deleteTaskById = (id) => {
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
                        fetchExpertsMetrics();
                        if (selectedExpert) {
                            loadExpertTasks(selectedExpert);
                        }
                    } else {
                        const err = await res.json();
                        throw new Error(err.message || 'Failed to delete task');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            }
        });
    };

    window.completeTaskById = async (id) => {
        try {
            const res = await fetch(`api/tasks.php?id=${id}`, {
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
                fetchExpertsMetrics();
                if (selectedExpert) {
                    loadExpertTasks(selectedExpert);
                }
            } else {
                const err = await res.json();
                throw new Error(err.message || 'Failed to update task status');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    };

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
                fetchExpertsMetrics();
                if (selectedExpert) {
                    loadExpertTasks(selectedExpert);
                }
                window.open(`api/invoices.php?action=download&invoiceNumber=${invoice.invoiceNumber}`, '_blank');
            } else {
                throw new Error(invoice.message || 'Failed to generate invoice');
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    };
});
</script>

<?php include 'includes/footer.php'; ?>
