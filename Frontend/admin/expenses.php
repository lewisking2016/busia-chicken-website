<?php
/**
 * Sub-Module: Expense Manager (Bookkeeping)
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$path_prefix = '../../';
$page_title = 'Expense Logger';
include __DIR__ . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-stock.css?v=1.3">

<div class="admin-stock-wrapper">
    <div class="dashboard-hero-card" style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 32px; border-radius: 8px; margin-bottom: 32px; color: #ffffff;">
        <h1 style="color: #ffffff; margin: 0 0 8px 0;">Expense Logger</h1>
        <p style="color: rgba(255, 255, 255, 0.9); margin: 0;">Log business expenditures, select categories, and manage outgoing cash flow.</p>
    </div>

    <!-- Expenses Listing Card -->
    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Outflow Registry</h3>
            <button class="btn btn-primary btn-sm" onclick="openExpenseModal()">
                <i data-lucide="plus"></i> Log Expense
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expenses-body">
                    <tr><td colspan="5" style="text-align:center; padding: 20px;">Loading expenses...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Expense Modal -->
<div id="expense-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #ffffff; padding: 32px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="expense-modal-title" style="margin-bottom: 24px;">Log Expense</h3>
        <form id="expense-form">
            <input type="hidden" name="id" id="expense-id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Expense Category</label>
                <select name="category" id="expense-category" class="form-control" required style="width:100%; height:42px;">
                    <option value="">Choose Category...</option>
                    <option value="Feed Purchase">Feed Purchase</option>
                    <option value="Chicks / Livestock">Chicks / Livestock</option>
                    <option value="Vaccines & Drugs">Vaccines & Drugs</option>
                    <option value="Farm Labor / Wages">Farm Labor / Wages</option>
                    <option value="Utilities (Water/Power)">Utilities (Water/Power)</option>
                    <option value="Packaging & Bags">Packaging & Bags</option>
                    <option value="Transportation / Fuel">Transportation / Fuel</option>
                    <option value="Equipment Repairs">Equipment Repairs</option>
                    <option value="Other Operations">Other Operations</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Amount (KES)</label>
                <input type="number" name="amount" id="expense-amount" class="form-control" step="0.01" min="0.01" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Transaction Date</label>
                <input type="date" name="transaction_date" id="expense-date" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Description / Remarks</label>
                <textarea name="description" id="expense-description" class="form-control" style="height: 60px; font-family:inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="button" class="btn btn-trans" style="flex: 1;" onclick="closeExpenseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Log</button>
            </div>
        </form>
    </div>
</div>

<script>
window.expenses_list = [];

async function loadExpenses() {
    try {
        const response = await fetch('/Backend/api/admin_poultry.php?action=get_expenses');
        const result = await response.json();
        if (result.success) {
            window.expenses_list = result.data;
            renderExpenses();
        }
    } catch(e) {
        console.error("Error loading expenses:", e);
    }
}

function renderExpenses() {
    const tbody = document.getElementById('expenses-body');
    if (!window.expenses_list.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">No business expenses logged yet. Click "Log Expense" to start.</td></tr>';
        return;
    }

    tbody.innerHTML = window.expenses_list.map(ex => `
        <tr>
            <td>${ex.transaction_date}</td>
            <td><span class="badge-pill badge-pill-warning">${ex.category}</span></td>
            <td><strong>KES ${Number(ex.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</strong></td>
            <td>${ex.description || '-'}</td>
            <td>
                <div style="display:flex; gap: 8px;">
                    <button class="btn btn-trans btn-sm" onclick="editExpense(${ex.id})">Edit</button>
                    <button class="btn btn-trans btn-sm" style="color:#dc2626;" onclick="deleteExpense(${ex.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openExpenseModal() {
    document.getElementById('expense-modal-title').textContent = 'Log Expense';
    document.getElementById('expense-form').reset();
    document.getElementById('expense-id').value = '';
    document.getElementById('expense-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('expense-modal').style.display = 'flex';
}

function closeExpenseModal() {
    document.getElementById('expense-modal').style.display = 'none';
}

function editExpense(id) {
    const ex = window.expenses_list.find(item => item.id == id);
    if (!ex) return;

    document.getElementById('expense-modal-title').textContent = 'Edit Expense Outlay';
    document.getElementById('expense-id').value = ex.id;
    document.getElementById('expense-category').value = ex.category;
    document.getElementById('expense-amount').value = ex.amount;
    document.getElementById('expense-date').value = ex.transaction_date;
    document.getElementById('expense-description').value = ex.description;

    document.getElementById('expense-modal').style.display = 'flex';
}

async function deleteExpense(id) {
    if (!confirm('Are you sure you want to delete this expense record?')) return;
    try {
        const formData = new FormData();
        formData.append('id', id.toString());
        formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');

        const response = await fetch('/Backend/api/admin_poultry.php?action=delete_expense', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            loadExpenses();
        } else {
            alert(result.message);
        }
    } catch(e) { console.error(e); }
}

document.getElementById('expense-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');
    try {
        const response = await fetch('/Backend/api/admin_poultry.php?action=save_expense', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            closeExpenseModal();
            loadExpenses();
        } else {
            alert(result.message);
        }
    } catch(e) { console.error(e); }
});

document.addEventListener('DOMContentLoaded', () => {
    loadExpenses();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
