<?php
/**
 * Sub-Module: Flock Management
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
$page_title = 'Flock Manager';
include __DIR__ . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-stock.css?v=1.3">

<div class="admin-stock-wrapper">
    <div class="dashboard-hero-card" style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 32px; border-radius: 8px; margin-bottom: 32px; color: #ffffff;">
        <h1 style="color: #ffffff; margin: 0 0 8px 0;">Flock Manager</h1>
        <p style="color: rgba(255, 255, 255, 0.9); margin: 0;">Add and manage chicken flocks, monitor counts, and track poultry breeds.</p>
    </div>

    <!-- Flock Section Cards -->
    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Active & Historical Flocks</h3>
            <button class="btn btn-primary btn-sm" onclick="openFlockModal()">
                <i data-lucide="plus"></i> Hatch New Flock
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Flock Name</th>
                        <th>Breed</th>
                        <th>Initial Count</th>
                        <th>Current Count</th>
                        <th>Mortality Rate</th>
                        <th>Hatch Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="flocks-body">
                    <tr><td colspan="8" style="text-align:center; padding: 20px;">Loading flock data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Flock Modal -->
<div id="flock-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #ffffff; padding: 32px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="flock-modal-title" style="margin-bottom: 24px;">Hatch New Flock</h3>
        <form id="flock-form">
            <input type="hidden" name="id" id="flock-id">
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Flock Name</label>
                <input type="text" name="flock_name" id="flock-name" class="form-control" placeholder="e.g. Batch A - Layers" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Breed / Bird Type</label>
                <input type="text" name="breed" id="flock-breed" class="form-control" placeholder="e.g. ISA Brown" list="breed-suggestions" required>
                <datalist id="breed-suggestions">
                    <option value="ISA Brown"></option>
                    <option value="Kenbrow"></option>
                    <option value="Cobb 500"></option>
                    <option value="Ross 308"></option>
                    <option value="Kienyeji (Indigenous)"></option>
                    <option value="Sasso"></option>
                </datalist>
                <small style="display:block;color:#64748b;margin-top:6px;line-height:1.5;">Common types: <strong>ISA Brown / Kenbrow</strong> (eggs) · <strong>Cobb / Ross</strong> (meat) · <strong>Kienyeji</strong></small>
            </div>
            <div id="vax-preview" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;margin-bottom:15px;color:#14532d;font-size:0.84rem;"></div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Initial Count (Chicks)</label>
                <input type="number" name="initial_count" id="flock-initial-count" class="form-control" min="1" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;" id="current-count-group">
                <label class="form-label">Current Count</label>
                <input type="number" name="current_count" id="flock-current-count" class="form-control" min="0">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Hatch Date</label>
                <input type="date" name="hatch_date" id="flock-hatch-date" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;" id="status-group">
                <label class="form-label">Status</label>
                <select name="status" id="flock-status" class="form-control" style="width:100%; height:42px;">
                    <?php 
                    require_once __DIR__ . '/../../Backend/api/dropdowns.php';
                    echo renderDropdownOptions('flock_statuses', null, ''); 
                    ?>
                </select>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="button" class="btn btn-trans" style="flex: 1;" onclick="closeFlockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Flock</button>
            </div>
        </form>
    </div>
</div>

<!-- What happened next? Guided success step (so nobody gets lost after saving) -->
<div id="flock-success" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:2400;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:430px;padding:30px 28px;text-align:center;box-shadow:0 25px 60px rgba(15,23,42,0.25);">
        <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i data-lucide="check" style="width:28px;height:28px;"></i>
        </div>
        <h3 style="margin:0 0 6px;font-family:'Outfit',sans-serif;color:#0f172a;">Flock added successfully</h3>
        <p id="flock-success-detail" style="margin:0 0 14px;color:#475569;font-size:0.92rem;line-height:1.55;"></p>
        <div id="flock-success-badge" style="margin-bottom:18px;"></div>
        <div style="display:flex;flex-direction:column;gap:9px;">
            <a href="/Frontend/admin/hub_operations.php?tab=vaccinations" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px 16px;border-radius:6px;text-decoration:none;">
                <i data-lucide="syringe" style="width:16px;height:16px;"></i> View Vaccine Plan
            </a>
            <a href="/Frontend/admin/production.php" class="btn btn-outline" style="width:100%;justify-content:center;padding:10px 16px;border-radius:6px;text-decoration:none;">
                <i data-lucide="egg" style="width:16px;height:16px;"></i> Log Today's Eggs
            </a>
            <button type="button" class="btn btn-trans" style="width:100%;justify-content:center;padding:10px 16px;border-radius:6px;" onclick="document.getElementById('flock-success').style.display='none'">
                Done — I'm finished
            </button>
        </div>
    </div>
</div>

<script>
window.flocks_list = [];

function showTableError(tbodyId, colSpan, message) {
    document.getElementById(tbodyId).innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 32px;"><div style="display:inline-flex; align-items:center; gap:10px; color:#dc2626; background:#fef2f2; border:1px solid #fecaca; padding:14px 24px; border-radius:8px; font-weight:600;"><i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>${message}</div></td></tr>`;
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

async function loadFlocks() {
    setTableLoading('flocks-body', 8, 'Loading flock data...');
    try {
        const response = await fetch('/Backend/api/admin_poultry.php?action=get_flocks');
        if (!response.ok) throw new Error('Server error: ' + response.status);
        const result = await response.json();
        if (result.success) {
            window.flocks_list = result.data;
            renderFlocks();
        } else {
            showTableError('flocks-body', 8, result.message || 'Failed to load flocks.');
        }
    } catch (e) {
        showTableError('flocks-body', 8, 'Network error. Could not connect to server.');
        console.error('Error loading flocks:', e);
    }
}

function renderFlocks() {
    const tbody = document.getElementById('flocks-body');
    if (!window.flocks_list.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 20px;">No flocks registered yet. Click "Hatch New Flock" to start.</td></tr>';
        return;
    }

    tbody.innerHTML = window.flocks_list.map(f => {
        let statusBadge = 'badge-pill-success';
        if (f.status === 'sold') statusBadge = 'badge-pill-warning';
        if (f.status === 'archived') statusBadge = 'badge-pill-danger';

        const deadCount = Number(f.initial_count) - Number(f.current_count);
        const mortalityRate = f.initial_count > 0 ? ((deadCount / f.initial_count) * 100).toFixed(1) : 0;

        return `
        <tr>
            <td><strong>${f.flock_name}</strong></td>
            <td>${f.breed}</td>
            <td>${Number(f.initial_count).toLocaleString()}</td>
            <td>${Number(f.current_count).toLocaleString()}</td>
            <td><strong style="color: ${mortalityRate > 5 ? '#dc2626' : '#16a34a'};">${mortalityRate}%</strong> (${deadCount} dead)</td>
            <td>${f.hatch_date}</td>
            <td><span class="badge-pill ${statusBadge}">${f.status}</span></td>
            <td>
                <div style="display:flex; gap: 8px;">
                    <button class="btn btn-trans btn-sm" onclick="editFlock(${f.id})">Edit</button>
                    <button class="btn btn-trans btn-sm" style="color:#dc2626;" onclick="deleteFlock(${f.id})">Delete</button>
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

function openFlockModal() {
    document.getElementById('flock-modal-title').textContent = 'Hatch New Flock';
    document.getElementById('flock-form').reset();
    document.getElementById('flock-id').value = '';
    document.getElementById('current-count-group').style.display = 'none';
    document.getElementById('status-group').style.display = 'none';
    document.getElementById('flock-modal').style.display = 'flex';
    renderVaxPreview();
}

function closeFlockModal() {
    document.getElementById('flock-modal').style.display = 'none';
}

/* ── Auto-vaccination preview: pulled LIVE from the admin's Vaccine Program
   (Vaccines screen → Vaccine program). No schedules are hardcoded here. ── */
let vaxPreviewTimer = null;
const VAX_TYPE_LABELS = { layer: 'Layers (egg birds)', broiler: 'Broilers (meat birds)', kienyeji: 'Kienyeji', general: 'General' };
function renderVaxPreview() {
    const el = document.getElementById('vax-preview');
    const breed = (document.getElementById('flock-breed').value || '').trim();
    if (!el) return;
    if (!breed) { el.style.display = 'none'; return; }
    clearTimeout(vaxPreviewTimer);
    el.style.display = 'block';
    el.innerHTML = '<small style="color:#166534;">Checking the vaccine program...</small>';
    vaxPreviewTimer = setTimeout(async () => {
        try {
            const res = await fetch('/Backend/api/admin_poultry.php?action=resolve_vaccine_plan&breed=' + encodeURIComponent(breed));
            const r = await res.json();
            if (!r.success) throw new Error(r.message || 'load failed');
            const items = r.data || [];
            if (!items.length) {
                el.innerHTML = '<div style="display:flex;align-items:flex-start;gap:8px;color:#92400e;"><i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;margin-top:1px;"></i><span>No vaccine program has been set for this bird type yet. Ask the Farm Manager to add one under <strong>My Birds → Vaccines → Vaccine Program</strong> — or add vaccines for this flock later.</span></div>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }
            const typeLabel = VAX_TYPE_LABELS[r.bird_type] || r.bird_type;
            el.innerHTML = '<div style="display:flex;align-items:center;gap:6px;font-weight:700;margin-bottom:6px;">' +
                '<i data-lucide="syringe" style="width:14px;height:14px;"></i> ' + escapeHtml(typeLabel) + ' — we will add these automatically:</div>' +
                '<ul style="margin:0;padding-left:18px;display:grid;gap:2px;">' +
                items.map(v => '<li>' + escapeHtml(v.vaccine_name) + ' <small style="color:#166534;">(age ' + Number(v.day_after_hatch) + (Number(v.day_after_hatch) === 1 ? ' day' : ' days') + ')</small></li>').join('') +
                '</ul>' +
                '<small style="display:block;margin-top:6px;color:#166534;">Dates are worked out from the hatch date. You can change them later on the Vaccines screen.</small>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            el.innerHTML = '<small style="color:#94a3b8;">Could not load the vaccine program right now — vaccines can still be added after saving.</small>';
        }
    }, 350);
}

/* Guided "what next?" step so nobody gets lost right after saving a flock */
function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

function showFlockDone(flockName, birds, vaccinesAdded) {
    const detail = document.getElementById('flock-success-detail');
    const badge = document.getElementById('flock-success-badge');
    if (!detail || !badge) return;
    detail.innerHTML = 'Flock <strong>' + escapeHtml(flockName) + '</strong> is now in the system with <strong>' + Number(birds || 0).toLocaleString() + '</strong> birds.';
    if (Number(vaccinesAdded) > 0) {
        badge.innerHTML = '<div style="display:inline-flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-weight:700;padding:8px 16px;border-radius:8px;font-size:0.88rem;">' +
            '<i data-lucide="syringe" style="width:15px;height:15px;"></i> ' + Number(vaccinesAdded) + ' vaccines scheduled automatically</div>';
    } else {
        badge.innerHTML = '';
    }
    document.getElementById('flock-success').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function editFlock(id) {
    const f = window.flocks_list.find(item => item.id == id);
    if (!f) return;

    document.getElementById('flock-modal-title').textContent = 'Edit Flock Details';
    document.getElementById('flock-id').value = f.id;
    document.getElementById('flock-name').value = f.flock_name;
    document.getElementById('flock-breed').value = f.breed;
    document.getElementById('flock-initial-count').value = f.initial_count;
    document.getElementById('flock-current-count').value = f.current_count;
    document.getElementById('flock-hatch-date').value = f.hatch_date;
    document.getElementById('flock-status').value = f.status;

    document.getElementById('current-count-group').style.display = 'block';
    document.getElementById('status-group').style.display = 'block';
    document.getElementById('flock-modal').style.display = 'flex';
    renderVaxPreview();
}

async function deleteFlock(id) {
    if (!confirm('Are you sure you want to delete this flock? All production records will be deleted.')) return;
    try {
        const formData = new FormData();
        formData.append('id', id.toString());
        formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');

        const response = await fetch('/Backend/api/admin_poultry.php?action=delete_flock', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            loadFlocks();
        } else {
            alert(result.message);
        }
    } catch(e) { console.error(e); }
}

document.getElementById('flock-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('[type="submit"]');
    setBtnLoading(btn, true);
    const formData = new FormData(e.target);
    formData.append('csrf_token', window.BusiaAdmin?.csrfToken || '');
    try {
        const response = await fetch('/Backend/api/admin_poultry.php?action=save_flock', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            const isNewFlock = !document.getElementById('flock-id').value;
            const name = (document.getElementById('flock-name').value || '').trim();
            const birds = document.getElementById('flock-initial-count').value;
            const vaxAdded = result.data && result.data.vaccines_added ? Number(result.data.vaccines_added) : 0;
            closeFlockModal();
            loadFlocks();
            // New flock: guide the user to the next useful step instead of
            // dropping them back on an empty page ("where did my data go?").
            if (isNewFlock) {
                showFlockDone(name || 'Flock', birds, vaxAdded);
            }
        } else {
            alert('Error: ' + (result.message || 'Could not save flock.'));
        }
    } catch(e) {
        alert('Network error. Please check your connection and try again.');
        console.error(e);
    } finally {
        setBtnLoading(btn, false);
    }
});

// Live vaccine-plan preview while typing the breed
document.getElementById('flock-breed').addEventListener('input', renderVaxPreview);

// Spinner keyframe animation
if (!document.getElementById('admin-spin-style')) {
    const style = document.createElement('style');
    style.id = 'admin-spin-style';
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
}

document.addEventListener('DOMContentLoaded', () => {
    loadFlocks();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
