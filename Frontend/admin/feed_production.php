<?php
/**
 * Admin — Feed Mill & Production Module
 * ERPNext-aligned feed manufacturing engine:
 *   - Bill of Materials (BOM) / Feed Recipes with ingredient % & unit cost
 *   - Batch Production & Mixing with Raw Material stock deduction
 *   - Automatic Finished Feeds Stocking in Product Inventory
 *   - Live COGS (Cost of Goods Sold) calculation per bag and per kg
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Feed Production & Milling - Admin';
include __DIR__ . '/includes/admin_header.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'produce';
$validTabs = ['produce','recipes','stock','history'];
if (!in_array($tab, $validTabs, true)) $tab = 'produce';

$recipes = [];
$finishedFeeds = [];
$rawMaterials = [];
$kpis = ['total_batches' => 0, 'total_kg_produced' => 0, 'finished_bags_stock' => 0, 'raw_material_value' => 0];

if ($pdo) {
    $recipes = safeQueryAll($pdo, "SELECT * FROM feed_recipes ORDER BY recipe_name");
    $finishedFeeds = safeQueryAll($pdo, "SELECT p.*, r.recipe_name, r.base_bag_size_kg FROM products p LEFT JOIN feed_recipes r ON r.product_id = p.id WHERE p.product_type='feed' AND p.is_active=1 ORDER BY p.name");
    $rawMaterials = safeQueryAll($pdo, "SELECT id, material_name, current_stock, unit, current_price_per_unit, min_stock_level FROM raw_materials WHERE category='feed_ingredient' ORDER BY material_name");
    
    // KPI summary
    $kpiProd = $pdo->query("SELECT COUNT(*) AS total_batches, COALESCE(SUM(total_kg), 0) AS total_kg FROM feed_production_batches")->fetch(PDO::FETCH_ASSOC);
    if ($kpiProd) {
        $kpis['total_batches'] = (int)$kpiProd['total_batches'];
        $kpis['total_kg_produced'] = (float)$kpiProd['total_kg'];
    }
    $kpiStock = $pdo->query("SELECT COALESCE(SUM(stock_quantity), 0) FROM products WHERE product_type='feed' AND is_active=1")->fetchColumn();
    $kpis['finished_bags_stock'] = (int)$kpiStock;
    $kpiRaw = $pdo->query("SELECT COALESCE(SUM(current_stock * current_price_per_unit), 0) FROM raw_materials WHERE category='feed_ingredient'")->fetchColumn();
    $kpis['raw_material_value'] = (float)$kpiRaw;
}
?>
<style>
@keyframes spin{to{transform:rotate(360deg)}}
.feed-stat-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.feed-stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 700;
}
.stock-ok { background: #dcfce7; color: #166534; }
.stock-low { background: #fee2e2; color: #991b1b; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Feed Production & Milling</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Formulate feed recipes, mill batches, and automatically convert raw materials into sellable finished feed bags.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn btn-outline" onclick="openRecipeModal()"><i data-lucide="plus" style="width:15px;height:15px;"></i> New Recipe (BOM)</button>
        <a href="/Frontend/admin/bulk_sales.php?product=feed" class="btn btn-primary"><i data-lucide="shopping-cart" style="width:15px;height:15px;"></i> Sell Feeds</a>
    </div>
</div>

<!-- Key Metrics Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:22px;">
    <div class="feed-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Finished Feed in Stock</small>
            <strong style="display:block;font-size:1.35rem;color:#15803d;margin-top:2px;"><?= number_format($kpis['finished_bags_stock']) ?> Bags</strong>
        </div>
        <div style="width:40px;height:40px;border-radius:10px;background:#eef7f0;color:#166534;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="boxes" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="feed-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Total Milled to Date</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-text-heading);margin-top:2px;"><?= number_format($kpis['total_kg_produced']) ?> kg</strong>
        </div>
        <div style="width:40px;height:40px;border-radius:10px;background:#ede9fe;color:#6d28d9;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="scale" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="feed-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Production Batches</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-text-heading);margin-top:2px;"><?= number_format($kpis['total_batches']) ?> Runs</strong>
        </div>
        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="flask-conical" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="feed-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Raw Material Value</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-primary);margin-top:2px;">KES <?= number_format($kpis['raw_material_value'], 2) ?></strong>
        </div>
        <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;color:#166534;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="wheat" style="width:20px;height:20px;"></i>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=produce" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='produce'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="play-circle" style="width:15px;height:15px;"></i> Mill / Produce Batch</a>
    <a href="?tab=stock" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='stock'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="package" style="width:15px;height:15px;"></i> Finished Feed Stock (<?= count($finishedFeeds) ?>)</a>
    <a href="?tab=recipes" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='recipes'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="book-open" style="width:15px;height:15px;"></i> Feed Recipes (BOM)</a>
    <a href="?tab=history" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='history'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="history" style="width:15px;height:15px;"></i> Production History</a>
</div>

<!-- ═════════ TAB 1: PRODUCE FEED BATCH ═════════ -->
<?php if ($tab === 'produce'): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    <!-- Production Form -->
    <div class="admin-card">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Production Run Setup</h3>
        <p style="font-size:0.85rem;color:#64748b;margin:0 0 18px;">Select a recipe and target bag quantity. Raw materials are automatically verified and deducted upon milling.</p>
        
        <form id="produce-form">
            <div class="admin-form-group">
                <label class="admin-form-label">Feed Recipe (Formula) *</label>
                <select class="admin-form-control" id="pr-recipe" required onchange="loadRecipeIngredients()">
                    <option value="">— Select Feed Recipe —</option>
                    <?php foreach ($recipes as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" data-bag="<?= (float)$r['base_bag_size_kg'] ?>" data-name="<?= htmlspecialchars($r['recipe_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($r['recipe_name'], ENT_QUOTES, 'UTF-8') ?> (<?= (float)$r['base_bag_size_kg'] ?> kg bag)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Number of Bags *</label>
                    <input class="admin-form-control" type="number" id="pr-bags" min="1" value="10" required oninput="recalcProduction()">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Production Date *</label>
                    <input class="admin-form-control" type="date" id="pr-date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <!-- Cost Summary Banner -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin:18px 0;">
                <div>
                    <small style="color:#64748b;font-size:0.75rem;font-weight:600;text-transform:uppercase;">Total Output</small>
                    <strong id="pr-total-kg" style="display:block;font-size:1.2rem;color:var(--admin-text-heading);margin-top:2px;">0 kg</strong>
                </div>
                <div>
                    <small style="color:#64748b;font-size:0.75rem;font-weight:600;text-transform:uppercase;">Total Batch Cost</small>
                    <strong id="pr-total-cost" style="display:block;font-size:1.2rem;color:var(--admin-primary);margin-top:2px;">KES 0.00</strong>
                </div>
                <div>
                    <small style="color:#64748b;font-size:0.75rem;font-weight:600;text-transform:uppercase;">Cost Per Bag</small>
                    <strong id="pr-cost-bag" style="display:block;font-size:1.2rem;color:#15803d;margin-top:2px;">KES 0.00</strong>
                </div>
            </div>

            <button type="submit" id="pr-submit-btn" class="btn btn-primary" style="width:100%;padding:12px;font-size:0.95rem;">
                <i data-lucide="play" style="width:16px;height:16px;"></i> Mill & Stock Bags Now
            </button>
        </form>
    </div>

    <!-- Feasibility & Ingredient Requirements -->
    <div class="admin-card">
        <h3 style="margin:0 0 6px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Raw Material Feasibility</h3>
        <p style="font-size:0.85rem;color:#64748b;margin:0 0 16px;">Real-time stock check against warehouse inventory before production starts.</p>

        <div id="pr-ingredients">
            <div style="text-align:center;padding:40px 20px;color:#64748b;border:2px dashed #e2e8f0;border-radius:10px;">
                <i data-lucide="flask-conical" style="width:36px;height:36px;margin:0 auto 10px;display:block;opacity:0.6;"></i>
                Select a recipe on the left to verify ingredient availability.
            </div>
        </div>
    </div>
</div>

<!-- ═════════ TAB 2: FINISHED FEED STOCK ═════════ -->
<?php elseif ($tab === 'stock'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Warehouse Finished Feeds</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Ready-to-sell bags milled in our facility. Automatically incremented on production runs.</p>
        </div>
        <a href="/Frontend/admin/bulk_sales.php?product=feed" class="btn btn-primary"><i data-lucide="shopping-cart" style="width:15px;height:15px;"></i> Sell Feeds</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr>
                <th>Product Name</th>
                <th>Bag Size</th>
                <th>In Stock</th>
                <th>Selling Price</th>
                <th>Total Value</th>
                <th>Status</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($finishedFeeds)): ?>
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#64748b;">No finished feeds stocked yet. Run a production batch to stock bags!</td></tr>
            <?php else: foreach ($finishedFeeds as $f): 
                $stock = (int)$f['stock_quantity'];
                $val = $stock * (float)$f['price'];
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= (float)($f['weight_kg'] ?: 70) ?> kg bag</td>
                    <td><strong style="font-size:1.05rem;color:<?= $stock > 10 ? '#15803d' : '#b91c1c' ?>;"><?= $stock ?> Bags</strong></td>
                    <td>KES <?= number_format((float)$f['price'], 2) ?></td>
                    <td><strong>KES <?= number_format($val, 2) ?></strong></td>
                    <td>
                        <span class="feed-stock-badge <?= $stock > 10 ? 'stock-ok' : 'stock-low' ?>">
                            <?= $stock > 10 ? 'In Stock' : ($stock > 0 ? 'Low Stock' : 'Out of Stock') ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="?tab=produce" class="btn btn-trans btn-sm" title="Mill More"><i data-lucide="play" style="width:13px;height:13px;"></i> Produce</a>
                            <a href="/Frontend/admin/bulk_sales.php?product=feed" class="btn btn-outline btn-sm" title="Sell"><i data-lucide="shopping-cart" style="width:13px;height:13px;"></i> Sell</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═════════ TAB 3: RECIPES (BOM) ═════════ -->
<?php elseif ($tab === 'recipes'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Formulations & Recipes (Bill of Materials)</h3>
        <button class="btn btn-primary" onclick="openRecipeModal()"><i data-lucide="plus" style="width:15px;height:15px;"></i> New Recipe</button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Recipe Name</th><th>Target Species</th><th>Base Bag Size</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($recipes)): ?>
                <tr><td colspan="6" style="text-align:center;padding:28px;color:#64748b;">No recipes defined yet.</td></tr>
            <?php else: foreach ($recipes as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['recipe_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><span class="badge-pill badge-pill-info"><?= htmlspecialchars(ucfirst($r['target_species'] ?? 'all'), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><strong><?= (float)$r['base_bag_size_kg'] ?> kg</strong></td>
                    <td><?= htmlspecialchars($r['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge-pill <?= $r['is_active'] ? 'badge-pill-success' : 'badge-pill-info' ?>"><?= $r['is_active'] ? 'Active' : 'Draft' ?></span></td>
                    <td>
                        <button class="btn btn-trans btn-sm" onclick='openRecipeModal(<?= json_encode($r) ?>)'><i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit</button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═════════ TAB 4: PRODUCTION HISTORY ═════════ -->
<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Milling & Production History</h3>
        <a href="/Backend/api/export.php?module=feed_production" class="btn btn-outline"><i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV</a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Recipe</th><th>Bags Milled</th><th>Bag Size</th><th>Total kg</th><th>Batch Cost</th><th>Cost / Bag</th><th>Cost / kg</th></tr></thead>
            <tbody id="history-body">
                <tr><td colspan="8" style="text-align:center;padding:28px;color:#64748b;">Loading history...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Recipe Modal (BOM Setup) -->
<div id="recipe-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:2000;align-items:center;justify-content:center;overflow-y:auto;padding:16px;backdrop-filter:blur(3px);">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:700px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);max-height:92vh;overflow-y:auto;">
        <h3 id="recipe-modal-title" style="margin:0 0 18px;font-family:'Outfit',sans-serif;font-size:1.25rem;">Feed Recipe (Bill of Materials)</h3>
        
        <form id="recipe-form">
            <input type="hidden" id="r-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Recipe Name *</label>
                    <input class="admin-form-control" id="rc-name" required placeholder="e.g. Layers Mash Standard">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Target Bird Species</label>
                    <select class="admin-form-control" id="rc-target">
                        <option value="layers">Layers</option>
                        <option value="broilers">Broilers</option>
                        <option value="chicks">Chicks / Starter</option>
                        <option value="kienyeji">Kienyeji</option>
                        <option value="all">General Poultry</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bag Size (kg) *</label>
                    <input class="admin-form-control" type="number" step="0.01" id="rc-bag" value="70" required>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Description</label>
                    <input class="admin-form-control" id="rc-desc" placeholder="Nutritional formulation notes">
                </div>
            </div>

            <div style="margin:20px 0 10px;display:flex;justify-content:space-between;align-items:center;">
                <h4 style="margin:0;font-family:'Outfit',sans-serif;font-size:0.95rem;font-weight:700;">Formula Ingredients (per 1 Bag)</h4>
                <button type="button" class="btn btn-outline btn-sm" onclick="addIngredientRow()"><i data-lucide="plus" style="width:13px;height:13px;"></i> Add Ingredient</button>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
                <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <th style="text-align:left;padding:8px;font-size:0.8rem;color:#64748b;">Raw Material</th>
                    <th style="text-align:left;padding:8px;font-size:0.8rem;color:#64748b;width:150px;">kg per bag</th>
                    <th style="padding:8px;width:50px;"></th>
                </tr></thead>
                <tbody id="recipe-ingredients"></tbody>
            </table>

            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeRecipeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Formula</button>
            </div>
        </form>
    </div>
</div>

<script>
let ingredients = <?= json_encode($rawMaterials) ?>;
let ingIdx = 0;
let currentRecipeIngredients = [];

function addIngredientRow(data = null) {
    const idx = ingIdx++;
    const tr = document.createElement('tr');
    tr.dataset.idx = idx;
    tr.style.borderBottom = '1px solid #e2e8f0';
    let opts = '<option value="">Choose raw material...</option>' + ingredients.map(m => `<option value="${m.id}" data-price="${m.current_price_per_unit}" data-stock="${m.current_stock}">${escapeHtml(m.material_name)} (${m.current_stock} ${m.unit} in store)</option>`).join('');
    tr.innerHTML = `
        <td style="padding:6px;"><select class="admin-form-control" data-field="raw_material_id" style="font-size:0.85rem;padding:6px 8px;">${opts}</select></td>
        <td style="padding:6px;"><input class="admin-form-control" type="number" step="0.001" data-field="amount" value="${data?.amount_per_bag_kg||0}" style="font-size:0.85rem;padding:6px 8px;" placeholder="kg"></td>
        <td style="padding:6px;"><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()" style="padding:4px 8px;" aria-label="Remove row"><i data-lucide="x" style="width:12px;height:12px;"></i></button></td>
    `;
    document.getElementById('recipe-ingredients').appendChild(tr);
    if (data) tr.querySelector('[data-field=raw_material_id]').value = data.raw_material_id;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function openRecipeModal(d = null) {
    document.getElementById('recipe-modal-title').textContent = d?.id ? 'Edit Formula (BOM)' : 'New Feed Formula (BOM)';
    document.getElementById('r-id').value = d?.id || '';
    document.getElementById('rc-name').value = d?.recipe_name || '';
    document.getElementById('rc-target').value = d?.target_species || 'layers';
    document.getElementById('rc-bag').value = d?.base_bag_size_kg || 70;
    document.getElementById('rc-desc').value = d?.description || '';
    document.getElementById('recipe-ingredients').innerHTML = '';
    ingIdx = 0;
    if (d?.id) {
        fetch('/Backend/api/admin_poultry_v2.php?action=get_recipe_ingredients&recipe_id=' + d.id)
            .then(r => r.json())
            .then(r => { (r.data||[]).forEach(i => addIngredientRow(i)); });
    } else {
        addIngredientRow();
        addIngredientRow();
        addIngredientRow();
    }
    document.getElementById('recipe-modal').style.display = 'flex';
}
function closeRecipeModal() { document.getElementById('recipe-modal').style.display = 'none'; }

document.getElementById('recipe-form').addEventListener('submit', async e => {
    e.preventDefault();
    const ings = [];
    document.querySelectorAll('#recipe-ingredients tr').forEach(tr => {
        const mat = tr.querySelector('[data-field=raw_material_id]').value;
        const amt = parseFloat(tr.querySelector('[data-field=amount]').value || 0);
        if (mat && amt > 0) ings.push({raw_material_id: mat, amount_per_bag_kg: amt});
    });
    if (!ings.length) { alert('Please add at least one ingredient to the formula'); return; }
    const fd = new FormData();
    fd.append('id', document.getElementById('r-id').value);
    fd.append('recipe_name', document.getElementById('rc-name').value);
    fd.append('description', document.getElementById('rc-desc').value);
    fd.append('base_bag_size_kg', document.getElementById('rc-bag').value);
    fd.append('target_species', document.getElementById('rc-target').value);
    fd.append('ingredients', JSON.stringify(ings));
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_recipe', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) { alert('Formula saved successfully'); closeRecipeModal(); location.reload(); }
    else alert('Error: ' + r.message);
});

async function loadRecipeIngredients() {
    const rid = document.getElementById('pr-recipe').value;
    const container = document.getElementById('pr-ingredients');
    if (!rid) { container.innerHTML = '<div style="text-align:center;padding:40px 20px;color:#64748b;border:2px dashed #e2e8f0;border-radius:10px;">Select a recipe on the left to verify ingredient availability.</div>'; recalcProduction(); return; }
    
    container.innerHTML = '<p style="text-align:center;padding:20px;color:#64748b;">Checking raw materials...</p>';
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=get_recipe_ingredients&recipe_id=' + rid);
    const r = await res.json();
    if (!r.success || !r.data?.length) { 
        container.innerHTML = '<div style="padding:16px;background:#fee2e2;border-radius:8px;color:#b91c1c;">No ingredients defined for this formula. Please edit the recipe first.</div>'; 
        currentRecipeIngredients = [];
        return; 
    }
    currentRecipeIngredients = r.data;
    renderFeasibility();
}

function renderFeasibility() {
    const container = document.getElementById('pr-ingredients');
    const bags = parseInt(document.getElementById('pr-bags').value || 1);
    let allOk = true;

    let html = '<div style="display:flex;flex-direction:column;gap:8px;">';
    currentRecipeIngredients.forEach(i => {
        const need = parseFloat(i.amount_per_bag_kg) * bags;
        // Find current stock from rawMaterials array
        const raw = ingredients.find(m => String(m.id) === String(i.raw_material_id));
        const stock = parseFloat(raw?.current_stock || 0);
        const unit = raw?.unit || 'kg';
        const isSufficient = stock >= need;
        if (!isSufficient) allOk = false;

        html += `<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#f8fafc;border:1px solid ${isSufficient ? '#e2e8f0' : '#fecaca'};border-radius:8px;">
            <div>
                <strong style="display:block;font-size:0.9rem;">${escapeHtml(i.material_name)}</strong>
                <small style="color:#64748b;">Requires: ${need.toFixed(2)} ${unit} (${parseFloat(i.amount_per_bag_kg)} ${unit}/bag)</small>
            </div>
            <div style="text-align:right;">
                <span class="feed-stock-badge ${isSufficient ? 'stock-ok' : 'stock-low'}">
                    ${isSufficient ? 'Available: ' + stock.toFixed(1) + ' ' + unit : 'Short by ' + (need - stock).toFixed(1) + ' ' + unit}
                </span>
            </div>
        </div>`;
    });
    html += '</div>';

    if (!allOk) {
        html += `<div style="margin-top:14px;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
            <i data-lucide="alert-triangle" style="width:18px;height:18px;flex-shrink:0;"></i>
            <span>Warning: Some raw materials have insufficient stock for ${bags} bags.</span>
        </div>`;
    }
    container.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons();
    recalcProduction();
}

function recalcProduction() {
    const bags = parseInt(document.getElementById('pr-bags').value || 0);
    const selOpt = document.getElementById('pr-recipe').selectedOptions[0];
    const bagSize = parseFloat(selOpt?.dataset.bag || 0);
    const totalKg = bags * bagSize;
    let totalCost = 0;

    currentRecipeIngredients.forEach(i => {
        const need = parseFloat(i.amount_per_bag_kg) * bags;
        const raw = ingredients.find(m => String(m.id) === String(i.raw_material_id));
        const price = parseFloat(raw?.current_price_per_unit || 0);
        totalCost += need * price;
    });

    const costPerBag = bags > 0 ? totalCost / bags : 0;
    document.getElementById('pr-total-kg').textContent = totalKg.toFixed(1) + ' kg';
    document.getElementById('pr-total-cost').textContent = 'KES ' + totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('pr-cost-bag').textContent = 'KES ' + costPerBag.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

document.getElementById('produce-form').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('pr-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Processing Production...';

    const fd = new FormData();
    fd.append('recipe_id', document.getElementById('pr-recipe').value);
    fd.append('bags_produced', document.getElementById('pr-bags').value);

    try {
        const res = await fetch('/Backend/api/admin_poultry_v2.php?action=produce_feed', {method:'POST', body:fd});
        const r = await res.json();
        if (r.success) {
            alert(r.message || 'Feed produced and stocked successfully!');
            location.href = '?tab=stock';
        } else {
            alert('Error: ' + r.message);
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="play" style="width:16px;height:16px;"></i> Mill & Stock Bags Now';
        }
    } catch (e) {
        alert('Network error during production run');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="play" style="width:16px;height:16px;"></i> Mill & Stock Bags Now';
    }
});

async function loadHistory() {
    const tbody = document.getElementById('history-body');
    if (!tbody) return;
    try {
        const res = await fetch('/Backend/api/admin_poultry_v2.php?action=get_production_history');
        const r = await res.json();
        if (!r.success || !r.data?.length) { 
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:28px;color:#64748b;">No production runs recorded yet.</td></tr>'; 
            return; 
        }
        tbody.innerHTML = r.data.map(p => {
            const bags = parseInt(p.bags_produced);
            const costBag = bags > 0 ? (parseFloat(p.total_cost) / bags) : 0;
            return `<tr>
                <td>${p.production_date}</td>
                <td><strong>${escapeHtml(p.recipe_name)}</strong></td>
                <td><strong style="color:var(--admin-primary);">${bags} Bags</strong></td>
                <td>${parseFloat(p.bag_size_kg)} kg</td>
                <td>${parseFloat(p.total_kg).toFixed(1)} kg</td>
                <td>KES ${parseFloat(p.total_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td><strong style="color:#15803d;">KES ${costBag.toFixed(2)}</strong></td>
                <td>KES ${parseFloat(p.cost_per_kg).toFixed(2)}</td>
            </tr>`;
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#dc2626;">Network error loading history</td></tr>';
    }
}

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

document.addEventListener('DOMContentLoaded', () => {
    if ('<?= $tab ?>' === 'history') loadHistory();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php';
