<?php
/**
 * Admin Dashboard with Analytics
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

// Admin access check
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager', 'stock_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$path_prefix = '../../';
$page_title = 'Admin Dashboard';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-dashboard-wrapper" style="margin: 0; padding: 0;">
    <style>
        .dashboard-hero-card {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-light) 100%);
            color: #ffffff !important;
            border-radius: 4px;
            padding: 32px;
            margin-bottom: 24px;
            border: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(27, 94, 32, 0.15);
        }

        .dashboard-hero-card::after {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,193,7,0.15) 0%, transparent 80%);
            border-radius: 50%;
        }

        .dashboard-hero-card h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            margin: 0 0 12px 0;
            font-weight: 700;
            color: #ffffff !important;
        }

        .dashboard-hero-card p {
            margin: 0 0 24px 0;
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 1rem;
            line-height: 1.6;
            max-width: 600px;
        }

        .dashboard-hero-card .btn {
            border-radius: 4px;
            padding: 10px 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .dashboard-hero-card .btn-white {
            background: #ffffff;
            color: var(--admin-primary);
        }

        .dashboard-hero-card .btn-white:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .dashboard-hero-card .btn-trans {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.25);
        }

        .dashboard-hero-card .btn-trans:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Grid layouts */
        .dashboard-kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .dashboard-main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        @media (max-width: 1024px) {
            .dashboard-kpi-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .dashboard-main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dashboard-kpi-row {
                grid-template-columns: 1fr;
            }
        }

        .mini-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid var(--admin-border);
            transition: background 0.2s ease;
        }

        .mini-list-item:last-child {
            border-bottom: none;
        }

        .mini-list-item:hover {
            background: rgba(248, 250, 252, 0.8);
        }

        .mini-list-item .item-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mini-list-item .item-details strong {
            color: var(--admin-text-heading);
            font-size: 0.95rem;
        }

        .mini-list-item .item-details span {
            color: #64748b;
            font-size: 0.8rem;
        }

        .chart-box {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>

    <!-- Redesigned Welcome Banner -->
    <div class="dashboard-hero-card">
        <h1>Operations Cockpit</h1>
        <p>Manage products, view analytics reports, monitor system health status, and handle orders all in a clean, high-performance workspace.</p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a class="btn btn-white" href="orders.php">
                <i data-lucide="shopping-cart" style="width: 18px; height: 18px;"></i>
                <span>Review Orders</span>
            </a>
            <a class="btn btn-trans" href="products.php">
                <i data-lucide="package" style="width: 18px; height: 18px;"></i>
                <span>Manage Products</span>
            </a>
            <a class="btn btn-trans" href="reports.php">
                <i data-lucide="bar-chart" style="width: 18px; height: 18px;"></i>
                <span>Analytics Report</span>
            </a>
        </div>
    </div>

    <!-- Redesigned KPI cards -->
    <div class="dashboard-kpi-row">
        <div class="stat-card">
            <div class="stat-card-info">
                <small>Total Revenue</small>
                <strong id="kpi-sales">KES 0</strong>
                <span style="font-size: 0.8rem; color: #16a34a; font-weight: 600; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i> +18.5% this month
                </span>
            </div>
            <div class="stat-card-icon">
                <i data-lucide="dollar-sign"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <small>Orders Completed</small>
                <strong id="kpi-orders">0</strong>
                <span style="font-size: 0.8rem; color: #16a34a; font-weight: 600; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="check" style="width: 14px; height: 14px;"></i> +9.1% conversion
                </span>
            </div>
            <div class="stat-card-icon info">
                <i data-lucide="shopping-bag"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <small>Avg. Order Value</small>
                <strong id="kpi-avg">KES 0</strong>
                <span style="font-size: 0.8rem; color: #16a34a; font-weight: 600; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i> +5.2% growth
                </span>
            </div>
            <div class="stat-card-icon accent">
                <i data-lucide="pie-chart"></i>
            </div>
        </div>

        <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='stock_alerts.php'">
            <div class="stat-card-info">
                <small>Inventory Alerts</small>
                <strong id="kpi-alerts-summary">0</strong>
                <span style="font-size: 0.8rem; color: #dc2626; font-weight: 600; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i> Action required
                </span>
            </div>
            <div class="stat-card-icon accent" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;">
                <i data-lucide="alert-circle"></i>
            </div>
        </div>
    </div>

    <!-- Main Grid containing Charts and Lists -->
    <div class="dashboard-main-grid">
        <!-- Revenue Chart Card -->
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading);">Revenue Trend</h3>
                <span class="badge-pill badge-pill-success">Live Sync</span>
            </div>
            <div class="chart-box">
                <canvas id="chart-sales"></canvas>
            </div>
        </div>
        <!-- System Status & Stocks Alerts -->
        <div class="admin-card" style="display: flex; flex-direction: column; gap: 20px;">
            <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading);">System Overview</h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: rgba(27, 94, 32, 0.04); border-radius: 4px; border: 1px solid rgba(27, 94, 32, 0.08);">
                    <div style="width: 10px; height: 10px; background: #16a34a; border-radius: 50%;"></div>
                    <div style="flex-grow: 1;">
                        <h5 style="margin: 0; font-size: 0.9rem; color: var(--admin-text-heading);">Platform Status</h5>
                        <p style="margin: 0; font-size: 0.75rem; color: #64748b;">All systems operational</p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: rgba(255, 193, 7, 0.06); border-radius: 4px; border: 1px solid rgba(255, 193, 7, 0.1); cursor: pointer;" onclick="window.location.href='stock_alerts.php'">
                    <i data-lucide="bell" style="width: 16px; height: 16px; color: #d97706;"></i>
                    <div style="flex-grow: 1;">
                        <h5 style="margin: 0; font-size: 0.9rem; color: var(--admin-text-heading);"><span id="kpi-alerts">0</span> Stock Alerts</h5>
                        <p style="margin: 0; font-size: 0.75rem; color: #64748b;">Review item counts</p>
                    </div>
                </div>
            </div>

            <!-- Top Products Widget -->
            <div style="margin-top: 10px;">
                <h4 style="margin: 0 0 14px 0; font-family: 'Outfit', sans-serif; font-size: 1rem; color: var(--admin-text-heading);">Top Moving Products</h4>
                <div style="border: 1px solid var(--admin-border); border-radius: 4px; overflow: hidden;" id="top-products">
                    <p style="padding: 16px; text-align: center; color: #64748b; margin: 0; font-size: 0.9rem;">Loading products...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-main-grid" style="grid-template-columns: 1fr 1fr;">
        <!-- Order Volume Card -->
        <div class="admin-card">
            <h3 style="margin: 0 0 24px 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading);">Order Volumes</h3>
            <div class="chart-box" style="height: 250px;">
                <canvas id="chart-orders"></canvas>
            </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="admin-card">
            <h3 style="margin: 0 0 20px 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading);">System Audit Log</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Action / Log Details</th>
                            <th style="text-align: right;">Time</th>
                        </tr>
                    </thead>
                    <tbody id="recent-activity">
                        <tr>
                            <td colspan="2" style="text-align: center; color: #64748b; padding: 20px;">Fetching logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function loadDashboard() {
    const response = await fetch('/Backend/api/admin_analytics.php');
    const data = await response.json();

    if (!data.success) {
        console.error('Analytics fetch failed', data);
        return;
    }

    const sales = (data.data.sales || []).map((item) => ({ date: item.day, value: Number(item.total) }));
    const orders = (data.data.orders || []).map((item) => ({ date: item.day, value: Number(item.cnt) }));
    const topProducts = data.data.top_products || [];

    const labels = sales.map((item) => item.date);

    new Chart(document.getElementById('chart-sales'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: sales.map((item) => item.value),
                borderColor: '#1B5E20',
                backgroundColor: 'rgba(27, 94, 32, 0.08)',
                fill: true,
                tension: 0.32,
                pointRadius: 3,
                pointBackgroundColor: '#FFC107',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b' } },
                y: { grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { color: '#64748b' } },
            },
        },
    });

    new Chart(document.getElementById('chart-orders'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Orders',
                data: orders.map((item) => item.value),
                backgroundColor: '#FFC107',
                borderRadius: 0,
                maxBarThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b' } },
                y: { grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { color: '#64748b', precision: 0 } },
            },
        },
    });

    document.getElementById('top-products').innerHTML = topProducts.length ? topProducts
        .slice(0, 5)
        .map((item) => `
            <div class="mini-list-item">
                <div class="item-details">
                    <strong>${item.name}</strong>
                    <span>${item.qty} units sold</span>
                </div>
                <strong style="color: var(--admin-primary);">${item.qty}</strong>
            </div>
        `)
        .join('') : '<p style="padding: 16px; text-align: center; color: #64748b; margin: 0; font-size: 0.9rem;">No product sales recorded yet.</p>';

    const recentOrders = data.data.recent_orders || [];

    document.getElementById('recent-activity').innerHTML = recentOrders.length ? recentOrders
        .map((order) => {
            const dateStr = new Date(order.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric'});
            const statusClass = order.status === 'completed' ? 'badge-pill-success' : (order.status === 'pending' ? 'badge-pill-warning' : 'badge-pill-danger');
            return `
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--admin-text-heading);">Order #${order.id} by ${order.first_name || 'Guest'} ${order.last_name || ''}</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                            <span>KES ${Number(order.total_amount).toLocaleString()}</span>
                            <span class="badge-pill ${statusClass}">${order.status}</span>
                        </div>
                    </td>
                    <td style="text-align: right; vertical-align: middle; color: #64748b; font-size: 0.85rem;">
                        ${dateStr}
                    </td>
                </tr>
            `;
        })
        .join('') : '<tr><td colspan="2" style="text-align: center; color: #64748b; padding: 20px;">No recent activities/orders found.</td></tr>';

    const revenue = sales.reduce((sum, item) => sum + item.value, 0);
    const orderCount = orders.reduce((sum, item) => sum + item.value, 0);
    const averageOrder = orderCount ? Math.round(revenue / orderCount) : 0;

    document.getElementById('kpi-sales').textContent = `KES ${revenue.toLocaleString()}`;
    document.getElementById('kpi-orders').textContent = orderCount.toLocaleString();
    document.getElementById('kpi-avg').textContent = `KES ${averageOrder.toLocaleString()}`;
    document.getElementById('kpi-alerts').textContent = data.data.alerts || 0;
    document.getElementById('kpi-alerts-summary').textContent = data.data.alerts || 0;
}

// Auto-refresh every 30 seconds for live feel
setInterval(loadDashboard, 30000);

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
