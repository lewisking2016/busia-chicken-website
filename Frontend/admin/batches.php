<?php
/**
 * Admin — Chicken Batches & Houses Module
 * Strictly manages live chickens:
 *   - Housing, flock lifecycle, age tracking
 *   - Daily logs: mortality, weights, egg production
 *   - Real-time survival rates & batch profitability
 *   - Direct one-click Live Birds Sales terminal link
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Chicken Batches & Houses - Admin';
include __DIR__ . '/includes/admin_header.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'batches';
$validTabs = ['batches', 'houses', 'records'];
if (!in_array($tab, $validTabs, true)) $tab = 'batches';

// Fetch batches with economics and mortality rollups
$batches = [];
$houses = [];
$totalActiveBirds = 0;
$totalInitialActive = 0;
$totalActiveBatches = 0;
$totalCapacity = 0;
$totalOccupied = 0;

if ($pdo) {
    try {
        $batches = $pdo->query("
            SELECT b.*, h.house_name,
                   (SELECT COALESCE(SUM(dbr.mortality), 0) FROM daily_batch_records dbr WHERE dbr.batch_id = b.id) AS total_mortality,
                   (SELECT COALESCE(SUM(dbr.sold_birds), 0) FROM daily_batch_records dbr WHERE dbr.batch_id = b.id) AS total_sold_birds,
                   (SELECT COALESCE(SUM(bs.total_amount), 0) FROM bulk_sales bs WHERE bs.batch_id = b.id) AS sales_revenue,
                   (SELECT COALESCE(SUM(bc.total_cost), 0) FROM batch_costs bc WHERE bc.batch_id = b.id) AS total_costs
            FROM batches b
            LEFT JOIN houses h ON h.id = b.house_id
            ORDER BY b.placement_date DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $houses = $pdo->query("
            SELECT h.*,
                   (SELECT COALESCE(SUM(b.current_birds), 0) FROM batches b WHERE b.house_id = h.id AND b.status = 'active') AS occupied_birds,
                   (SELECT COUNT(*) FROM batches b WHERE b.house_id = h.id AND b.status = 'active') AS active_batches_count
            FROM houses h
            ORDER BY h.house_name
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($batches as $b) {
            if ($b['status'] === 'active') {
                $totalActiveBatches++;
                $totalActiveBirds += (int)$b['current_birds'];
                $totalInitialActive += (int)$b['initial_birds'];
            }
        }

        foreach ($houses as $h) {
            $totalCapacity += (int)$h['capacity'];
            $totalOccupied += (int)$h['occupied_birds'];
        }
    } catch (Exception $e) {
        $batches = [];
        $houses = [];
    }
}

$survivalRate = $totalInitialActive > 0 ? round(($totalActiveBirds / $totalInitialActive) * 100, 1) : 100;
$capacityUsage = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;
?>

<style>
.batch-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 992px) {
    .batch-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
    .batch-kpi-grid { grid-template-columns: 1fr; }
}

.survival-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 6px;
}
.survival-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.3s ease;
}

.table-code-badge {
    background: #f1f5f9;
    color: #334155;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.8rem;
    font-family: monospace;
    border: 1px solid #e2e8f0;
    display: inline-block;
}
</style>

<!-- Page Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;display:flex;align-items:center;gap:10px;">
            <i data-lucide="bird" style="width:26px;height:26px;color:var(--admin-primary);"></i>
            Chicken Batches & Houses
        </h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Monitor live flock lifecycles, log daily mortality & weights, and sell live birds directly to the sales terminal.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="bulk_sales.php?item_type=chicken" class="btn btn-outline" style="background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;">
            <i data-lucide="shopping-cart" style="width:16px;height:16px;"></i> Sell Chickens
        </a>
        <button class="btn btn-primary" onclick="openBatchModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> New Batch
        </button>
    </div>
</div>

<!-- KPI Cards Banner -->
<div class="batch-kpi-grid">
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Active Live Birds</small>
            <strong style="font-size:1.6rem;color:#15803d;"><?= number_format($totalActiveBirds) ?></strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">Across <?= $totalActiveBatches ?> active flocks</span>
        </div>
        <div class="stat-card-icon" style="background:#dcfce7;color:#15803d;">
            <i data-lucide="bird"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Active Batches</small>
            <strong style="font-size:1.6rem;color:var(--admin-primary);"><?= $totalActiveBatches ?></strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">Broilers, Layers & Kienyeji</span>
        </div>
        <div class="stat-card-icon">
            <i data-lucide="layers"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Flock Survival Rate</small>
            <strong style="font-size:1.6rem;color:<?= $survivalRate >= 95 ? '#15803d' : ($survivalRate >= 90 ? '#d97706' : '#dc2626') ?>;">
                <?= $survivalRate ?>%
            </strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;"><?= number_format($totalInitialActive - $totalActiveBirds) ?> total losses</span>
        </div>
        <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
            <i data-lucide="activity"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Housing Occupancy</small>
            <strong style="font-size:1.6rem;color:#0284c7;"><?= $capacityUsage ?>%</strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;"><?= number_format($totalOccupied) ?> / <?= number_format($totalCapacity) ?> birds</span>
        </div>
        <div class="stat-card-icon info">
            <i data-lucide="home"></i>
        </div>
    </div>
</div>

<!-- Tab Navigation Bar -->
<div style="display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:12px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=batches" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='batches'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="layers" style="width:16px;height:16px;"></i> Active & Past Batches (<?= count($batches) ?>)
    </a>
    <a href="?tab=houses" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='houses'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="home" style="width:16px;height:16px;"></i> Chicken Houses (<?= count($houses) ?>)
    </a>
    <a href="?tab=records" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='records'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="clipboard-list" style="width:16px;height:16px;"></i> Daily Logs & Mortality
    </a>
</div>

<?php if ($tab === 'batches'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Flock Batches Registry</h3>
            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Directly launch live sales or log records for each batch.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/Backend/api/export.php?module=batches" class="btn btn-outline" style="font-size:0.85rem;"><i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV</a>
            <button class="btn btn-primary" onclick="openBatchModal()" style="font-size:0.85rem;"><i data-lucide="plus-circle" style="width:15px;height:15px;"></i> New Batch</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>House & Type</th>
                    <th>Age & Placed</th>
                    <th style="min-width:180px;">Birds (Live / Initial)</th>
                    <th>Cost vs Sales</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($batches)): ?>
                <tr><td colspan="7" style="text-align:center;padding:36px;color:#64748b;">No batches found. Click "New Batch" to record your first flock.</td></tr>
            <?php else: foreach ($batches as $b):
                $init = max(1, (int)$b['initial_birds']);
                $curr = (int)$b['current_birds'];
                $pct = round(($curr / $init) * 100, 1);
                $barColor = $pct >= 95 ? '#16a34a' : ($pct >= 90 ? '#eab308' : '#dc2626');

                // Age calculation
                $placedTime = strtotime($b['placement_date']);
                $now = time();
                $diffDays = max(0, (int)floor(($now - $placedTime) / 86400));
                $weeks = (int)floor($diffDays / 7);
                $extraDays = $diffDays % 7;

                $rev = (float)$b['sales_revenue'];
                $cst = (float)$b['total_costs'];
                $profit = $rev - $cst;
            ?>
                <tr>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:4px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span class="table-code-badge"><?= htmlspecialchars($b['batch_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong style="color:var(--admin-text-heading);font-size:0.95rem;"><?= htmlspecialchars($b['batch_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <?php if (!empty($b['breed'])): ?>
                                <small style="color:#64748b;display:inline-flex;align-items:center;gap:4px;">
                                    <i data-lucide="tag" style="width:12px;height:12px;"></i> <?= htmlspecialchars($b['breed'], ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:3px;">
                            <span style="font-weight:600;font-size:0.88rem;color:#334155;display:inline-flex;align-items:center;gap:4px;">
                                <i data-lucide="home" style="width:13px;height:13px;color:#64748b;"></i>
                                <?= htmlspecialchars($b['house_name'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="badge-pill badge-pill-info" style="align-self:flex-start;text-transform:capitalize;font-size:0.75rem;">
                                <?= htmlspecialchars(str_replace('_', ' ', $b['batch_type']), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:0.85rem;color:#334155;">
                            <strong>Wk <?= $weeks ?></strong> <span style="color:#64748b;">(<?= $diffDays ?> days)</span>
                        </div>
                        <small style="color:#64748b;"><?= htmlspecialchars($b['placement_date'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td>
                        <div style="display:flex;justify-content:space-between;font-size:0.86rem;font-weight:700;">
                            <span style="color:<?= $barColor ?>;"><?= number_format($curr) ?> live</span>
                            <span style="color:#64748b;"><?= number_format($init) ?> placed</span>
                        </div>
                        <div class="survival-bar">
                            <div class="survival-bar-fill" style="width:<?= min(100, $pct) ?>%;background:<?= $barColor ?>;"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:#64748b;margin-top:3px;">
                            <span><?= $pct ?>% survival</span>
                            <?php if ((int)$b['total_mortality'] > 0): ?>
                                <span style="color:#dc2626;">-<?= (int)$b['total_mortality'] ?> dead</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:0.82rem;">
                            <div style="color:#64748b;">Cost: KES <?= number_format($cst) ?></div>
                            <div style="color:#15803d;font-weight:600;">Sales: KES <?= number_format($rev) ?></div>
                            <?php if ($rev > 0): ?>
                                <span style="font-size:0.75rem;font-weight:700;color:<?= $profit >= 0 ? '#16a34a' : '#dc2626' ?>;">
                                    Net: <?= $profit >= 0 ? '+' : '' ?>KES <?= number_format($profit) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge-pill <?= $b['status']==='active'?'badge-pill-success':($b['status']==='sold'?'badge-pill-warning':'badge-pill-info') ?>">
                            <?= htmlspecialchars($b['status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div class="tbl-actions" style="justify-content:flex-end;gap:5px;">
                            <?php if ($curr > 0 && $b['status'] === 'active'): ?>
                                <a href="bulk_sales.php?item_type=chicken&batch_id=<?= (int)$b['id'] ?>" class="btn btn-outline btn-sm" style="background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;font-weight:700;" title="Sell Live Birds">
                                    <i data-lucide="shopping-cart" style="width:13px;height:13px;"></i> Sell
                                </a>
                            <?php endif; ?>
                            <a class="btn btn-trans btn-sm" href="?tab=records&batch_id=<?= (int)$b['id'] ?>" title="Daily Logs">
                                <i data-lucide="clipboard-list" style="width:13px;height:13px;"></i>
                            </a>
                            <a class="btn btn-trans btn-sm" href="feeding.php?tab=allocations&batch_id=<?= (int)$b['id'] ?>" title="Feed This Flock">
                                <i data-lucide="utensils" style="width:13px;height:13px;"></i>
                            </a>
                            <button class="btn btn-trans btn-sm" onclick="openBatchModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Batch">
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteBatch(<?= (int)$b['id'] ?>)" title="Delete Batch">
                                <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'houses'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Poultry Housing & Sheds</h3>
            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Manage physical housing capacity, ventilation, and flock allocations.</p>
        </div>
        <button class="btn btn-primary" onclick="openHouseModal()"><i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add House</button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>House Code</th>
                    <th>House Name</th>
                    <th>Location / Section</th>
                    <th>Max Capacity</th>
                    <th style="min-width:180px;">Occupancy</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($houses)): ?>
                <tr><td colspan="7" style="text-align:center;padding:32px;color:#64748b;">No houses recorded yet.</td></tr>
            <?php else: foreach ($houses as $h):
                $cap = max(1, (int)$h['capacity']);
                $occ = (int)$h['occupied_birds'];
                $occPct = round(($occ / $cap) * 100, 1);
                $occColor = $occPct > 100 ? '#dc2626' : ($occPct > 80 ? '#eab308' : '#16a34a');
            ?>
                <tr>
                    <td><span class="table-code-badge"><?= htmlspecialchars($h['house_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><strong><?= htmlspecialchars($h['house_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($h['location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><strong><?= number_format((int)$h['capacity']) ?></strong> birds</td>
                    <td>
                        <div style="display:flex;justify-content:space-between;font-size:0.84rem;font-weight:600;">
                            <span style="color:<?= $occColor ?>;"><?= number_format($occ) ?> birds</span>
                            <span style="color:#64748b;"><?= $occPct ?>% full</span>
                        </div>
                        <div class="survival-bar">
                            <div class="survival-bar-fill" style="width:<?= min(100, $occPct) ?>%;background:<?= $occColor ?>;"></div>
                        </div>
                        <small style="color:#64748b;font-size:0.75rem;"><?= (int)$h['active_batches_count'] ?> active flock(s)</small>
                    </td>
                    <td>
                        <span class="badge-pill <?= $h['is_active'] ? 'badge-pill-success' : 'badge-pill-info' ?>">
                            <?= $h['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <button class="btn btn-trans btn-sm" onclick='openHouseModal(<?= json_encode($h) ?>)'><i data-lucide="pencil" style="width:13px;height:13px;"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="deleteHouse(<?= (int)$h['id'] ?>)"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: /* records */ ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Daily Flock Logs</h3>
            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Daily mortality, bird weights, sales, and egg collections.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="/Backend/api/export.php?module=daily_records" class="btn btn-outline" id="rec-export" style="font-size:0.85rem;"><i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV</a>
            <button class="btn btn-primary" onclick="openRecordModal()" style="font-size:0.85rem;"><i data-lucide="plus-circle" style="width:15px;height:15px;"></i> Log Today's Record</button>
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;">
        <select class="admin-form-control" id="rec-filter-batch" style="max-width:280px;" onchange="loadRecords()">
            <option value="">All active & past batches</option>
            <?php foreach ($batches as $b): ?>
                <option value="<?= (int)$b['id'] ?>" <?= ((int)($_GET['batch_id'] ?? 0)===(int)$b['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($b['batch_name'] . ' (' . $b['batch_code'] . ')', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input class="admin-form-control" type="date" id="rec-from" style="max-width:160px;" onchange="loadRecords()" placeholder="From">
        <input class="admin-form-control" type="date" id="rec-to" style="max-width:160px;" onchange="loadRecords()" placeholder="To">
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Wk</th>
                    <th>Batch</th>
                    <th>Open</th>
                    <th>Mort</th>
                    <th>Sold</th>
                    <th>Close</th>
                    <th>Avg. Wt (kg)</th>
                    <th>Eggs</th>
                    <th>Trays</th>
                    <th>Damaged</th>
                    <th>% Prod</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="records-body">
                <tr><td colspan="13" style="text-align:center;padding:28px;color:#64748b;">Loading daily logs...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Batch Modal -->
<div id="batch-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:640px;box-shadow:0 20px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;margin:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 id="batch-modal-title" style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">New Chicken Batch</h3>
            <button type="button" onclick="closeBatchModal()" style="background:none;border:none;cursor:pointer;color:#64748b;" aria-label="Close batch form"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form id="batch-form">
            <input type="hidden" id="b-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Batch Name *</label>
                    <input class="admin-form-control" id="b-name" required placeholder="e.g. Broiler Batch 32 Tangakona">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Batch Code *</label>
                    <input class="admin-form-control" id="b-code" required placeholder="e.g. BRO-32">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">House / Shed *</label>
                    <select class="admin-form-control" id="b-house" required>
                        <option value="">Select house...</option>
                        <?php foreach ($houses as $h): ?>
                            <option value="<?= (int)$h['id'] ?>">
                                <?= htmlspecialchars($h['house_name'] . ' (Cap: ' . $h['capacity'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Breed</label>
                    <input class="admin-form-control" id="b-breed" placeholder="e.g. Cobb 500, ISA Brown, KARI">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Flock Type *</label>
                    <select class="admin-form-control" id="b-type" required>
                        <option value="broiler">Broiler (Meat Birds)</option>
                        <option value="layer">Layer (Egg Production)</option>
                        <option value="kienyeji">Improved Kienyeji</option>
                        <option value="dual_purpose">Dual Purpose</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Initial Birds Placed *</label>
                    <input class="admin-form-control" type="number" id="b-birds" min="1" required placeholder="e.g. 1000">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Placement Date *</label>
                    <input class="admin-form-control" type="date" id="b-placement" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Expected Sale / Harvest</label>
                    <input class="admin-form-control" type="date" id="b-sale">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Flock Status</label>
                    <select class="admin-form-control" id="b-status">
                        <option value="active">Active (Currently Raising)</option>
                        <option value="sold">Sold Out</option>
                        <option value="completed">Completed / Culled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="admin-form-group" style="grid-column:span 2">
                    <label class="admin-form-label">Notes & Source Details</label>
                    <textarea class="admin-form-control" id="b-notes" rows="2" placeholder="Hatchery supplier, vaccination notes, chick cost..."></textarea>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeBatchModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> Save Batch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- House Modal -->
<div id="house-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:480px;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 id="house-modal-title" style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Add Chicken House</h3>
            <button type="button" onclick="closeHouseModal()" style="background:none;border:none;cursor:pointer;color:#64748b;" aria-label="Close house form"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form id="house-form">
            <input type="hidden" id="h-id">
            <div class="admin-form-group"><label class="admin-form-label">House Name *</label><input class="admin-form-control" id="h-name" required placeholder="e.g. Long House East"></div>
            <div class="admin-form-group"><label class="admin-form-label">House Code *</label><input class="admin-form-control" id="h-code" required placeholder="e.g. H-03"></div>
            <div class="admin-form-group"><label class="admin-form-label">Location / Farm Section</label><input class="admin-form-control" id="h-loc" placeholder="e.g. Sector 2, Tangakona"></div>
            <div class="admin-form-group"><label class="admin-form-label">Capacity (bird count) *</label><input class="admin-form-control" type="number" id="h-cap" min="1" value="2000" required></div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeHouseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save House</button>
            </div>
        </form>
    </div>
</div>

<!-- Daily Record Modal -->
<div id="record-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:720px;box-shadow:0 20px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;margin:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 id="record-modal-title" style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Log Daily Record</h3>
            <button type="button" onclick="closeRecordModal()" style="background:none;border:none;cursor:pointer;color:#64748b;" aria-label="Close record form"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form id="record-form">
            <input type="hidden" id="r-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Batch *</label>
                    <select class="admin-form-control" id="r-batch" required onchange="handleBatchSelectionChange()">
                        <option value="">Select batch...</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" data-current="<?= (int)$b['current_birds'] ?>" data-type="<?= htmlspecialchars($b['batch_type'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($b['batch_name'] . ' (' . $b['batch_code'] . ') — ' . $b['current_birds'] . ' live', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Log Date *</label><input class="admin-form-control" type="date" id="r-date" required value="<?= date('Y-m-d') ?>"></div>
                <div class="admin-form-group"><label class="admin-form-label">Opening Birds</label><input class="admin-form-control" type="number" id="r-open" min="0" value="0"></div>
                <div class="admin-form-group"><label class="admin-form-label">Mortality Today (Dead)</label><input class="admin-form-control" type="number" id="r-mort" min="0" value="0" style="color:#dc2626;font-weight:700;"></div>
                <div class="admin-form-group"><label class="admin-form-label">Birds Sold (Direct)</label><input class="admin-form-control" type="number" id="r-sold" min="0" value="0"></div>
                <div class="admin-form-group"><label class="admin-form-label">Average Weight (kg)</label><input class="admin-form-control" type="number" step="0.001" id="r-awt" min="0" value="0" placeholder="e.g. 1.850"></div>
                <div class="admin-form-group"><label class="admin-form-label">Total Eggs Collected</label><input class="admin-form-control" type="number" id="r-eggs" min="0" value="0" oninput="calculateTrays()"></div>
                <div class="admin-form-group"><label class="admin-form-label">Trays (Calculated 30/tray)</label><input class="admin-form-control" type="number" id="r-trays" min="0" value="0" readonly style="background:#f8fafc;"></div>
                <div class="admin-form-group"><label class="admin-form-label">Extra Large Eggs</label><input class="admin-form-control" type="number" id="r-xl" min="0" value="0"></div>
                <div class="admin-form-group"><label class="admin-form-label">Damaged / Broken Eggs</label><input class="admin-form-control" type="number" id="r-dmg" min="0" value="0"></div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Daily Notes</label><textarea class="admin-form-control" id="r-notes" rows="2" placeholder="Symptoms, feeding behavior, health..."></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeRecordModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function calculateTrays() {
    const eggs = parseInt(document.getElementById('r-eggs').value || 0);
    document.getElementById('r-trays').value = Math.floor(eggs / 30);
}

function handleBatchSelectionChange() {
    const select = document.getElementById('r-batch');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.current) {
        document.getElementById('r-open').value = opt.dataset.current;
    }
}

function openBatchModal(d) {
    document.getElementById('batch-modal-title').textContent = d?.id ? 'Edit Chicken Batch' : 'New Chicken Batch';
    document.getElementById('b-id').value = d?.id || '';
    document.getElementById('b-name').value = d?.batch_name || '';
    document.getElementById('b-code').value = d?.batch_code || '';
    document.getElementById('b-house').value = d?.house_id || '';
    document.getElementById('b-breed').value = d?.breed || '';
    document.getElementById('b-type').value = d?.batch_type || 'broiler';
    document.getElementById('b-birds').value = d?.initial_birds || '';
    document.getElementById('b-placement').value = d?.placement_date || new Date().toISOString().split('T')[0];
    document.getElementById('b-sale').value = d?.expected_sale_date || '';
    document.getElementById('b-status').value = d?.status || 'active';
    document.getElementById('b-notes').value = d?.notes || '';
    document.getElementById('batch-modal').style.display = 'flex';
}
function closeBatchModal() { document.getElementById('batch-modal').style.display = 'none'; }

document.getElementById('batch-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('b-id').value);
    fd.append('batch_name', document.getElementById('b-name').value);
    fd.append('batch_code', document.getElementById('b-code').value);
    fd.append('house_id', document.getElementById('b-house').value);
    fd.append('breed', document.getElementById('b-breed').value);
    fd.append('batch_type', document.getElementById('b-type').value);
    fd.append('initial_birds', document.getElementById('b-birds').value);
    fd.append('placement_date', document.getElementById('b-placement').value);
    fd.append('expected_sale_date', document.getElementById('b-sale').value);
    fd.append('status', document.getElementById('b-status').value);
    fd.append('notes', document.getElementById('b-notes').value);
    try {
        const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_batch', {method:'POST', body:fd});
        const r = await res.json();
        if (r.success) location.reload();
        else alert('Error: ' + r.message);
    } catch(err) {
        alert('Server communication error.');
    }
});

async function deleteBatch(id) {
    if (!confirm('Are you sure you want to delete this batch? All daily records will also be removed.')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=delete_batch', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) location.reload(); else alert(r.message);
}

function openHouseModal(d) {
    document.getElementById('house-modal-title').textContent = d?.id ? 'Edit House' : 'Add Chicken House';
    document.getElementById('h-id').value = d?.id || '';
    document.getElementById('h-name').value = d?.house_name || '';
    document.getElementById('h-code').value = d?.house_code || '';
    document.getElementById('h-loc').value = d?.location || '';
    document.getElementById('h-cap').value = d?.capacity || 2000;
    document.getElementById('house-modal').style.display = 'flex';
}
function closeHouseModal() { document.getElementById('house-modal').style.display = 'none'; }

document.getElementById('house-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('h-id').value);
    fd.append('house_name', document.getElementById('h-name').value);
    fd.append('house_code', document.getElementById('h-code').value);
    fd.append('location', document.getElementById('h-loc').value);
    fd.append('capacity', document.getElementById('h-cap').value);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_house', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) location.reload();
    else alert('Error: ' + r.message);
});

async function deleteHouse(id) {
    if (!confirm('Delete this house? Only houses without active flocks can be deleted.')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=delete_house', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) location.reload(); else alert(r.message);
}

function openRecordModal(d) {
    document.getElementById('record-modal-title').textContent = d?.id ? 'Edit Daily Record' : 'Log Daily Record';
    document.getElementById('r-id').value = d?.id || '';
    document.getElementById('r-batch').value = d?.batch_id || (new URLSearchParams(location.search)).get('batch_id') || '';
    document.getElementById('r-date').value = d?.record_date || new Date().toISOString().split('T')[0];
    document.getElementById('r-open').value = d?.opening_birds || 0;
    document.getElementById('r-mort').value = d?.mortality || 0;
    document.getElementById('r-sold').value = d?.sold_birds || 0;
    document.getElementById('r-awt').value = d?.average_weight_kg || 0;
    document.getElementById('r-eggs').value = d?.total_eggs || 0;
    document.getElementById('r-trays').value = d?.trays || 0;
    document.getElementById('r-xl').value = d?.extra_large_eggs || 0;
    document.getElementById('r-dmg').value = d?.damaged_eggs || 0;
    document.getElementById('r-notes').value = d?.notes || '';
    if (!d?.id && document.getElementById('r-batch').value) {
        handleBatchSelectionChange();
    }
    document.getElementById('record-modal').style.display = 'flex';
}
function closeRecordModal() { document.getElementById('record-modal').style.display = 'none'; }

document.getElementById('record-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('r-id').value);
    fd.append('batch_id', document.getElementById('r-batch').value);
    fd.append('record_date', document.getElementById('r-date').value);
    fd.append('opening_birds', document.getElementById('r-open').value);
    fd.append('mortality', document.getElementById('r-mort').value);
    fd.append('sold_birds', document.getElementById('r-sold').value);
    fd.append('expected_weight_kg', 0);
    fd.append('average_weight_kg', document.getElementById('r-awt').value);
    fd.append('trays', document.getElementById('r-trays').value);
    fd.append('total_eggs', document.getElementById('r-eggs').value);
    fd.append('extra_large_eggs', document.getElementById('r-xl').value);
    fd.append('damaged_eggs', document.getElementById('r-dmg').value);
    fd.append('notes', document.getElementById('r-notes').value);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_batch_record', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) { closeRecordModal(); loadRecords(); }
    else alert('Error: ' + r.message);
});

async function loadRecords() {
    const tbody = document.getElementById('records-body');
    if (!tbody) return;
    const batch = document.getElementById('rec-filter-batch')?.value || '';
    const from = document.getElementById('rec-from')?.value || '';
    const to = document.getElementById('rec-to')?.value || '';
    let url = '/Backend/api/admin_poultry_v2.php?action=get_batch_records';
    if (batch) url += '&batch_id=' + batch;
    if (from) url += '&from=' + from;
    if (to) url += '&to=' + to;
    try {
        const res = await fetch(url);
        const r = await res.json();
        if (!r.success) { tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;color:#dc2626;">' + (r.message||'Failed') + '</td></tr>'; return; }
        const data = r.data || [];
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;padding:28px;color:#64748b;">No records logged yet.</td></tr>'; return; }
        tbody.innerHTML = data.map(r => {
            const pct = (parseFloat(r.production_pct) * 100).toFixed(1);
            return `<tr>
                <td><strong>${r.record_date}</strong></td>
                <td>${r.week_number||'—'}</td>
                <td><span class="table-code-badge">#${r.batch_id}</span></td>
                <td>${parseInt(r.opening_birds||0).toLocaleString()}</td>
                <td style="color:#dc2626;font-weight:700;">${parseInt(r.mortality||0)}</td>
                <td>${parseInt(r.sold_birds||0)}</td>
                <td><strong>${parseInt(r.closing_birds||0).toLocaleString()}</strong></td>
                <td>${parseFloat(r.average_weight_kg||0).toFixed(3)} kg</td>
                <td><strong>${parseInt(r.total_eggs||0).toLocaleString()}</strong></td>
                <td>${parseInt(r.trays||0)}</td>
                <td style="color:#64748b;">${parseInt(r.damaged_eggs||0)}</td>
                <td><span class="badge-pill ${pct > 60 ? 'badge-pill-success' : 'badge-pill-info'}">${pct}%</span></td>
                <td style="text-align:right;">
                    <button class="btn btn-trans btn-sm" onclick='openRecordModal(${JSON.stringify(r)})' aria-label="Edit record"><i data-lucide="pencil" style="width:13px;height:13px;"></i></button>
                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(${r.id})" aria-label="Delete record"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button>
                </td>
            </tr>`;
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;color:#dc2626;">Network error</td></tr>';
    }
}

async function deleteRecord(id) {
    if (!confirm('Delete this record?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=delete_batch_record', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) loadRecords(); else alert(r.message);
}

document.addEventListener('DOMContentLoaded', () => {
    loadRecords();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php';
