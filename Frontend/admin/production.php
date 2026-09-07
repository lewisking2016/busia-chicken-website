<?php
/**
 * Sub-Module: Daily Production Logs
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','sales_staff'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$path_prefix = '../../';
$page_title = 'Production Tracker';
include __DIR__ . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-stock.css?v=1.3">

<div class="admin-stock-wrapper">
    <div class="dashboard-hero-card" style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 32px; border-radius: 8px; margin-bottom: 32px; color: #ffffff;">
        <h1 style="color: #ffffff; margin: 0 0 8px 0;">Production Tracker</h1>
        <p style="color: rgba(255, 255, 255, 0.9); margin: 0;">Log daily egg yields, broiler meat output weights, feed consumption, and mortality stats.</p>
    </div>

    <!-- Production Logs Card -->
    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Daily Yield & Production Logbook</h3>
            <button class="btn btn-primary btn-sm" onclick="openProductionModal()">
                <i data-lucide="plus"></i> Log Daily Yield
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Flock</th>
                        <th>Eggs Collected</th>
                        <th>Cracked Eggs</th>
                        <th>Meat Weight (kgs)</th>
                        <th>Feed Eaten (kgs)</th>
                        <th>Mortality</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="production-body">
                    <tr><td colspan="9" style="text-align:center; padding: 20px;">Loading yield records...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Daily Yield Modal -->
<div id="production-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #ffffff; padding: 32px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow-y:auto; max-height:90vh;">
        <h3 id="production-modal-title" style="margin-bottom: 24px;">Log Today's Production</h3>
        <form id="production-form">
            <input type="hidden" name="id" id="production-id">

            <div style="display:flex;align-items:center;gap:8px;margin:2px 0 10px;">
                <span style="width:22px;height:22px;border-radius:50%;background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:800;flex-shrink:0;">1</span>
                <strong style="color:var(--admin-text-heading);font-size:0.92rem;">Which flock, and when?</strong>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label">Flock</label>
                    <select name="flock_id" id="production-flock-id" class="form-control" required style="width:100%; height:42px;">
                        <option value="">Choose a Flock...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="record_date" id="production-date" class="form-control" required>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;margin:4px 0 10px;">
                <span style="width:22px;height:22px;border-radius:50%;background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:800;flex-shrink:0;">2</span>
                <strong style="color:var(--admin-text-heading);font-size:0.92rem;">How many eggs today?</strong>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label">Good eggs collected</label>
                    <input type="number" name="eggs_collected" id="production-eggs" class="form-control" min="0" value="0" placeholder="0">
                    <small style="color:#94a3b8;">Whole, clean eggs</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Cracked / broken eggs</label>
                    <input type="number" name="cracked_eggs" id="production-cracked" class="form-control" min="0" value="0" placeholder="0">
                    <small style="color:#94a3b8;">Count them separately</small>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;margin:4px 0 10px;">
                <span style="width:22px;height:22px;border-radius:50%;background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:800;flex-shrink:0;">3</span>
                <strong style="color:var(--admin-text-heading);font-size:0.92rem;">Feed &amp; meat (optional)</strong>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div class="form-group">
                    <label class="form-label">Feed eaten (kgs)</label>
                    <input type="number" name="feed_consumed_kg" id="production-feed" class="form-control" step="0.01" min="0" value="0.00" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Meat yield (kgs)</label>
                    <input type="number" name="meat_weight_kg" id="production-meat" class="form-control" step="0.01" min="0" value="0.00" placeholder="0.00">
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;margin:4px 0 10px;">
                <span style="width:22px;height:22px;border-radius:50%;background:#b91c1c;color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:800;flex-shrink:0;">4</span>
                <strong style="color:var(--admin-text-heading);font-size:0.92rem;">Birds lost today?</strong>
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <input type="number" name="mortality" id="production-mortality" class="form-control" min="0" value="0" placeholder="0">
                <small style="color:#94a3b8;">We remove these birds from the flock count automatically.</small>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Notes / Remarks (optional)</label>
                <textarea name="notes" id="production-notes" class="form-control" style="height: 56px; font-family:inherit;" placeholder="Anything unusual today?"></textarea>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="button" class="btn btn-trans" style="flex: 1;" onclick="closeProductionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Today's Log</button>
            </div>
        </form>
    </div>
</div>

<!-- Saved! What next? Guided step so nobody gets lost after logging -->
<div id="production-success" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:2400;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:430px;padding:30px 28px;text-align:center;box-shadow:0 25px 60px rgba(15,23,42,0.25);">
        <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i data-lucide="check" style="width:28px;height:28px;"></i>
        </div>
        <h3 style="margin:0 0 6px;font-family:'Outfit',sans-serif;color:#0f172a;">Daily log saved</h3>
        <p id="production-success-detail" style="margin:0 0 18px;color:#475569;font-size:0.92rem;line-height:1.55;"></p>
        <div style="display:flex;flex-direction:column;gap:9px;">
            <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px 16px;border-radius:6px;" onclick="closeProductionSuccess(); openProductionModal();">
                <i data-lucide="clipboard-list" style="width:16px;height:16px;"></i> Add Another Record
            </button>
            <a href="/Frontend/admin/extras.php" class="btn btn-outline" style="width:100%;justify-content:center;padding:10px 16px;border-radius:6px;text-decoration:none;">
                <i data-lucide="heart-crack" style="width:16px;height:16px;"></i> Record Egg Losses (Broken / Stolen)
            </a>
            <button type="button" class="btn btn-trans" style="width:100%;justify-content:center;padding:10px 16px;border-radius:6px;" onclick="closeProductionSuccess()">
                Done — I'm finished
            </button>
        </div>
    </div>
</div>

<script>
window.production_list = [];
window.flocks_list = [];

function showTableError(tbodyId, colSpan, message) {
    document.getElementById(tbodyId).innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 32px;"><div style="display:inline-flex; align-items:center; gap:10px; color:#dc2626; background:#fef2f2; border:1px solid #fecaca; padding:14px 24px; border-radius:8px; font-weight:600;"><i data-lucide=\"alert-triangle\" style=\"width:18px;height:18px;\"></i>${message}</div></td></tr>`;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function setTableLoading(tbodyId, colSpan, message) {
    document.getElementById(tbodyId).innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 32px; color:#64748b;"><div style="display:inline-flex; align-items:center; gap:10px;"><div style="width:20px;height:20px;border:2px solid #cbd5e1;border-top-color:var(--admin-primary);border-radius:50%;animation:spin 0.8s linear infinite;"></div>${message}</div></td></tr>`;
}

function setBtnLoading(btn, loading) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.origText = btn.innerHTML;
        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px;"><div style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;"></div>Saving...</span>';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.origText || 'Save';
    }
}

async function loadData() {
    setTableLoading('production-body', 9, 'Loading yield records...');
    try {
        const flockRes = await fetch('/Backend/api/admin_poultry.php?action=get_flocks');
        if (!flockRes.ok) throw new Error('Server error');
        const flockResult = await flockRes.json();
        if (flockResult.success) {
            window.flocks_list = flockResult.data;
            populateFlocksDropdown();
        }

        const prodRes = await fetch('/Backend/api/admin_poultry.php?action=get_production');
        if (!prodRes.ok) throw new Error('Server error');
        const prodResult = await prodRes.json();
        if (prodResult.success) {
            window.production_list = prodResult.data;
            renderProduction();
        } else {
            showTableError('production-body', 9, prodResult.message || 'Failed to load production logs.');
        }
    } catch (e) {
        showTableError('production-body', 9, 'Network error. Could not connect to server.');
        console.error('Error loading production data:', e);
    }
}

function populateFlocksDropdown() {
    const dropdown = document.getElementById('production-flock-id');
    dropdown.innerHTML = '<option value="">Choose a Flock...</option>' + 
        window.flocks_list.filter(f => f.status === 'active')
            .map(f => `<option value="${f.id}">${f.flock_name} (${f.breed})</option>`).join('');
}

function renderProduction() {
    const tbody = document.getElementById('production-body');
    if (!window.production_list.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding: 20px;">No daily yield logs recorded yet. Click "Log Daily Yield" to begin.</td></tr>';
        return;
    }

    tbody.innerHTML = window.production_list.map(p => `
        <tr>
            <td>${p.record_date}</td>
            <td><strong>${p.flock_name}</strong></td>
            <td>${Number(p.eggs_collected).toLocaleString()}</td>
            <td>${Number(p.cracked_eggs).toLocaleString()}</td>
            <td>${Number(p.meat_weight_kg).toLocaleString()} kgs</td>
            <td>${Number(p.feed_consumed_kg).toLocaleString()} kgs</td>
            <td><strong style="color:${p.mortality > 0 ? '#dc2626' : '#1e293b'}">${p.mortality}</strong></td>
            <td style="max-width:200px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">${p.notes || '-'}</td>
            <td>
                <div style="display:flex; gap: 8px;">
                    <button class="btn btn-trans btn-sm" onclick="editProduction(${p.id})">Edit</button>
                    <button class="btn btn-trans btn-sm" style="color:#dc2626;" onclick="deleteProduction(${p.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openProductionModal() {
    document.getElementById('production-modal-title').textContent = 'Log Today\'s Production';
    document.getElementById('production-form').reset();
    document.getElementById('production-id').value = '';
    document.getElementById('production-date').value = new Date().toISOString().split('T')[0];
    // If there is only one active flock, pick it for the user so they
    // cannot log the wrong batch or get stuck on an empty dropdown.
    const sel = document.getElementById('production-flock-id');
    if (sel && sel.options.length === 2) sel.value = sel.options[1].value;
    document.getElementById('production-modal').style.display = 'flex';
}

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

function closeProductionSuccess() {
    document.getElementById('production-success').style.display = 'none';
}

function showProductionDone(summary) {
    const detail = document.getElementById('production-success-detail');
    if (!detail) return;
    detail.innerHTML = '<strong>' + escapeHtml(summary.flock || '') + '</strong> · ' + escapeHtml(summary.date || '') + '<br>' +
        (summary.eggs > 0 ? '<strong>' + Number(summary.eggs).toLocaleString() + '</strong> eggs collected' : 'No eggs recorded') +
        (summary.mortality > 0 ? ' · <strong>' + Number(summary.mortality).toLocaleString() + '</strong> birds lost (removed from flock count)' : '') +
        (summary.feed > 0 ? ' · <strong>' + Number(summary.feed).toLocaleString() + '</strong> kg feed' : '');
    document.getElementById('production-success').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeProductionModal() {
    document.getElementById('production-modal').style.display = 'none';
}

function editProduction(id) {
    const p = window.production_list.find(item => item.id == id);
    if (!p) return;

    document.getElementById('production-modal-title').textContent = 'Edit Daily Log';
    document.getElementById('production-id').value = p.id;
    document.getElementById('production-flock-id').value = p.flock_id;
    document.getElementById('production-date').value = p.record_date;
    document.getElementById('production-eggs').value = p.eggs_collected;
    document.getElementById('production-cracked').value = p.cracked_eggs;
    document.getElementById('production-meat').value = p.meat_weight_kg;
    document.getElementById('production-feed').value = p.feed_consumed_kg;
    document.getElementById('production-mortality').value = p.mortality;
    document.getElementById('production-notes').value = p.notes;

    document.getElementById('production-modal').style.display = 'flex';
}

async function deleteProduction(id) {
    if (!confirm('Are you sure you want to delete this log? If this log has mortality, deleting will add the birds back to the flock count.')) return;
    try {
        const formData = new FormData();
        formData.append('id', id.toString());
        formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');

        const response = await fetch('/Backend/api/admin_poultry.php?action=delete_production', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            loadData();
        } else {
            alert(result.message);
        }
    } catch(e) { console.error(e); }
}

document.getElementById('production-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('[type="submit"]');
    setBtnLoading(btn, true);
    const formData = new FormData(e.target);
    formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');
    try {
        const response = await fetch('/Backend/api/admin_poultry.php?action=save_production', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            const isNew = !document.getElementById('production-id').value;
            const sel = document.getElementById('production-flock-id');
            const flockName = sel && sel.selectedOptions.length ? sel.selectedOptions[0].textContent.trim() : '';
            const summary = {
                flock: flockName,
                date: document.getElementById('production-date').value,
                eggs: Number(document.getElementById('production-eggs').value) || 0,
                mortality: Number(document.getElementById('production-mortality').value) || 0,
                feed: Number(document.getElementById('production-feed').value) || 0
            };
            closeProductionModal();
            loadData();
            // New log: show a friendly "saved — what next?" step instead of
            // silently returning to the table.
            if (isNew) showProductionDone(summary);
        } else {
            alert('Error: ' + (result.message || 'Could not save log.'));
        }
    } catch(e) {
        alert('Network error. Please check your connection and try again.');
        console.error(e);
    } finally {
        setBtnLoading(btn, false);
    }
});

if (!document.getElementById('admin-spin-style')) {
    const style = document.createElement('style');
    style.id = 'admin-spin-style';
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
}

document.addEventListener('DOMContentLoaded', () => {
    loadData();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
