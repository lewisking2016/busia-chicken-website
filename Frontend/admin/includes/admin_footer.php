<?php
/**
 * Admin footer for admin pages.
 */
declare(strict_types=1);
?>
    </div>
</div>

<!-- Premium Interactive System Walkthrough Guide Modal -->
<div id="system-guide-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: #ffffff; width: 90%; max-width: 750px; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; transform: translateY(20px); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); max-height: 85vh;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 24px 32px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; position: relative;">
            <div>
                <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.4rem; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                    System Walkthrough Guide
                </h3>
                <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: rgba(255, 255, 255, 0.8);">Learn how to use and run the system step-by-step like a pro!</p>
            </div>
            <button id="close-system-guide" style="background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #ffffff; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <!-- Guide Content Body -->
        <div style="display: flex; flex: 1; min-height: 380px; overflow: hidden; background: #f8fafc;">
            
            <!-- Left Sidebar Navigation tabs -->
            <div style="width: 220px; border-right: 1px solid rgba(203, 213, 225, 0.8); background: #ffffff; padding: 16px 8px; display: flex; flex-direction: column; gap: 4px;">
                <button class="guide-nav-btn active" onclick="showGuideStep(0)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i>
                    <span>1. Dashboard</span>
                </button>
                <button class="guide-nav-btn" onclick="showGuideStep(1)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="package" style="width: 16px; height: 16px;"></i>
                    <span>2. Products</span>
                </button>
                <button class="guide-nav-btn" onclick="showGuideStep(2)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="shopping-bag" style="width: 16px; height: 16px;"></i>
                    <span>3. Orders</span>
                </button>
                <button class="guide-nav-btn" onclick="showGuideStep(3)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="bird" style="width: 16px; height: 16px;"></i>
                    <span>4. Poultry Ops</span>
                </button>
                <button class="guide-nav-btn" onclick="showGuideStep(4)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="brain-circuit" style="width: 16px; height: 16px;"></i>
                    <span>5. Feed & Stock</span>
                </button>
                <button class="guide-nav-btn" onclick="showGuideStep(5)" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 6px; text-align: left; font-weight: 600; font-size: 0.85rem; color: #475569; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="list-filter" style="width: 16px; height: 16px;"></i>
                    <span>6. Dropdowns</span>
                </button>
            </div>

            <!-- Right Content Area -->
            <div style="flex: 1; padding: 32px; overflow-y: auto;">
                
                <!-- Step 1: Dashboard -->
                <div class="guide-step-pane" style="display: block;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="layout-dashboard" style="color: var(--admin-primary);"></i>
                        Main Admin Dashboard
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        The Dashboard is your main screen. It gives you a quick snapshot of the entire farm business at a glance.
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Overview Metrics:</strong> Track total sales, active orders, customer count, and low stock warnings instantly.</li>
                        <li><strong>Sales Charts:</strong> Visualize order trends over the last 30 days to see if the business is growing.</li>
                        <li><strong>Recent Orders:</strong> See the latest customer orders, check payments, and process deliveries quickly.</li>
                    </ul>
                </div>

                <!-- Step 2: Products -->
                <div class="guide-step-pane" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="package" style="color: var(--admin-primary);"></i>
                        Products Management
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        This module allows you to manage all the chicken products and animal feed bags sold on the public shop.
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Add/Edit Products:</strong> Enter the name, retail price, stock level, description, category, and images.</li>
                        <li><strong>Link Recipes:</strong> For finished feed bags (like Layers Mash), you can link a production recipe so that making bags automatically deducts ingredients!</li>
                        <li><strong>Visibility Toggle:</strong> Instantly enable or disable a product from displaying on the public website.</li>
                    </ul>
                </div>

                <!-- Step 3: Orders -->
                <div class="guide-step-pane" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="shopping-bag" style="color: var(--admin-primary);"></i>
                        Orders Processing
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        Manage customer purchases, update order statuses, and oversee fulfillment.
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Fulfillment Workflow:</strong> Update status from <em>Pending</em> to <em>Processing</em>, <em>Shipped</em>, or <em>Delivered</em>.</li>
                        <li><strong>Payment Verification:</strong> Track payment details, including M-Pesa receipt references, to confirm payments.</li>
                        <li><strong>Detailed Summaries:</strong> View the customer's contact details, shipping address, and exact ordered items.</li>
                    </ul>
                </div>

                <!-- Step 4: Poultry Operations -->
                <div class="guide-step-pane" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="bird" style="color: var(--admin-primary);"></i>
                        Poultry Operations
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        Log daily animal yields, track flock counts, vaccine health schedules, and manage expenditures:
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Flock Manager:</strong> Hatch and record new chicken groups, monitoring current counts and mortality rates.</li>
                        <li><strong>Production Tracker:</strong> Track daily egg yields (clean vs cracked), meat weights (kgs), and feed consumed.</li>
                        <li><strong>Health & Vaccines:</strong> Administer scheduled treatments to keep flocks healthy.</li>
                        <li><strong>Expense Logger:</strong> Log outlays like labor, repairs, feed purchases, and cash flows.</li>
                    </ul>
                </div>

                <!-- Step 5: Feed & Stock -->
                <div class="guide-step-pane" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="brain-circuit" style="color: var(--admin-primary);"></i>
                        Feed & Stock Modules
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        The heart of the inventory system. It has four sub-navigation views:
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Stock Overview:</strong> View exact raw material stock levels (kgs), unit prices, and assets value.</li>
                        <li><strong>Feed Recipes:</strong> Create ingredient recipes and click "Produce Batch" to turn raw ingredients into finished feed bags automatically.</li>
                        <li><strong>Buy Ingredients:</strong> Record purchases made from suppliers. Mark them as "delivered" to automatically add the weight to your inventory and recalculate moving cost averages.</li>
                        <li><strong>Alert Center:</strong> View warnings when ingredients fall below their warning thresholds, or price changes occur.</li>
                    </ul>
                </div>

                <!-- Step 6: Dropdown Manager -->
                <div class="guide-step-pane" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="list-filter" style="color: var(--admin-primary);"></i>
                        Dropdown Manager
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;">
                        Customize selectable options across the system without writing any code.
                    </p>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; color: #334155; line-height: 1.5;">
                        <li><strong>Categories:</strong> Manage Order Statuses, Shipment Statuses, Units of Measurement, User Roles, and Product Categories.</li>
                        <li><strong>Add/Remove Options:</strong> Edit labels, values, and order ranks so dropdown lists display options exactly as you prefer.</li>
                        <li><strong>System Safeguard:</strong> Core system options are protected to keep the backend stable.</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Footer / Action Controls -->
        <div style="background: #ffffff; border-top: 1px solid rgba(203, 213, 225, 0.8); padding: 16px 32px; display: flex; justify-content: space-between; align-items: center;">
            <button id="guide-prev" onclick="changeGuideStep(-1)" class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 6px;">
                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i> Prev
            </button>
            <div style="display: flex; gap: 6px;" id="guide-dots">
                <span class="guide-dot active" onclick="showGuideStep(0)" style="width: 8px; height: 8px; border-radius: 50%; background: var(--admin-primary); display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
                <span class="guide-dot" onclick="showGuideStep(1)" style="width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
                <span class="guide-dot" onclick="showGuideStep(2)" style="width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
                <span class="guide-dot" onclick="showGuideStep(3)" style="width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
                <span class="guide-dot" onclick="showGuideStep(4)" style="width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
                <span class="guide-dot" onclick="showGuideStep(5)" style="width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; display: inline-block; cursor: pointer; transition: all 0.2s;"></span>
            </div>
            <button id="guide-next" onclick="changeGuideStep(1)" class="btn btn-primary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                Next <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

    </div>
</div>

<style>
.guide-nav-btn:hover {
    color: var(--admin-primary) !important;
    background: rgba(27, 94, 32, 0.04) !important;
}
.guide-nav-btn.active {
    color: #ffffff !important;
    background: var(--admin-primary) !important;
}
.guide-dot.active {
    background: var(--admin-primary) !important;
    transform: scale(1.2);
}
</style>

<script>
let currentGuideStep = 0;
const totalGuideSteps = 6;

function showGuideStep(stepIndex) {
    if (stepIndex < 0 || stepIndex >= totalGuideSteps) return;
    currentGuideStep = stepIndex;

    // Update Panes
    const panes = document.querySelectorAll('.guide-step-pane');
    panes.forEach((p, idx) => {
        p.style.display = idx === stepIndex ? 'block' : 'none';
    });

    // Update Nav Buttons
    const navBtns = document.querySelectorAll('.guide-nav-btn');
    navBtns.forEach((btn, idx) => {
        if (idx === stepIndex) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update Dots
    const dots = document.querySelectorAll('.guide-dot');
    dots.forEach((dot, idx) => {
        if (idx === stepIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });

    // Enable/Disable Prev/Next
    document.getElementById('guide-prev').disabled = stepIndex === 0;
    if (stepIndex === totalGuideSteps - 1) {
        document.getElementById('guide-next').innerHTML = 'Finish <i data-lucide="check" style="width: 16px; height: 16px;"></i>';
    } else {
        document.getElementById('guide-next').innerHTML = 'Next <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>';
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function changeGuideStep(delta) {
    const nextStep = currentGuideStep + delta;
    if (nextStep >= totalGuideSteps) {
        closeGuideModal();
    } else {
        showGuideStep(nextStep);
    }
}

function openGuideModal() {
    const modal = document.getElementById('system-guide-modal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.firstElementChild.style.transform = 'translateY(0)';
    }, 10);

    // Auto-detect page and set active guide tab!
    const path = window.location.pathname;
    if (path.includes('products.php')) {
        showGuideStep(1);
    } else if (path.includes('orders.php')) {
        showGuideStep(2);
    } else if (path.includes('flocks.php') || path.includes('production.php') || path.includes('vaccinations.php') || path.includes('expenses.php')) {
        showGuideStep(3);
    } else if (path.includes('stock_') || path.includes('incoming_stock.php')) {
        showGuideStep(4);
    } else if (path.includes('dropdowns.php')) {
        showGuideStep(5);
    } else {
        showGuideStep(0);
    }
}

function closeGuideModal() {
    const modal = document.getElementById('system-guide-modal');
    modal.style.opacity = '0';
    if (modal.firstElementChild) {
        modal.firstElementChild.style.transform = 'translateY(20px)';
    }
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('open-system-guide');
    if (trigger) {
        trigger.addEventListener('click', openGuideModal);
    }

    const closeBtn = document.getElementById('close-system-guide');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeGuideModal);
    }

    // Never auto-open the guide: it covers the whole admin panel and blocked
    // every sidebar click. It stays available via the help button only.
    const modal = document.getElementById('system-guide-modal');
    if (modal) {
        // Click the dimmed backdrop (outside the dialog) to dismiss
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeGuideModal();
            }
        });
        // Escape key dismisses too
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display !== 'none') {
                closeGuideModal();
            }
        });
    }
});
</script>

    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/gsap/gsap.min.js"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/lucide/lucide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <script>
    /* Sidebar scroll persistence: keep the left nav exactly where the user left it
       when navigating between modules, and bring the active module into view on
       direct loads (bookmark / refresh) instead of resetting to the top. */
    document.addEventListener('DOMContentLoaded', () => {
        const nav = document.getElementById('admin-nav');
        if (!nav) return;

        const SKEY = 'busiaAdminNavScroll';

        const saved = sessionStorage.getItem(SKEY);
        if (saved !== null && saved !== '' && !isNaN(parseInt(saved, 10))) {
            // Same sidebar markup on every admin page, so the saved offset maps 1:1.
            nav.scrollTop = parseInt(saved, 10);
        } else {
            // Direct load: center the highlighted (active) module in the nav so it
            // is visible instead of hidden above the fold.
            const active = nav.querySelector('a.nav-item.active, a.nav-sub.active');
            if (active) {
                const navRect = nav.getBoundingClientRect();
                const itemRect = active.getBoundingClientRect();
                nav.scrollTop += (itemRect.top - navRect.top) - (navRect.height / 2) + (itemRect.height / 2);
            }
        }

        // Remember the offset when a nav link is clicked so the next page restores it.
        nav.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href) {
                sessionStorage.setItem(SKEY, String(nav.scrollTop));
                // Mobile drawer: close after choosing a module.
                setNavOpen(false);
            }
        });

        /* Mobile sidebar drawer: hamburger opens/closes the nav as an overlay. */
        const backdrop = document.getElementById('admin-nav-backdrop');
        const toggle = document.getElementById('admin-nav-toggle');
        const setNavOpen = (open) => {
            nav.classList.toggle('open', open);
            document.body.classList.toggle('nav-open', open);
            if (toggle) toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        };
        if (toggle) {
            toggle.addEventListener('click', () => setNavOpen(!nav.classList.contains('open')));
        }
        if (backdrop) {
            backdrop.addEventListener('click', () => setNavOpen(false));
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') setNavOpen(false);
        });

        /* Collapsible nav groups: the active section is open by default; other
           sections stay collapsed and remember their open/closed state. */
        nav.querySelectorAll('li.nav-group').forEach(group => {
            const ul = group.querySelector('.nav-subs');
            const chevron = group.querySelector('.nav-chevron');
            if (!ul || !chevron) return;
            const link = group.querySelector('a');
            const key = 'busiaNavOpen:' + (chevron.dataset.navGroupKey || (link ? link.getAttribute('href') : '') || '');
            const isActive = !!group.querySelector('a.nav-item.active');
            const apply = (open) => {
                ul.style.display = open ? 'flex' : 'none';
                group.classList.toggle('nav-group-open', open);
            };
            if (isActive) {
                apply(true);
            } else {
                const stored = sessionStorage.getItem(key);
                if (stored === '1') apply(true);
                else if (stored === '0') apply(false);
            }
            chevron.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const open = ul.style.display !== 'none';
                apply(!open);
                sessionStorage.setItem(key, open ? '0' : '1');
            });
        });
    });
    </script>
</body>
</html>
