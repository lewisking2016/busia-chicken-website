<?php
/**
 * Sub-Module: Alert Center
 * Notifications for low stock levels and price fluctuations.
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
$page_title = 'Stock Alert Center';
include __DIR__ . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="/Frontend/assets/css/admin-stock.css">

<div class="admin-stock-wrapper">
    <div class="dashboard-hero-card">
        <h1>Stock Alert Center</h1>
        <p>Stay informed about inventory shortages, market price changes, and production bottlenecks.</p>
    </div>

    <?php include __DIR__ . '/includes/stock_nav.php'; ?>

    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0;">Active Notifications</h3>
            <button class="btn btn-trans btn-sm" onclick="resolveAlert(0)">Mark All as Resolved</button>
        </div>

        <div id="alerts-container">
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <div class="spinner"></div>
                <p>Checking system health...</p>
            </div>
        </div>
    </div>
</div>

<script>
async function loadAlerts() {
    try {
        const response = await fetch('/Backend/api/admin_stock.php?action=get_dashboard');
        const result = await response.json();
        
        const container = document.getElementById('alerts-container');
        
        if (!result.success || result.data.alerts.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 60px 40px; background: #f8fafc; border-radius: 8px;">
                    <i data-lucide="check-circle-2" style="width: 64px; height: 64px; color: #16a34a; margin-bottom: 16px;"></i>
                    <h3>System Healthy</h3>
                    <p>No critical stock alerts or price fluctuations detected at this time.</p>
                </div>
            `;
        } else {
            container.innerHTML = result.data.alerts.map(a => `
                <div class="alert-item ${a.alert_type}">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="${getAlertIcon(a.alert_type)}"></i>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 1rem; text-transform: capitalize;">${a.alert_type.replace('_', ' ')}</strong>
                            <p style="margin: 0; color: #475569; font-size: 0.9rem;">${a.message}</p>
                            <span style="font-size: 0.75rem; color: #94a3b8;">${new Date(a.created_at).toLocaleString()}</span>
                        </div>
                    </div>
                    <button class="btn btn-trans btn-sm" onclick="resolveAlert(${a.id})">Resolve</button>
                </div>
            `).join('');
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (err) { console.error(err); }
}

async function resolveAlert(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'resolve_alert');
        formData.append('id', id);
        formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');

        const response = await fetch('/Backend/api/admin_stock.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            loadAlerts();
        }
    } catch (err) { console.error(err); }
}

function getAlertIcon(type) {
    switch(type) {
        case 'low_stock': return 'alert-triangle';
        case 'price_fluctuation': return 'trending-up';
        case 'bottleneck': return 'activity';
        default: return 'bell';
    }
}

document.addEventListener('DOMContentLoaded', loadAlerts);
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
