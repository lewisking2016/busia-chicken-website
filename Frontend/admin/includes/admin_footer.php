<?php
/**
 * Admin footer for admin pages.
 */
declare(strict_types=1);
?>
    </div>
</div>

<?php include __DIR__ . '/system_guide.php'; ?>

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
    /* Mobile table cards: on small screens each <td> shows its column label
       (copied from <thead>) above/inline with its value via data-label.
       Idempotent: runs immediately, on ready/load, and when JS re-renders
       table rows (AJAX), so dynamically refreshed tables keep their labels. */
    (function () {
        function labelTables(root) {
            (root || document).querySelectorAll('table.admin-table').forEach(tbl => {
                const heads = Array.from(tbl.querySelectorAll('thead th')).map(
                    th => (th.textContent || '').trim().replace(/\s+/g, ' ')
                );
                if (!heads.length) return;
                tbl.querySelectorAll('tbody tr').forEach(tr => {
                    tr.querySelectorAll('td').forEach((td, i) => {
                        if (i < heads.length) td.setAttribute('data-label', heads[i]);
                    });
                });
            });
        }
        // Tables are server-rendered before this script runs, so label
        // immediately; also re-run on ready/load and when JS adds tables.
        labelTables();
        document.addEventListener('DOMContentLoaded', () => labelTables());
        window.addEventListener('load', () => labelTables());
        if (typeof MutationObserver !== 'undefined') {
            const mo = new MutationObserver(muts => {
                if (muts.some(m => m.type === 'childList' && m.addedNodes.length)) {
                    labelTables(document);
                }
            });
            if (document.body) mo.observe(document.body, { childList: true, subtree: true });
        }
    })();
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

        /* Quick Actions dropdown: toggle on click, close on outside click,
           and "click" shortcuts open the page's own add/edit form. */
        const qaToggle = document.getElementById('quick-actions-toggle');
        const qaMenu = document.getElementById('quick-actions-menu');
        if (qaToggle && qaMenu) {
            const closeQa = () => { qaMenu.style.display = 'none'; const ch = qaToggle.querySelector('svg:last-child'); if (ch) ch.style.transform = ''; };
            qaToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const open = qaMenu.style.display !== 'none';
                closeQa();
                if (!open) {
                    qaMenu.style.display = 'block';
                    const ch = qaToggle.querySelector('svg:last-child');
                    if (ch) ch.style.transform = 'rotate(180deg)';
                }
            });
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.quick-actions-wrap')) closeQa();
            });
            qaMenu.addEventListener('click', (e) => {
                const t = e.target.closest('[data-quick-click]');
                if (!t) return;
                const text = t.getAttribute('data-quick-click');
                // Try to open the page's own add/edit form button by label.
                // Only real <button> elements open modals — the dropdown's own
                // <a> links also carry data-quick-click, so exclude them.
                const opener = [...document.querySelectorAll('button')].find(b => {
                    if (b === t || t.contains(b)) return false;
                    const label = (b.textContent || '').trim();
                    return label === text || label.includes(text);
                });
                if (opener && opener.closest('form')) {
                    // It's a submit button inside a hidden modal — open the modal first.
                    const modal = opener.closest('[id$="-modal"]');
                    if (modal) modal.style.display = 'flex';
                    e.preventDefault();
                } else if (opener) {
                    e.preventDefault();
                    opener.click();
                }
                closeQa();
            });
        }

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

        /* Nav search: type what you want to do → matching items appear with
           their section open, the rest hide. Clears back to the saved layout. */
        const navSearch = document.getElementById('admin-nav-search');
        const navList = document.getElementById('admin-nav-list');
        const navEmpty = document.getElementById('nav-search-empty');
        const navClear = document.getElementById('nav-search-clear');

        function openGroupVisual(group, open) {
            const ul = group.querySelector('.nav-subs');
            const chev = group.querySelector('.nav-chevron');
            if (!ul) return;
            ul.style.display = open ? 'flex' : 'none';
            group.classList.toggle('nav-group-open', open);
            if (chev) chev.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        function groupStorageKey(group) {
            const chev = group.querySelector('.nav-chevron');
            const link = group.querySelector('a');
            return 'busiaNavOpen:' + (chev && chev.dataset.navGroupKey ? chev.dataset.navGroupKey : (link ? link.getAttribute('href') : '') || '');
        }
        function restoreGroupState(group) {
            const isActive = !!group.querySelector('a.nav-item.active');
            if (isActive) { openGroupVisual(group, true); return; }
            try {
                openGroupVisual(group, sessionStorage.getItem(groupStorageKey(group)) === '1');
            } catch (e) { openGroupVisual(group, false); }
        }
        function clearNavHighlights() {
            nav.querySelectorAll('mark.nav-hl').forEach(m => m.replaceWith(document.createTextNode(m.textContent)));
        }
        function highlightAnchor(a, q) {
            let guard = 0;
            while (guard++ < 10) {
                const walker = document.createTreeWalker(a, NodeFilter.SHOW_TEXT, {
                    acceptNode(n) {
                        const p = n.parentElement;
                        if (p && (p.closest('mark') || p.tagName === 'I' || p.tagName === 'SVG')) return NodeFilter.FILTER_REJECT;
                        return (n.nodeValue || '').toLowerCase().includes(q) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                    }
                });
                const node = walker.nextNode();
                if (!node) break;
                const idx = node.nodeValue.toLowerCase().indexOf(q);
                if (idx < 0) continue;
                try {
                    const range = document.createRange();
                    range.setStart(node, idx);
                    range.setEnd(node, idx + q.length);
                    const mark = document.createElement('mark');
                    mark.className = 'nav-hl';
                    range.surroundContents(mark);
                } catch (err) { break; }
            }
        }
        function applyNavFilter() {
            const q = (navSearch.value || '').trim().toLowerCase();
            clearNavHighlights();
            if (navClear) navClear.style.display = q ? 'inline-flex' : 'none';
            if (!q) {
                if (navList) {
                    navList.querySelectorAll(':scope > li').forEach(li => li.style.display = '');
                    navList.querySelectorAll('a').forEach(a => a.style.display = 'flex');
                }
                nav.querySelectorAll('li.nav-group').forEach(restoreGroupState);
                if (navEmpty) navEmpty.style.display = 'none';
                return;
            }
            if (!navList) return;
            let anyHit = false;
            const itemText = a => (a.textContent || '').toLowerCase();
            navList.querySelectorAll(':scope > li').forEach(li => {
                if (li.classList.contains('nav-group')) {
                    const top = li.querySelector(':scope > div > a');
                    const subs = Array.from(li.querySelectorAll(':scope > ul a'));
                    const topHit = top ? itemText(top).includes(q) : false;
                    const subHits = subs.filter(a => itemText(a).includes(q));
                    const show = topHit || subHits.length > 0;
                    li.style.display = show ? '' : 'none';
                    if (show) {
                        anyHit = true;
                        openGroupVisual(li, true);
                        if (topHit) highlightAnchor(top, q);
                        subHits.forEach(a => highlightAnchor(a, q));
                    }
                } else {
                    // A top-level row can carry several links (Today's Tasks grid):
                    // show it when ANY link matches, highlight matches, and hide
                    // only the links that don't match.
                    const anchors = Array.from(li.querySelectorAll('a'));
                    const hits = anchors.filter(a => itemText(a).includes(q));
                    li.style.display = hits.length ? '' : 'none';
                    anchors.forEach(a => { a.style.display = itemText(a).includes(q) ? 'flex' : 'none'; });
                    if (hits.length) { anyHit = true; hits.forEach(a => highlightAnchor(a, q)); }
                }
            });
            if (navEmpty) navEmpty.style.display = anyHit ? 'none' : 'block';
        }

        if (navSearch && navList) {
            navSearch.addEventListener('input', applyNavFilter);
            if (navClear) navClear.addEventListener('click', () => { navSearch.value = ''; applyNavFilter(); navSearch.focus(); });
            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
                    e.preventDefault();
                    navSearch.focus();
                }
            });
        }
    });
    </script>

    <script>
    /* Detail view switch (Cards ⇄ List). Only the records/details change —
       summary cards are untouched. The control appears automatically on any
       page with an .admin-table AND on combined hub pages that host module
       frames (hub_mybirds / hub_money / etc.) so the switch is always
       available, even when the module's own toolbar is hidden inside the hub.
       The choice is remembered per browser. Default = "auto": list on wide
       screens, cards on small screens. */
    (function () {
        const KEY = 'busiaDetailView';
        const toggle = document.getElementById('detail-view-toggle');
        const countTables = () => document.querySelectorAll('table.admin-table').length;
        const frame = document.getElementById('mb-frame');

        function syncVisibility() {
            if (!toggle) return;
            toggle.style.display = (countTables() || !!frame) ? 'inline-flex' : 'none';
        }

        // Apply the chosen view to this page and, on hub pages, to the module
        // frame inside it (same origin). Each embedded module already ships the
        // busia-details-* CSS + data-label markup, so flipping its body class
        // is instant and no form state is lost.
        function applyToDoc(doc, m) {
            if (!doc || !doc.body) return;
            doc.body.classList.toggle('busia-details-cards', m === 'cards');
            doc.body.classList.toggle('busia-details-list', m === 'list');
            const ft = doc.getElementById('detail-view-toggle');
            if (ft) {
                ft.querySelectorAll('button[data-dt]').forEach(b => {
                    const on = b.getAttribute('data-dt') === m;
                    b.classList.toggle('dt-on', on);
                    b.setAttribute('aria-pressed', String(on));
                });
            }
        }

        function applyMode(mode) {
            const m = (mode === 'cards' || mode === 'list') ? mode : 'auto';
            applyToDoc(document, m);
            if (frame) {
                try { if (frame.contentDocument) applyToDoc(frame.contentDocument, m); } catch (e) {}
            }
            if (toggle) {
                toggle.querySelectorAll('button[data-dt]').forEach(b => {
                    const on = b.getAttribute('data-dt') === m;
                    b.classList.toggle('dt-on', on);
                    b.setAttribute('aria-pressed', String(on));
                });
            }
            try { localStorage.setItem(KEY, m); } catch (e) {}
        }

        if (toggle) {
            toggle.querySelectorAll('button[data-dt]').forEach(b => {
                b.addEventListener('click', () => {
                    const want = b.getAttribute('data-dt');
                    const current = document.body.classList.contains('busia-details-cards') ? 'cards'
                        : document.body.classList.contains('busia-details-list') ? 'list' : 'auto';
                    // Tapping the active option returns to the automatic layout.
                    applyMode(current === want ? 'auto' : want);
                });
            });
        }

        let saved = 'auto';
        try { saved = localStorage.getItem(KEY) || 'auto'; } catch (e) {}
        applyMode(saved);
        syncVisibility();

        // Keep visibility in sync when JS renders tables after page load.
        if (typeof MutationObserver !== 'undefined' && document.body) {
            new MutationObserver(syncVisibility).observe(document.body, { childList: true, subtree: true });
        }

        // On hub pages the module frame loads after this script runs, so apply
        // the saved (or just-chosen) view to the frame once it is ready.
        if (frame) {
            frame.addEventListener('load', () => {
                let cur = 'auto';
                try { cur = document.body.classList.contains('busia-details-cards') ? 'cards'
                    : document.body.classList.contains('busia-details-list') ? 'list' : 'auto'; } catch (e) {}
                applyMode(cur);
            });
        }
    })();
    </script>
</body>
</html>
