<?php
/**
 * Admin — Feeding Program & Feed Allocations
 * Manages flock feed consumption:
 *   - Pulls finished feeds from warehouse inventory (products)
 *   - Tracks internal feed consumption & auto-rolls costs to batch_costs
 *   - Auto-deducts feed bags from products inventory
 *   - FCR (Feed Conversion Ratio) analysis for commercial optimization
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Feeding Program - Admin';
include __DIR__ . '/includes/admin_header.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'allocations';
$validTabs = ['allocations', 'standards', 'fcr'];
if (!in_array($tab, $validTabs, true)) $tab = 'allocations';

$batches = [];
$feedProducts = [];
$recentMillingCost = 52.0;
$totalKgMonth = 0;
$totalAllocationsCount = 0;

if ($pdo) {
    try {
        $batches = safeQueryAll($pdo, "SELECT id, batch_name, batch_code, batch_type, current_birds, placement_date FROM batches WHERE status IN ('active','completed','sold') ORDER BY placement_date DESC");

        $feedProducts = safeQueryAll($pdo, "SELECT id, name, weight_kg, price, stock_quantity FROM products WHERE product_type='feed' OR name LIKE '%feed%' OR name LIKE '%mash%' ORDER BY stock_quantity DESC");

        $costRow = $pdo->query("SELECT cost_per_kg FROM feed_production_batches ORDER BY production_date DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if (!empty($costRow['cost_per_kg']) && (float)$costRow['cost_per_kg'] > 0) {
            $recentMillingCost = (float)$costRow['cost_per_kg'];
        }

        $monthStats = $pdo->query("SELECT COALESCE(SUM(kg_fed), 0) AS total_kg, COUNT(*) AS cnt FROM feed_allocations WHERE allocation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch(PDO::FETCH_ASSOC);
        $totalKgMonth = (float)($monthStats['total_kg'] ?? 0);
        $totalAllocationsCount = (int)($monthStats['cnt'] ?? 0);
    } catch (Exception $e) {
        $batches = [];
        $feedProducts = [];
    }
}
$estimatedMonthlyFeedCost = round($totalKgMonth * $recentMillingCost, 2);
?>

<style>
.feeding-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 992px) {
    .feeding-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
    .feeding-kpi-grid { grid-template-columns: 1fr; }
}

.feed-toggle-btn {
    flex: 1;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-weight: 600;
    font-size: 0.85rem;
    color: #475569;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
}
.feed-toggle-btn.is-active {
    background: var(--admin-primary);
    color: #fff;
    border-color: var(--admin-primary);
}
</style>

<!-- Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;display:flex;align-items:center;gap:10px;">
            <i data-lucide="utensils" style="width:26px;height:26px;color:var(--admin-primary);"></i>
            Flock Feeding Program & Consumption
        </h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Record feed given to each flock. Deducts finished feed bags from warehouse stock and logs internal batch expenses automatically.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="feed_production.php" class="btn btn-outline" style="background:#fefce8;color:#ca8a04;border-color:#fef08a;">
            <i data-lucide="wheat" style="width:16px;height:16px;"></i> Produce More Feed
        </a>
        <button class="btn btn-primary" onclick="openAllocationModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Record Feeding
        </button>
    </div>
</div>

<!-- KPI Banner -->
<div class="feeding-kpi-grid">
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Feed Consumed (30d)</small>
            <strong style="font-size:1.6rem;color:#15803d;"><?= number_format($totalKgMonth, 1) ?> kg</strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">~<?= number_format(round($totalKgMonth / 50)) ?> bags (50kg eq.)</span>
        </div>
        <div class="stat-card-icon" style="background:#dcfce7;color:#15803d;">
            <i data-lucide="wheat"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Internal Feed Expense</small>
            <strong style="font-size:1.6rem;color:var(--admin-primary);">KES <?= number_format($estimatedMonthlyFeedCost) ?></strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">Charged to flock batch costs</span>
        </div>
        <div class="stat-card-icon">
            <i data-lucide="dollar-sign"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Milling Cost Benchmark</small>
            <strong style="font-size:1.6rem;color:#0284c7;">KES <?= number_format($recentMillingCost, 2) ?>/kg</strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">~KES <?= number_format($recentMillingCost * 50) ?> / 50kg bag</span>
        </div>
        <div class="stat-card-icon info">
            <i data-lucide="calculator"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <small>Feeding Sessions (30d)</small>
            <strong style="font-size:1.6rem;color:#d97706;"><?= number_format($totalAllocationsCount) ?></strong>
            <span style="font-size:0.75rem;color:#64748b;margin-top:2px;">Active flock logs</span>
        </div>
        <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
            <i data-lucide="clipboard-check"></i>
        </div>
    </div>
</div>

<!-- Tab Navigation Bar -->
<div style="display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:12px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=allocations" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='allocations'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="utensils" style="width:16px;height:16px;"></i> What Birds Ate (Allocations)
    </a>
    <a href="?tab=standards" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='standards'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="book-open" style="width:16px;height:16px;"></i> Feeding Standards
    </a>
    <a href="?tab=fcr" style="display:flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.88rem;<?= $tab==='fcr'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>">
        <i data-lucide="bar-chart-3" style="width:16px;height:16px;"></i> FCR Efficiency Report
    </a>
</div>

<?php if ($tab === 'allocations'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Flock Feeding Logs</h3>
            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Daily feed allocated from finished stock to chicken batches.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/Backend/api/export.php?module=feed_allocations" class="btn btn-outline" style="font-size:0.85rem;"><i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV</a>
            <button class="btn btn-primary" onclick="openAllocationModal()" style="font-size:0.85rem;"><i data-lucide="plus-circle" style="width:15px;height:15px;"></i> Record Feeding</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Flock Batch</th>
                    <th>Feed Product</th>
                    <th>Kilograms Fed</th>
                    <th>Bag Equivalent</th>
                    <th>Internal Cost (Est.)</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody id="alloc-body">
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#64748b;">Loading feed consumption records...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'standards'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Target Feeding Guidelines by Age</h3>
            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Standard nutritional intake schedules (grams/bird/day) for optimal growth and egg laying.</p>
        </div>
        <button class="btn btn-primary" onclick="openStandardModal()" style="font-size:0.85rem;"><i data-lucide="plus-circle" style="width:15px;height:15px;"></i> Add Week</button>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:16px;">
        <select class="admin-form-control" id="std-type" onchange="loadStandards()" style="max-width:220px;">
            <option value="broiler">Broilers (Meat)</option>
            <option value="layer">Layers (Eggs)</option>
            <option value="kienyeji">Kienyeji</option>
            <option value="dual_purpose">Dual Purpose</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Feed per bird / day (grams)</th>
                    <th>Feed per bird / week (kg)</th>
                    <th>For 1,000 birds (kg/day)</th>
                    <th>Feed Type</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="standards-body">
                <tr><td colspan="6" style="text-align:center;padding:28px;color:#64748b;">Loading standards...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php else: /* FCR */ ?>
<div class="admin-card">
    <div style="margin-bottom:18px;">
        <h3 style="margin:0 0 6px;font-family:'Outfit',sans-serif;font-size:1.15rem;color:var(--admin-text-heading);">Feed Conversion Ratio (FCR) Analysis</h3>
        <p style="margin:0;color:#64748b;font-size:0.85rem;"><strong>FCR = Total Feed Consumed (kg) ÷ Total Meat or Eggs Produced (kg)</strong>. A lower FCR means higher feed conversion efficiency and greater farm profitability.</p>
    </div>

    <div class="admin-form-group" style="max-width:440px;margin-bottom:24px;">
        <label class="admin-form-label">Select Chicken Batch</label>
        <select class="admin-form-control" id="fcr-batch" onchange="loadFcr()">
            <option value="">Choose active or past batch...</option>
            <?php foreach ($batches as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['batch_name'] . ' (' . $b['batch_code'] . ') — ' . $b['batch_type'] . ' (' . $b['current_birds'] . ' birds)', ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="fcr-result" style="display:none;">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-card-info">
                    <small>Total Feed Given</small>
                    <strong id="fcr-feed" style="font-size:1.5rem;color:#15803d;">—</strong>
                </div>
                <div class="stat-card-icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="wheat"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <small>Total Eggs Collected</small>
                    <strong id="fcr-eggs" style="font-size:1.5rem;color:var(--admin-primary);">—</strong>
                </div>
                <div class="stat-card-icon info"><i data-lucide="egg"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <small>Total Live Meat (kg)</small>
                    <strong id="fcr-meat" style="font-size:1.5rem;color:#d97706;">—</strong>
                </div>
                <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;"><i data-lucide="bird"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <small>Batch FCR</small>
                    <strong id="fcr-ratio" style="font-size:1.7rem;">—</strong>
                    <small id="fcr-grade" style="display:block;margin-top:2px;font-weight:700;"></small>
                </div>
                <div class="stat-card-icon" style="background:#f1f5f9;color:#334155;"><i data-lucide="bar-chart-3"></i></div>
            </div>
        </div>
        <div class="admin-card" style="background:#f8fafc;border:1px solid #e2e8f0;padding:20px;border-radius:10px;">
            <h4 style="margin:0 0 8px;font-family:'Outfit',sans-serif;font-size:1rem;color:var(--admin-text-heading);display:flex;align-items:center;gap:8px;">
                <i data-lucide="lightbulb" style="width:16px;height:16px;color:#ca8a04;"></i> Efficiency Evaluation
            </h4>
            <p id="fcr-explanation" style="margin:0;color:#475569;font-size:0.9rem;line-height:1.6;"></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Feeding Allocation Modal -->
<div id="alloc-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:520px;box-shadow:0 20px 40px rgba(0,0,0,0.2);margin:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div>
                <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Record Flock Feeding</h3>
                <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;">Deducts feed inventory & allocates cost to flock.</p>
            </div>
            <button type="button" onclick="closeAllocationModal()" style="background:none;border:none;cursor:pointer;color:#64748b;" aria-label="Close allocation form"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>

        <form id="alloc-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="admin-form-group" style="grid-column:span 2">
                    <label class="admin-form-label">Flock Batch *</label>
                    <select class="admin-form-control" id="al-batch" required>
                        <option value="">Select flock batch...</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= ((int)($_GET['batch_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['batch_name'] . ' (' . $b['batch_code'] . ') — ' . $b['current_birds'] . ' live birds', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Feeding Date *</label>
                    <input class="admin-form-control" type="date" id="al-date" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Feed Unit</label>
                    <div style="display:flex;gap:6px;">
                        <button type="button" class="feed-toggle-btn is-active" id="btn-mode-bags" onclick="setFeedMode('bags')">Bags (50kg)</button>
                        <button type="button" class="feed-toggle-btn" id="btn-mode-kg" onclick="setFeedMode('kg')">Total kg</button>
                    </div>
                </div>

                <div class="admin-form-group" style="grid-column:span 2">
                    <label class="admin-form-label">Finished Feed Product *</label>
                    <select class="admin-form-control" id="al-feed-product" required onchange="handleFeedProductChange()">
                        <option value="">Select feed from warehouse stock...</option>
                        <?php foreach ($feedProducts as $fp): ?>
                            <option value="<?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?>" data-weight="<?= (float)($fp['weight_kg'] ?: 50) ?>" data-stock="<?= (int)$fp['stock_quantity'] ?>">
                                <?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$fp['stock_quantity'] ?> bags available)
                            </option>
                        <?php endforeach; ?>
                        <option value="custom">Other / Custom Mix</option>
                    </select>
                </div>

                <div class="admin-form-group" id="custom-feed-group" style="grid-column:span 2;display:none;">
                    <label class="admin-form-label">Custom Feed Name</label>
                    <input class="admin-form-control" id="al-feed-custom" placeholder="e.g. Broiler Finisher Blend">
                </div>

                <div class="admin-form-group" id="bags-input-group">
                    <label class="admin-form-label" id="lbl-quantity">Bags Fed *</label>
                    <input class="admin-form-control" type="number" step="0.5" id="al-bags" min="0.5" value="1" oninput="calculateTotalKg()">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Total Kilograms (kg) *</label>
                    <input class="admin-form-control" type="number" step="0.001" id="al-kg" required min="0.1" value="50" oninput="calculateBagsFromKg()">
                </div>

                <!-- Live Cost Preview -->
                <div style="grid-column:span 2;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <small style="color:#15803d;display:block;font-weight:600;">Internal Flock Feed Cost (Est.)</small>
                        <span style="font-size:0.75rem;color:#64748b;">@ KES <?= number_format($recentMillingCost, 2) ?>/kg milling cost</span>
                    </div>
                    <strong id="live-cost-preview" style="font-size:1.15rem;color:#15803d;">KES 2,600</strong>
                </div>

                <div class="admin-form-group" style="grid-column:span 2">
                    <label class="admin-form-label">Notes</label>
                    <input class="admin-form-control" id="al-notes" placeholder="Feed dispenser, morning/evening, appetite observations...">
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeAllocationModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> Save & Deduct Stock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Standard Modal -->
<div id="std-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:440px;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Feeding Standard</h3>
            <button type="button" onclick="document.getElementById('std-modal').style.display='none'" style="background:none;border:none;cursor:pointer;color:#64748b;" aria-label="Close standard form"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form id="std-form">
            <input type="hidden" id="s-id">
            <div class="admin-form-group">
                <label class="admin-form-label">Bird Type</label>
                <select class="admin-form-control" id="s-type">
                    <option value="broiler">Broilers</option>
                    <option value="layer">Layers</option>
                    <option value="kienyeji">Kienyeji</option>
                    <option value="dual_purpose">Dual Purpose</option>
                </select>
            </div>
            <div class="admin-form-group"><label class="admin-form-label">Week Number *</label><input class="admin-form-control" type="number" id="s-week" min="1" max="80" required></div>
            <div class="admin-form-group"><label class="admin-form-label">Grams per bird per day *</label><input class="admin-form-control" type="number" step="0.01" id="s-grams" required placeholder="e.g. 130"></div>
            <div class="admin-form-group"><label class="admin-form-label">Feed Type Name</label><input class="admin-form-control" id="s-feed" placeholder="e.g. Broiler Finisher"></div>
            <div class="admin-form-group"><label class="admin-form-label">Notes</label><input class="admin-form-control" id="s-notes"></div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="document.getElementById('std-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Standard</button>
            </div>
        </form>
    </div>
</div>

<script>
const currentTab = '<?= $tab ?>';
const millingCostPerKg = <?= (float)$recentMillingCost ?>;
let activeFeedMode = 'bags';

function setFeedMode(mode) {
    activeFeedMode = mode;
    document.getElementById('btn-mode-bags').classList.toggle('is-active', mode === 'bags');
    document.getElementById('btn-mode-kg').classList.toggle('is-active', mode === 'kg');
    if (mode === 'bags') {
        calculateTotalKg();
    } else {
        calculateBagsFromKg();
    }
}

function handleFeedProductChange() {
    const sel = document.getElementById('al-feed-product');
    const isCustom = sel.value === 'custom';
    document.getElementById('custom-feed-group').style.display = isCustom ? 'block' : 'none';
    calculateTotalKg();
}

function getSelectedBagWeight() {
    const sel = document.getElementById('al-feed-product');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.weight) {
        return parseFloat(opt.dataset.weight) || 50;
    }
    return 50;
}

function calculateTotalKg() {
    const bagWeight = getSelectedBagWeight();
    const bags = parseFloat(document.getElementById('al-bags').value || 0);
    const totalKg = bags * bagWeight;
    document.getElementById('al-kg').value = totalKg.toFixed(2);
    updateCostPreview(totalKg);
}

function calculateBagsFromKg() {
    const bagWeight = getSelectedBagWeight();
    const kg = parseFloat(document.getElementById('al-kg').value || 0);
    const bags = bagWeight > 0 ? (kg / bagWeight).toFixed(2) : 0;
    document.getElementById('al-bags').value = bags;
    updateCostPreview(kg);
}

function updateCostPreview(kg) {
    const cost = kg * millingCostPerKg;
    document.getElementById('live-cost-preview').textContent = 'KES ' + Math.round(cost).toLocaleString();
}

function openAllocationModal() {
    document.getElementById('alloc-modal').style.display = 'flex';
    calculateTotalKg();
}
function closeAllocationModal() { document.getElementById('alloc-modal').style.display = 'none'; }

document.getElementById('alloc-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('batch_id', document.getElementById('al-batch').value);
    fd.append('allocation_date', document.getElementById('al-date').value);
    fd.append('kg_fed', document.getElementById('al-kg').value);

    const selProd = document.getElementById('al-feed-product').value;
    const feedType = selProd === 'custom' ? (document.getElementById('al-feed-custom').value || 'Custom Feed') : selProd;
    fd.append('feed_type', feedType);
    fd.append('notes', document.getElementById('al-notes').value);

    try {
        const res = await fetch('/Backend/api/admin_business.php?action=add_feed_allocation', {method:'POST', body:fd});
        const r = await res.json();
        if (r.success) {
            closeAllocationModal();
            loadAllocations();
            alert('Feeding recorded successfully! Inventory deducted and batch cost updated.');
        } else {
            alert('Error: ' + r.message);
        }
    } catch(err) {
        alert('Server communication error.');
    }
});

async function loadAllocations() {
    const tbody = document.getElementById('alloc-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:28px;color:#64748b;">Loading feed consumption records...</td></tr>';
    try {
        const res = await fetch('/Backend/api/admin_business.php?action=list_feed_allocations');
        const r = await res.json();
        if (!r.success) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#dc2626;">' + (r.message||'Failed') + '</td></tr>'; return; }
        const data = r.data || [];
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:28px;color:#64748b;">No feedings recorded yet.</td></tr>'; return; }
        tbody.innerHTML = data.map(a => {
            const kg = parseFloat(a.kg_fed || 0);
            const bagsEq = (kg / 50).toFixed(1);
            const estCost = Math.round(kg * millingCostPerKg);
            return `<tr>
                <td><strong>${a.allocation_date}</strong></td>
                <td><strong style="color:var(--admin-text-heading);">${escapeHtml(a.batch_name||'—')}</strong></td>
                <td><span style="font-weight:600;color:#15803d;">${escapeHtml(a.feed_type||'—')}</span></td>
                <td><strong>${kg.toFixed(2)} kg</strong></td>
                <td><span class="badge-pill badge-pill-info">${bagsEq} bags</span></td>
                <td><strong style="color:var(--admin-primary);">KES ${estCost.toLocaleString()}</strong></td>
                <td style="color:#64748b;font-size:0.85rem;">${escapeHtml(a.notes||'—')}</td>
            </tr>`;
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#dc2626;">Network error</td></tr>';
    }
}

function openStandardModal(d) {
    document.getElementById('s-id').value = d?.id || '';
    document.getElementById('s-type').value = d?.bird_type || 'broiler';
    document.getElementById('s-week').value = d?.week_number || '';
    document.getElementById('s-grams').value = d?.feed_per_bird_per_day_grams || '';
    document.getElementById('s-feed').value = d?.feed_type || '';
    document.getElementById('s-notes').value = d?.notes || '';
    document.getElementById('std-modal').style.display = 'flex';
}

document.getElementById('std-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('s-id').value);
    fd.append('bird_type', document.getElementById('s-type').value);
    fd.append('week_number', document.getElementById('s-week').value);
    fd.append('feed_per_bird_per_day_grams', document.getElementById('s-grams').value);
    fd.append('feed_type', document.getElementById('s-feed').value);
    fd.append('notes', document.getElementById('s-notes').value);
    const res = await fetch('/Backend/api/admin_business.php?action=save_feeding_standard', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) { document.getElementById('std-modal').style.display='none'; loadStandards(); }
    else alert('Error: ' + r.message);
});

async function loadStandards() {
    const type = document.getElementById('std-type')?.value || 'broiler';
    const tbody = document.getElementById('standards-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:28px;color:#64748b;">Loading...</td></tr>';
    try {
        const res = await fetch('/Backend/api/admin_business.php?action=list_feeding_standards');
        const r = await res.json();
        let data = (r.data || []).filter(s => s.bird_type === type);
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:28px;color:#64748b;">No standards recorded for this bird type.</td></tr>'; return; }
        tbody.innerHTML = data.map(s => `<tr>
            <td><strong>Week ${s.week_number}</strong></td>
            <td>${parseFloat(s.feed_per_bird_per_day_grams).toFixed(1)} g</td>
            <td>${(parseFloat(s.feed_per_bird_per_day_grams) * 7 / 1000).toFixed(2)} kg</td>
            <td><strong>${(parseFloat(s.feed_per_bird_per_day_grams) * 1000 / 1000).toFixed(1)} kg</strong></td>
            <td><span class="badge-pill badge-pill-info">${escapeHtml(s.feed_type||'—')}</span></td>
            <td style="text-align:right;">
                <button class="btn btn-trans btn-sm" onclick='openStandardModal(${JSON.stringify(s)})' aria-label="Edit feeding standard"><i data-lucide="pencil" style="width:13px;height:13px;"></i></button>
            </td>
        </tr>`).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#dc2626;">Network error</td></tr>'; }
}

async function loadFcr() {
    const bid = document.getElementById('fcr-batch').value;
    if (!bid) { document.getElementById('fcr-result').style.display='none'; return; }
    try {
        const res = await fetch('/Backend/api/admin_business.php?action=fcr_report&batch_id=' + bid);
        const r = await res.json();
        if (!r.success) { alert(r.message); return; }
        const d = r.data;
        document.getElementById('fcr-feed').textContent = d.total_feed_kg.toFixed(1) + ' kg';
        document.getElementById('fcr-eggs').textContent = d.total_eggs.toLocaleString();
        document.getElementById('fcr-meat').textContent = d.total_meat_kg.toFixed(1) + ' kg';
        const ratio = d.fcr;
        let grade = 'Average';
        let gradeColor = '#d97706';
        let explanation = '';
        if (d.batch.batch_type === 'broiler') {
            if (ratio > 0 && ratio <= 1.6) { grade = 'Excellent Efficiency'; gradeColor = '#16a34a'; explanation = 'FCR of ' + ratio.toFixed(2) + ' is exceptional for broilers. You are turning feed into meat with high efficiency and low wastage. Keep the current feeding protocol.'; }
            else if (ratio > 0 && ratio <= 1.9) { grade = 'Good Industry Standard'; gradeColor = '#16a34a'; explanation = 'FCR of ' + ratio.toFixed(2) + ' is healthy and profitable for commercial broilers (standard is 1.6 - 1.9).'; }
            else if (ratio > 0 && ratio <= 2.2) { grade = 'Average'; gradeColor = '#d97706'; explanation = 'FCR of ' + ratio.toFixed(2) + ' is slightly high. Inspect feeders for feed spillage or adjust feeding times.'; }
            else { grade = 'Requires Attention'; gradeColor = '#dc2626'; explanation = 'FCR of ' + ratio.toFixed(2) + ' indicates excessive feed consumed relative to weight gain. Check bird health, ventilation, or feed quality.'; }
        } else {
            if (ratio > 0 && ratio <= 2.0) { grade = 'Excellent Layer Conversion'; gradeColor = '#16a34a'; explanation = 'FCR of ' + ratio.toFixed(2) + ' indicates high laying productivity per kilogram of layer mash consumed.'; }
            else if (ratio > 0 && ratio <= 2.4) { grade = 'Good Range'; gradeColor = '#16a34a'; explanation = 'FCR of ' + ratio.toFixed(2) + ' is within expected standard for layers.'; }
            else { grade = 'Needs Improvement'; gradeColor = '#d97706'; explanation = 'FCR of ' + ratio.toFixed(2) + ' is above target. Check egg collection accuracy and check for feed wastage.'; }
        }
        document.getElementById('fcr-ratio').textContent = ratio > 0 ? ratio.toFixed(2) : '—';
        document.getElementById('fcr-ratio').style.color = gradeColor;
        document.getElementById('fcr-grade').textContent = grade;
        document.getElementById('fcr-grade').style.color = gradeColor;
        document.getElementById('fcr-explanation').textContent = explanation;
        document.getElementById('fcr-result').style.display = 'block';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) { alert('Failed to calculate FCR.'); }
}

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

document.addEventListener('DOMContentLoaded', () => {
    if (currentTab === 'allocations') loadAllocations();
    if (currentTab === 'standards') loadStandards();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php';
