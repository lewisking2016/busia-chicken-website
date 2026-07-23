<?php
/**
 * Sub-Module: Live Stock Dashboard
 * Real-time tracking of raw materials and finished bags.
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager', 'stock_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$path_prefix = '../../';
$page_title = 'Live Stock Dashboard';
include __DIR__ . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="/Frontend/assets/css/admin-stock.css">

<div class="admin-stock-wrapper">
    <div class="dashboard-hero-card" style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 32px; border-radius: 8px; margin-bottom: 32px; color: #ffffff;">
        <h1 style="color: #ffffff; margin: 0 0 8px 0;">Live Stock Dashboard</h1>
        <p style="color: rgba(255, 255, 255, 0.9); margin: 0;">Real-time tracking of raw materials in tons and finished bags in stock, including total inventory valuation.</p>
    </div>

    <?php include __DIR__ . '/includes/stock_nav.php'; ?>

    <div class="kpi-summary-row">
        <div class="stat-card">
            <div class="stat-card-info">
                <small>Raw Material Value</small>
                <strong id="val-raw-total">KES 0</strong>
            </div>
            <div class="stat-card-icon"><i data-lucide="database"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <small>Finished Stock Value</small>
                <strong id="val-finished-total">KES 0</strong>
            </div>
            <div class="stat-card-icon info"><i data-lucide="package"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <small>Total Inventory Assets</small>
                <strong id="val-total-assets">KES 0</strong>
            </div>
            <div class="stat-card-icon accent"><i data-lucide="trending-up"></i></div>
        </div>
    </div>

    <div class="stock-grid">
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Raw Materials (Tons)</h3>
                <button class="btn btn-primary btn-sm" onclick="openRMModal()">
                    <i data-lucide="plus"></i> Add Material
                </button>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Stock (Tons)</th>
                            <th>Current Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="raw-materials-body">
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Syncing raw material data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Finished Feed Stock (Bags)</h3>
                <span class="badge-pill badge-pill-success">Live Inventory</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Feed Type</th>
                            <th>Stock (Bags)</th>
                            <th>Retail Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="finished-stock-body">
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">Syncing inventory data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Raw Material Modal -->
<div id="rm-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #ffffff; padding: 32px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="rm-modal-title" style="margin-bottom: 24px;">Add Raw Material</h3>
        <form id="rm-form">
            <input type="hidden" name="id" id="rm-id">
            <div class="form-group">
                <label class="form-label">Material Name</label>
                <input type="text" name="name" id="rm-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price per Ton (KES)</label>
                <input type="number" name="current_price_per_ton" id="rm-price" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Current Stock (Tons)</label>
                <input type="number" name="stock_tons" id="rm-stock" class="form-control" step="0.001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Min Stock Level (Alert Threshold)</label>
                <input type="number" name="min_stock_level" id="rm-min" class="form-control" step="0.001" value="1.000">
            </div>
            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="button" class="btn btn-trans" style="flex: 1;" onclick="closeRMModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Material</button>
            </div>
        </form>
    </div>
</div>

<script>
async function loadDashboardData() {
    try {
        const response = await fetch('/Backend/api/admin_stock.php?action=get_dashboard');
        const result = await response.json();
        if (!result.success) return;

        window.raw_materials_data = result.data.raw_materials;
        const { raw_materials, finished_products, summary } = result.data;

        // Raw Materials
        document.getElementById('raw-materials-body').innerHTML = raw_materials.map(m => `
            <tr>
                <td><strong>${m.name}</strong></td>
                <td>${m.stock_tons} Tons</td>
                <td>KES ${Number(m.current_price_per_ton).toLocaleString()}</td>
                <td>KES ${Number(m.total_value).toLocaleString()}</td>
                <td>
                    <span class="badge-pill ${m.stock_tons <= m.min_stock_level ? 'badge-pill-danger' : 'badge-pill-success'}">
                        ${m.stock_tons <= m.min_stock_level ? 'Low Stock' : 'Healthy'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-trans btn-sm" onclick="editRM(${m.id})">Edit</button>
                </td>
            </tr>
        `).join('');

        // Finished Products
        document.getElementById('finished-stock-body').innerHTML = finished_products.map(p => `
            <tr>
                <td><strong>${p.name}</strong></td>
                <td>${p.stock_quantity} Bags</td>
                <td>KES ${Number(p.price).toLocaleString()}</td>
                <td>KES ${(Number(p.price) * p.stock_quantity).toLocaleString()}</td>
                <td>
                    <span class="badge-pill ${p.stock_quantity < 20 ? 'badge-pill-warning' : 'badge-pill-success'}">
                        ${p.stock_quantity < 20 ? 'Restock Soon' : 'In Stock'}
                    </span>
                </td>
            </tr>
        `).join('');

        // Summary
        document.getElementById('val-raw-total').textContent = `KES ${Number(summary.raw_value).toLocaleString()}`;
        document.getElementById('val-finished-total').textContent = `KES ${Number(summary.finished_value).toLocaleString()}`;
        document.getElementById('val-total-assets').textContent = `KES ${Number(summary.total_value).toLocaleString()}`;

        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (err) {
        console.error('Failed to load dashboard data', err);
    }
}

function openRMModal() {
    document.getElementById('rm-modal-title').textContent = 'Add Raw Material';
    document.getElementById('rm-form').reset();
    document.getElementById('rm-id').value = '';
    document.getElementById('rm-modal').style.display = 'flex';
}

function closeRMModal() {
    document.getElementById('rm-modal').style.display = 'none';
}

function editRM(id) {
    const m = window.raw_materials_data.find(item => item.id == id);
    if (!m) return;
    
    document.getElementById('rm-modal-title').textContent = 'Edit Raw Material';
    document.getElementById('rm-id').value = m.id;
    document.getElementById('rm-name').value = m.name;
    document.getElementById('rm-price').value = m.current_price_per_ton;
    document.getElementById('rm-stock').value = m.stock_tons;
    document.getElementById('rm-min').value = m.min_stock_level;
    document.getElementById('rm-modal').style.display = 'flex';
}

document.getElementById('rm-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/Backend/api/admin_stock.php?action=save_raw_material', {
            method: 'POST',
            body: (() => {
                formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');
                return formData;
            })()
        });
        const result = await response.json();
        
        if (result.success) {
            closeRMModal();
            loadDashboardData();
        } else {
            alert(result.message || 'Failed to save material');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while saving.');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
    // Auto-refresh every 30 seconds for a "Live" feel
    setInterval(loadDashboardData, 30000);
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
