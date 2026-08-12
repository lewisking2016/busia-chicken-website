<?php
/**
 * System Walkthrough Guide — every module, every flow.
 *
 * Rendered as a modal from $guideSections below. Each module entry:
 *   id     — stable key (used for prev/next + auto-detect)
 *   pages  — script basenames that auto-open this entry
 *   icon   — lucide icon name
 *   title  — heading
 *   summary— one or two sentences about what the module does
 *   steps  — the flow, numbered (supports inline <strong>/<em>)
 *   tips   — "good to know" bullet points
 *
 * Grouped to mirror the admin sidebar so the guide stays in sync with
 * what the user actually sees in navigation.
 */
declare(strict_types=1);

$guideSections = [
    'Getting Started' => [
        [
            'id' => 'dashboard', 'pages' => ['dashboard.php'], 'icon' => 'layout-dashboard',
            'title' => 'Dashboard — Farm Overview',
            'summary' => 'Your landing screen after login. It is a live snapshot of the whole farm: today’s money in and out, money owed by customers, today’s egg production, mortality, low-stock warnings and pending orders.',
            'steps' => [
                'Read the <strong>overview cards</strong> at the top: money in today, money out today, total customer credit owed, overdue credit, eggs produced today, mortality today, low-stock items and pending orders.',
                'Check the <strong>charts</strong> for sales and production trends so you can see whether the business is growing.',
                'Review the <strong>recent orders</strong> list — open each one to verify payment and update its status (pending → paid → shipped → completed).',
                'Click any card or button to jump straight into the relevant module for detail.',
            ],
            'tips' => [
                'The dashboard is read-only — all changes happen inside the modules.',
                'If you land here with a yellow notice saying you lack permission, that module was locked for your role. Ask the Super Admin to grant it under Settings → Roles &amp; Permissions.',
            ],
        ],
        [
            'id' => 'quick-actions', 'pages' => [], 'icon' => 'zap',
            'title' => 'Quick Actions Menu',
            'summary' => 'A shortcut menu in the top bar of every admin page. It lists that page’s main add/edit forms and exports, so you never have to hunt for the button.',
            'steps' => [
                'Look for the <strong>Quick Actions</strong> button at the top right of the page.',
                'Click it to open the menu — it shows shortcuts relevant to the page you are on (for example “Add Product” and “Export Products CSV” on the Products page).',
                'Click a shortcut — it opens that page’s own form/modal directly (e.g. Add Product opens the product form).',
                'Links that export (e.g. “Export … CSV”) download the file straight away.',
            ],
            'tips' => [
                'The menu is different on every module — it always matches the page you’re viewing.',
                'It works on phones too: the menu appears in the top bar above the drawer.',
            ],
        ],
    ],
    'Poultry Operations' => [
        [
            'id' => 'flocks', 'pages' => ['flocks.php', 'flocks_tab.php', 'hub_operations.php', 'operations.php'], 'icon' => 'bird',
            'title' => 'Flocks',
            'summary' => 'Every group of birds on the farm is a flock (batch). Flocks track how many birds you started with, how many are still alive, and whether the flock is active, sold or archived.',
            'steps' => [
                'Click <strong>Hatch New Flock</strong> to open the flock form.',
                'Enter the flock/batch name and code, choose the type (Broiler / Layer / Chicks), the house and the <strong>initial bird count</strong>.',
                'Save — the flock appears in the list with its current count.',
                'As birds die or are sold, record <strong>mortality</strong> on the flock so the live count stays accurate.',
                'When a flock is sold out, mark it <strong>Sold</strong> (or Archive old flocks) so it stops appearing in day-to-day lists.',
            ],
            'tips' => [
                'Accurate starting counts matter — every production and profit figure is built on them.',
                'Batches &amp; Houses and Daily Production all link back to flocks.',
            ],
        ],
        [
            'id' => 'production', 'pages' => ['production.php'], 'icon' => 'egg',
            'title' => 'Daily Production',
            'summary' => 'Log what each flock produced today: clean eggs, cracked eggs, meat weight in kg, and feed consumed.',
            'steps' => [
                'Click <strong>Log Daily Yield</strong>.',
                'Pick the batch/flock and the date (defaults to today).',
                'Enter clean eggs, cracked eggs, meat weight (kg) and feed consumed for that day.',
                'Save — the record is added to the daily production table and feeds the dashboard and analytics.',
            ],
            'tips' => [
                'Log every day for reliable charts and egg totals.',
                'Cracked eggs should also be captured in Losses &amp; Quality so they are not double counted as production.',
            ],
        ],
        [
            'id' => 'vaccinations', 'pages' => ['vaccinations.php'], 'icon' => 'syringe',
            'title' => 'Vaccinations',
            'summary' => 'Plan and track vaccines and treatments per batch, so no flock misses its schedule.',
            'steps' => [
                'Click <strong>Schedule Vaccine</strong>.',
                'Choose the batch, the age/day of the birds, the vaccine or treatment type and the date.',
                'Save as <strong>Scheduled</strong>.',
                'On the due date mark the record <strong>Completed</strong> — or <strong>Missed</strong> if it didn’t happen so you can reschedule.',
            ],
            'tips' => [
                'The calendar module can show upcoming vaccine dates at a glance.',
                'Keep notes on reactions or issues — history is useful at the end of a cycle.',
            ],
        ],
        [
            'id' => 'batches', 'pages' => ['batches.php'], 'icon' => 'warehouse',
            'title' => 'Batches & Houses',
            'summary' => 'Manage production batches and the houses they live in, and log each day’s record per batch (eggs by grade, mortality, feed).',
            'steps' => [
                'Use <strong>New Batch</strong> to open a batch, and <strong>Add House</strong> to register a house/building.',
                'Select a batch and click <strong>Log Today’s Record</strong> to enter that day’s eggs (Extra Large, B14, B15 etc.), mortality and feed.',
                'Records accumulate per batch and power the production and analytics screens.',
            ],
            'tips' => [
                'Assign each flock to a house so production is traceable by location.',
                'Keep one batch per distinct group of birds.',
            ],
        ],
        [
            'id' => 'health', 'pages' => ['health.php'], 'icon' => 'stethoscope',
            'title' => 'Health & Vet',
            'summary' => 'A veterinary diary: record deworming, vitamins, antibiotics, observation and other treatments, and see vaccination/mortality snapshots.',
            'steps' => [
                'Click <strong>New Health Record</strong> (via Quick Actions or the page header).',
                'Choose the <strong>record type</strong> — deworming, vitamins, antibiotic, observation or other.',
                'Select the batch, date, dosage/description and any notes, then save.',
                'Review the summary cards (vaccinations this month, mortality this week, treatments logged) to spot problems early.',
            ],
            'tips' => [
                'Use the notes field to record batch numbers of drugs for traceability.',
                'Serious issues are also a good candidate for a system alert.',
            ],
        ],
        [
            'id' => 'broiler', 'pages' => ['broiler.php'], 'icon' => 'scale',
            'title' => 'Broiler Workflow',
            'summary' => 'Weight-tracking for broiler batches: periodic weigh-ins tell you average weight per day so you know when birds are ready for market.',
            'steps' => [
                'Click <strong>Record Weigh-In</strong>.',
                'Pick the batch, the day number (age), the sample size (how many birds you weighed) and the average weight in kg.',
                'Save — the average grows over time and shows whether the flock is on target.',
            ],
            'tips' => [
                'Weigh a representative sample (e.g. 10 birds) rather than the whole flock.',
                'Compare weigh-ins to the breed standard to decide when to sell.',
            ],
        ],
        [
            'id' => 'hatchery', 'pages' => ['hatchery.php'], 'icon' => 'baby',
            'title' => 'Hatchery (DOC)',
            'summary' => 'Track hatching: how many eggs were set, how many were fertile, how many hatched, the hatch percentage and where the day-old chicks went.',
            'steps' => [
                'Click <strong>New Hatch Record</strong>.',
                'Enter eggs set, fertile eggs, hatched chicks, hatch %, destination (e.g. own brooding or sold) and any cost.',
                'Save — hatchery history is listed and exportable.',
            ],
            'tips' => [
                'Hatch % is a key hatchery health indicator — track it over time.',
                'Day-old chicks that go into the farm should become a new flock.',
            ],
        ],
        [
            'id' => 'feeding', 'pages' => ['feeding.php'], 'icon' => 'wheat',
            'title' => 'Feeding Program',
            'summary' => 'Two parts: weekly feed standards per bird type (grams per bird per day) and daily feed allocations logged per batch.',
            'steps' => [
                'Set up <strong>standards</strong> with “Add Week” — bird type, week number and grams per bird per day.',
                'Each day, click <strong>Record Feeding</strong>, choose the batch and enter the kg fed.',
                'The comparison between standard vs actual shows over- or under-feeding.',
            ],
            'tips' => [
                'Feed is usually the biggest cost — reconcile it against feed purchases in Stores &amp; Stock.',
            ],
        ],
        [
            'id' => 'losses', 'pages' => ['extras.php'], 'icon' => 'alert-triangle',
            'title' => 'Losses & Quality',
            'summary' => 'Two trackers: the egg loss log (broken, cracked, stolen, eaten, expired…) and raw-material quality tests (moisture, aflatoxin, purity…).',
            'steps' => [
                'On the <strong>Egg Losses</strong> tab click <strong>Record Loss</strong>.',
                'Choose the date, batch (optional), the loss <strong>type</strong> and crucially <strong>where it happened</strong> — During collection, On route (transport), At storage/farm or Other.',
                'Enter the quantity (eggs) and an optional value, add a reason, and save.',
                'Switch to the <strong>Quality Tests</strong> tab and click <strong>New Test</strong> to log a material test with a pass/borderline/fail result.',
            ],
            'tips' => [
                'Recording the stage (collection vs transport) shows you exactly where eggs are being damaged so you can fix it.',
                'Every loss log line appears in the export CSV for monthly reporting.',
            ],
        ],
    ],
    'Inventory & Stores' => [
        [
            'id' => 'products', 'pages' => ['products.php', 'hub_inventory.php'], 'icon' => 'package',
            'title' => 'Products Catalog',
            'summary' => 'Everything sold on the website and at the farm gate: live chicken, eggs, day-old chicks and feed. Each product has a price, stock level, type and visibility toggle.',
            'steps' => [
                'Click <strong>Add Product</strong> to create a product: name, type (Live Chicken / Eggs / Chicks / Feeds), price, stock, description and image.',
                'For feed bags, link a <strong>production recipe</strong> so producing a bag automatically deducts the ingredients from raw-materials stock.',
                'Toggle a product <strong>Active</strong> to show or hide it on the public shop instantly.',
                'Use the search box and type filter to find products, then Edit or Delete from the row actions.',
            ],
            'tips' => [
                'Keep stock levels honest — the website shows real availability.',
                'Use Quick Actions → “Export Products CSV” to back up or edit in bulk, then re-import via Bulk Import/Export.',
            ],
        ],
        [
            'id' => 'stores', 'pages' => ['stores.php', 'incoming_stock.php'], 'icon' => 'warehouse',
            'title' => 'Stores & Stock',
            'summary' => 'Raw materials (maize, soya, premix…) with live stock levels in kg, unit prices, suppliers and low-stock alerts.',
            'steps' => [
                'Review the stock table — quantity, unit, cost per kg, category and alert status.',
                'Use <strong>Record Movement</strong> / <strong>New Movement</strong> to add stock in (delivery) or remove stock out (usage/sale).',
                'Manage <strong>suppliers</strong> on the suppliers tab so purchase orders can reference them.',
                'Watch the alert list for materials below their minimum stock level and re-order via Procurement.',
            ],
            'tips' => [
                'Stock is measured in kg — legacy tons are converted automatically.',
                'Feed production and purchase orders update this stock automatically.',
            ],
        ],
        [
            'id' => 'feed-production', 'pages' => ['feed_production.php'], 'icon' => 'cog',
            'title' => 'Feed Production',
            'summary' => 'Build feed recipes from raw ingredients, then “produce” a batch — the system deducts the ingredients from raw-material stock and adds finished feed bags.',
            'steps' => [
                'Open the <strong>Recipes</strong> tab and click <strong>New Recipe</strong>.',
                'Name the finished feed (e.g. Layers Mash) and add each ingredient with its quantity per batch.',
                'Click <strong>Produce</strong> on a recipe, enter the number of bags, and confirm — ingredient stock is deducted and finished feed stock increases.',
                'Check the <strong>History</strong> tab to see every production run.',
            ],
            'tips' => [
                'Link the recipe to the matching product in Products so online feed sales stay in sync.',
            ],
        ],
        [
            'id' => 'egg-grading', 'pages' => ['egg_grading.php'], 'icon' => 'circle-dot',
            'title' => 'Egg Grading',
            'summary' => 'Grade collected eggs by size every day — Peewee, Small, Medium, Large, Extra Large, Jumbo, plus the farm’s B14/B15 grades and Cracked — and record crates and damaged eggs.',
            'steps' => [
                'Click <strong>New Grading</strong>.',
                'Pick the date, batch (optional) and the <strong>grade</strong> (size).',
                'Enter total eggs, crates, and any damaged count (subtracted from total), plus notes.',
                'Save — one row per day per grade, listed with an Export CSV button.',
            ],
            'tips' => [
                'Grading data feeds pricing decisions per size.',
                'Damaged eggs should also be logged as losses so they aren’t lost from the totals.',
            ],
        ],
    ],
    'Sales & Finance' => [
        [
            'id' => 'finance-hub', 'pages' => ['hub_finance.php', 'orders.php', 'sales.php', 'payments.php', 'expenses.php'], 'icon' => 'trending-up',
            'title' => 'Sales & Finance Hub',
            'summary' => 'The money nerve-centre: customer orders, sales summary, incoming payments, outgoing expenses and reports, all in tabs.',
            'steps' => [
                '<strong>Customer Orders</strong> tab: search orders, filter by status, and use <strong>Status</strong> to move an order pending → paid → processing → shipped → completed (or cancelled).',
                '<strong>Sales Summary</strong> tab: shows completed/paid orders as your sales register.',
                '<strong>Incoming Payments</strong> tab: click <strong>Log Payment</strong> to record cash, M-Pesa, bank transfer or cheque collections with status.',
                '<strong>Outgoing Expenses</strong> tab: embedded expenses list for farm spend.',
                '<strong>Reports</strong> tab: embedded analytics reports.',
            ],
            'tips' => [
                'Online customer orders appear here automatically.',
                'Use the Quick Actions menu to jump to Cashbook, LPO, Credit or Bulk Sales.',
            ],
        ],
        [
            'id' => 'lpo', 'pages' => ['lpo.php'], 'icon' => 'file-text',
            'title' => 'LPO & Invoicing',
            'summary' => 'Quotations, Local Purchase Orders (LPO) and invoices — one place for the whole document lifecycle, from a first quote to a paid invoice.',
            'steps' => [
                'Click <strong>New Document</strong> and choose the type: <strong>Quotation</strong>, <strong>LPO</strong> or <strong>Invoice</strong>.',
                'Fill the customer (name, phone, email, address), issue date and due/valid-until date.',
                'Add <strong>line items</strong> — description, quantity, unit, unit price — and optionally a tax rate % and discount; the total updates live.',
                'Save as <strong>Draft</strong>, then use the status selector in the table to move it along: sent → accepted → invoiced → paid (or cancelled).',
                'Click the <strong>eye</strong> action to open the print-ready document and press <strong>Print</strong> to produce a paper copy.',
                'Use <strong>Export CSV</strong> for a spreadsheet of all documents.',
            ],
            'tips' => [
                'A common flow: send a Quotation → customer raises an LPO → convert it to an Invoice → mark Paid when money lands.',
                'You can edit a document at any stage; totals are recomputed from the items.',
                'Every document appears in the Activity Logs audit trail.',
            ],
        ],
        [
            'id' => 'profit', 'pages' => ['profit.php'], 'icon' => 'calculator',
            'title' => 'Costs & Profit',
            'summary' => 'Log costs per batch (chicks, feed, drugs, labour, utilities, transport…) and see profit per batch and overall.',
            'steps' => [
                'Click <strong>Add Cost</strong>.',
                'Pick the batch, date, cost type, description, quantity/unit and total cost, plus how it was paid.',
                'Save — the batch cost summary and profit calculation update.',
            ],
            'tips' => [
                'Capture every cost, including small ones, or profit will look better than it is.',
            ],
        ],
        [
            'id' => 'cashbook', 'pages' => ['cashbook.php'], 'icon' => 'book-open',
            'title' => 'Cashbook (Money Book)',
            'summary' => 'A simple money in / money out ledger with daily balances — the farm’s day-to-day cash record.',
            'steps' => [
                'Click <strong>Money In</strong> to record income (source, amount, method, date, notes).',
                'Click <strong>Money Out</strong> to record spending the same way.',
                'The table shows today’s in/out and the running balance.',
                'Use <strong>Export CSV</strong> to download the ledger.',
            ],
            'tips' => [
                'Use it daily and it reconciles with the Sales & Finance hub at month end.',
            ],
        ],
        [
            'id' => 'credit', 'pages' => ['credit.php'], 'icon' => 'credit-card',
            'title' => 'Customer Credit',
            'summary' => 'Sell on credit and track what customers owe: balances, due dates and overdue amounts.',
            'steps' => [
                'Click <strong>Record Credit Sale</strong>.',
                'Pick or create the customer, enter the amount, due date and description.',
                'When the customer pays, record a <strong>payment</strong> against the credit — the balance reduces automatically.',
                'Watch the overdue list so money owed doesn’t get forgotten.',
            ],
            'tips' => [
                'Overdue totals also show on the dashboard.',
            ],
        ],
        [
            'id' => 'procurement', 'pages' => ['purchase_orders.php'], 'icon' => 'truck',
            'title' => 'Procurement (Purchase Orders)',
            'summary' => 'Order raw materials from suppliers, then receive them — receiving automatically adds the quantity to stores stock.',
            'steps' => [
                'Click <strong>New Order</strong>, choose the supplier, order date and expected delivery.',
                'Add the materials with quantity, unit and unit price — the PO total updates.',
                'Save the order (draft/sent/confirmed).',
                'When goods arrive, mark the order <strong>Received</strong> — stock is added automatically.',
                'Manage suppliers on the <strong>Suppliers</strong> tab (name, contact, phone, email, lead time).',
            ],
            'tips' => [
                'Receiving recalculates moving average costs automatically.',
            ],
        ],
        [
            'id' => 'daily-sales', 'pages' => ['daily_sales.php'], 'icon' => 'clipboard-check',
            'title' => 'Daily Reconciliation',
            'summary' => 'Each day, match what was produced and sold: crates sold, sales amount and notes — a daily close-out.',
            'steps' => [
                'Click <strong>Record Daily Sales</strong>.',
                'Enter the date, crates sold, sales total and any notes.',
                'Save — the summary cards (today, this week) update.',
            ],
            'tips' => [
                'Do this at close of day to keep daily totals trustworthy.',
            ],
        ],
        [
            'id' => 'bulk-sales', 'pages' => ['bulk_sales.php'], 'icon' => 'shopping-cart',
            'title' => 'Bulk Sales & Walk-in',
            'summary' => 'Quick sales for walk-in customers and bulk buyers, with a customer list so repeat buyers are one click away.',
            'steps' => [
                'Click <strong>New Sale</strong> — pick or add the customer, date, items/amount and payment method.',
                'Use <strong>Add Customer</strong> to register walk-in customers for future sales.',
                'The sales table and Export CSV give you the day’s bulk totals.',
            ],
            'tips' => [
                'Combine with Customer Credit when a buyer takes goods on credit.',
            ],
        ],
    ],
    'Reports & Tools' => [
        [
            'id' => 'analytics', 'pages' => ['analytics.php', 'reports.php'], 'icon' => 'bar-chart-2',
            'title' => 'Analytics & Charts',
            'summary' => 'Visual dashboards: KPI cards and charts for sales, production, costs and performance.',
            'steps' => [
                'Read the KPI cards at the top for headline numbers.',
                'Use the charts to compare periods and spot trends.',
                'Click the refresh button to reload the latest data.',
            ],
            'tips' => [
                'Charts only reflect what has been logged — keep the modules fed with data.',
            ],
        ],
        [
            'id' => 'import-export', 'pages' => ['bulk_import_export.php'], 'icon' => 'upload',
            'title' => 'Bulk Import / Export',
            'summary' => 'Move data in and out as CSV: download backups and templates, or mass-create products, customers, raw materials, flocks and expenses.',
            'steps' => [
                'The <strong>stats row</strong> shows live record counts (products, orders, customers, flocks).',
                '<strong>Export</strong> — pick an entity card and click Export to download a clean CSV of that data.',
                '<strong>Import</strong> — choose the entity type, then drag a CSV onto the dropzone (or browse).',
                'The <strong>format panel</strong> shows the exact columns expected; use <strong>Download CSV template</strong> to get a headers-only file to fill in.',
                'Click Import — the records are inserted and you get a count.',
            ],
            'tips' => [
                'Importing a product/customer with an existing name/email updates it instead of duplicating.',
                'Always download a template first so the columns match exactly.',
            ],
        ],
    ],
    'Team & Messages' => [
        [
            'id' => 'staff', 'pages' => ['staff.php'], 'icon' => 'users',
            'title' => 'Staff',
            'summary' => 'Create and manage staff accounts (who logs into the admin panel) with their roles and contact details.',
            'steps' => [
                'Click <strong>Add Staff</strong> and fill username, email, names, phone, role and an initial password.',
                'Save — the staff member can now log in with their own account.',
                'Use <strong>Edit</strong> to change details, role or reset their password.',
            ],
            'tips' => [
                'Choose the role carefully — it decides which modules the person can open (see Roles &amp; Permissions).',
            ],
        ],
        [
            'id' => 'users', 'pages' => ['users.php'], 'icon' => 'user-cog',
            'title' => 'Customers & User Accounts',
            'summary' => 'Every account in the system: edit your own admin profile, create customer/team accounts, change roles, reset passwords and remove accounts.',
            'steps' => [
                '<strong>My Admin Account</strong> — update your own name, email and password.',
                '<strong>Add User</strong> — create an account with a username, email, initial password and role (Customer, Sales Staff, Stock Manager, Farm Manager, Super Admin).',
                'Change a user’s <strong>role</strong> from the dropdown in the list.',
                'Use the <strong>key</strong> icon to reset a password, or the <strong>trash</strong> icon to delete an account (never your own).',
            ],
            'tips' => [
                'Roles decide module access — fine-tune them under Settings → Roles &amp; Permissions.',
            ],
        ],
        [
            'id' => 'tasks', 'pages' => ['tasks.php'], 'icon' => 'check-square',
            'title' => 'Tasks',
            'summary' => 'Assign farm tasks with a type, date and owner so nothing gets forgotten.',
            'steps' => [
                'Click <strong>Add Task</strong>.',
                'Enter the task, type, date and who it is assigned to.',
                'Mark tasks done as they are completed; the list shows open vs completed.',
            ],
            'tips' => [
                'Tasks appear on the Calendar by due date.',
            ],
        ],
        [
            'id' => 'messages', 'pages' => ['messages.php'], 'icon' => 'message-square',
            'title' => 'Messages',
            'summary' => 'Internal messaging: compose and send messages between staff accounts, with an inbox.',
            'steps' => [
                'Click <strong>Compose</strong>, pick the recipient, subject and message, and send.',
                'Read replies in the inbox; messages show status (read/unread).',
            ],
            'tips' => [
                'Messages stay inside the system — no need for personal phones.',
            ],
        ],
    ],
    'Settings' => [
        [
            'id' => 'calendar', 'pages' => ['calendar.php'], 'icon' => 'calendar',
            'title' => 'Calendar',
            'summary' => 'A month/week/day view of farm tasks and health/vaccination schedules.',
            'steps' => [
                'Switch between <strong>Month / Week / Day</strong> views.',
                'Click an event to see its details and due date.',
                'Add events from Tasks and Vaccinations automatically; use the page to keep an eye on what’s coming.',
            ],
            'tips' => [
                'Use it at the start of each week to plan feeding, vaccination and sales activity.',
            ],
        ],
        [
            'id' => 'dropdowns', 'pages' => ['dropdowns.php'], 'icon' => 'list',
            'title' => 'Dropdowns',
            'summary' => 'Edit the options inside system dropdowns (product categories, units, statuses, roles, chicken sizes…) without touching code.',
            'steps' => [
                'Pick a group from the list (e.g. Product Categories, Units, User Roles).',
                'Add a new option with <strong>Add Option</strong> (label + value + sort order).',
                'Edit, enable/disable, or delete custom options per row.',
            ],
            'tips' => [
                'Protected system options cannot be deleted — disable them instead to keep the backend stable.',
            ],
        ],
        [
            'id' => 'settings', 'pages' => ['settings.php', 'hub_settings.php'], 'icon' => 'settings',
            'title' => 'App Settings',
            'summary' => 'General farm configuration: farm name, contact email and phone used across the system.',
            'steps' => [
                'Edit the fields (farm name, contact email, phone number).',
                'Click <strong>Save Changes</strong> to apply them.',
                'The hub also links to Dropdowns, Activity Logs and Roles &amp; Permissions.',
            ],
            'tips' => [
                'These details appear on printed documents and the public site, so keep them current.',
            ],
        ],
        [
            'id' => 'logs', 'pages' => ['logs.php'], 'icon' => 'history',
            'title' => 'Activity Logs',
            'summary' => 'A complete audit trail of everything that happens in the system — who did what and when.',
            'steps' => [
                'Open the list — every major action (logins, product changes, orders, LPOs, imports…) is recorded with the user and timestamp.',
                'Use it to investigate changes, track who recorded what, and audit the team.',
            ],
            'tips' => [
                'Logs are append-only — they can’t be edited, which keeps them trustworthy.',
            ],
        ],
        [
            'id' => 'permissions', 'pages' => ['permissions.php'], 'icon' => 'shield',
            'title' => 'Roles & Permissions',
            'summary' => 'Control which modules each role can open: a matrix of roles × modules with separate “view” and “edit” checkboxes.',
            'steps' => [
                'Open the card for the role you want to change (Super Admin, Farm Manager, Stock Manager, Sales Staff, Customer).',
                'Click <strong>Show / hide modules</strong> to expand the matrix.',
                'Tick <strong>view</strong> to let the role open a module, and <strong>edit</strong> to let it make changes.',
                'Click <strong>Save</strong> for that role — the change applies immediately to every user with that role.',
            ],
            'tips' => [
                'Super Admin always has full access (its checkboxes are locked).',
                'A denied module disappears from that role’s sidebar, and direct links redirect to the dashboard with a notice.',
                'Create accounts and assign roles under Team &amp; Messages → Customers &amp; User Accounts.',
            ],
        ],
    ],
];

/* ── Build a script-name → guide-id map for auto-detection ── */
$guidePageMap = [];
foreach ($guideSections as $_group) {
    foreach ($_group as $_mod) {
        foreach ($_mod['pages'] as $_pg) {
            $guidePageMap[$_pg] = $_mod['id'];
        }
    }
}
$guideOrder = [];
foreach ($guideSections as $_group) {
    foreach ($_group as $_mod) {
        $guideOrder[] = $_mod['id'];
    }
}
?>
<!-- Premium Interactive System Walkthrough Guide Modal -->
<div id="system-guide-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: #ffffff; width: 94%; max-width: 900px; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; transform: translateY(20px); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); max-height: 88vh;">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--admin-primary) 0%, #064e3b 100%); padding: 20px 26px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; position: relative; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.3rem; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                    System Walkthrough Guide
                </h3>
                <p style="margin: 4px 0 0 0; font-size: 0.83rem; color: rgba(255, 255, 255, 0.82);">Every module, every flow — search for a module or pick it from the list.</p>
            </div>
            <button id="close-system-guide" aria-label="Close guide" style="background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #ffffff; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <!-- Guide Content Body -->
        <div style="display: flex; flex: 1; min-height: 400px; overflow: hidden; background: #f8fafc;">

            <!-- Left: grouped module navigation -->
            <div style="width: 250px; border-right: 1px solid rgba(203, 213, 225, 0.8); background: #ffffff; padding: 12px 10px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; flex-shrink: 0;">
                <input id="guide-search" type="text" placeholder="Search modules…" style="padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; outline: none; width: 100%; box-sizing: border-box;">
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                    <?php $gi = 0; foreach ($guideSections as $gLabel => $mods): ?>
                    <div class="guide-group">
                        <div style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; padding: 2px 8px 4px;"><?= htmlspecialchars($gLabel, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php foreach ($mods as $mod): ?>
                        <button class="guide-nav-btn<?= $gi === 0 ? ' active' : '' ?>" onclick="showGuide('<?= $mod['id'] ?>')" data-guide-id="<?= $mod['id'] ?>" style="display: flex; align-items: center; gap: 9px; width: 100%; padding: 8px 10px; border: none; background: none; border-radius: 7px; text-align: left; font-weight: 600; font-size: 0.83rem; color: #475569; cursor: pointer; transition: all 0.15s;">
                            <i data-lucide="<?= $mod['icon'] ?>" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?></span>
                        </button>
                        <?php $gi++; endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: module detail -->
            <div id="guide-panes" style="flex: 1; padding: 26px 28px; overflow-y: auto; min-width: 0;">
                <?php $pi = 0; foreach ($guideSections as $_group): foreach ($_group as $mod): ?>
                <div class="guide-step-pane" data-pane-id="<?= $mod['id'] ?>" style="display: <?= $pi === 0 ? 'block' : 'none' ?>;">
                    <h4 style="margin: 0 0 8px 0; font-size: 1.2rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 9px;">
                        <span style="width: 34px; height: 34px; border-radius: 9px; background: rgba(27,94,32,0.08); color: var(--admin-primary); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i data-lucide="<?= $mod['icon'] ?>" style="width: 18px; height: 18px;"></i></span>
                        <?= htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>
                    <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 0.92rem; color: #475569;"><?= $mod['summary'] ?></p>

                    <div style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-primary); margin-bottom: 8px;">How it works</div>
                    <ol style="margin: 0 0 18px 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem; color: #334155; line-height: 1.55;">
                        <?php foreach ($mod['steps'] as $step): ?>
                        <li><?= $step ?></li>
                        <?php endforeach; ?>
                    </ol>

                    <?php if (!empty($mod['tips'])): ?>
                    <div style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706; margin-bottom: 8px;">Good to know</div>
                    <ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 7px; font-size: 0.88rem; color: #78350f; line-height: 1.5; background: #fffbeb; border: 1px solid #fde68a; border-radius: 9px; padding: 14px 14px 14px 30px;">
                        <?php foreach ($mod['tips'] as $tip): ?>
                        <li><?= $tip ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php $pi++; endforeach; endforeach; ?>
            </div>
        </div>

        <!-- Footer / Action Controls -->
        <div style="background: #ffffff; border-top: 1px solid rgba(203, 213, 225, 0.8); padding: 14px 26px; display: flex; justify-content: space-between; align-items: center;">
            <button id="guide-prev" onclick="guideMove(-1)" class="btn btn-outline btn-sm" style="display: flex; align-items: center; gap: 6px;">
                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i> Prev
            </button>
            <div style="font-size: 0.82rem; color: #64748b; font-weight: 600;"><span id="guide-count">1</span> of <?= count($guideOrder) ?></div>
            <button id="guide-next" onclick="guideMove(1)" class="btn btn-primary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                Next <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

    </div>
</div>

<style>
.guide-nav-btn:hover { color: var(--admin-primary) !important; background: rgba(27, 94, 32, 0.05) !important; }
.guide-nav-btn.active { color: #ffffff !important; background: var(--admin-primary) !important; }
@media (max-width: 700px) {
    #system-guide-modal > div { flex-direction: column; }
    #system-guide-modal .guide-group + div { display: none; }
}
</style>

<script>
/* Ordered ids for prev/next + auto-detection */
const guideOrder = <?= json_encode($guideOrder) ?>;
const guidePageMap = <?= json_encode($guidePageMap) ?>;
let currentGuide = guideOrder[0] || null;

function showGuide(id, opts) {
    if (!guideOrder.includes(id)) id = guideOrder[0];
    currentGuide = id;
    document.querySelectorAll('.guide-step-pane').forEach(p => {
        p.style.display = p.getAttribute('data-pane-id') === id ? 'block' : 'none';
    });
    document.querySelectorAll('.guide-nav-btn').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-guide-id') === id);
    });
    const idx = guideOrder.indexOf(id) + 1;
    document.getElementById('guide-count').textContent = idx;
    document.getElementById('guide-prev').disabled = idx === 1;
    document.getElementById('guide-next').innerHTML = idx === guideOrder.length
        ? 'Finish <i data-lucide="check" style="width: 16px; height: 16px;"></i>'
        : 'Next <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    // Scroll the active nav item into view inside the left column
    const btn = document.querySelector('.guide-nav-btn.active');
    if (btn) btn.scrollIntoView({ block: 'nearest' });
}

function guideMove(delta) {
    const idx = guideOrder.indexOf(currentGuide) + delta;
    if (idx >= guideOrder.length) { closeGuideModal(); return; }
    showGuide(guideOrder[Math.max(0, idx)]);
}

function openGuideModal() {
    const modal = document.getElementById('system-guide-modal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.firstElementChild.style.transform = 'translateY(0)';
    }, 10);
    // Auto-detect the current page and open its module.
    const path = window.location.pathname;
    const page = path.split('/').pop() || '';
    showGuide(guidePageMap[page] || 'dashboard');
    // Reset search each time
    const search = document.getElementById('guide-search');
    if (search) { search.value = ''; filterGuide(''); }
}

function filterGuide(q) {
    q = (q || '').toLowerCase().trim();
    document.querySelectorAll('.guide-nav-btn').forEach(b => {
        const text = (b.textContent || '').toLowerCase();
        b.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.guide-group').forEach(g => {
        const visible = [...g.querySelectorAll('.guide-nav-btn')].some(b => b.style.display !== 'none');
        g.style.display = visible ? '' : 'none';
    });
}

function closeGuideModal() {
    const modal = document.getElementById('system-guide-modal');
    modal.style.opacity = '0';
    if (modal.firstElementChild) modal.firstElementChild.style.transform = 'translateY(20px)';
    setTimeout(() => { modal.style.display = 'none'; }, 300);
}

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('open-system-guide');
    if (trigger) trigger.addEventListener('click', openGuideModal);

    const closeBtn = document.getElementById('close-system-guide');
    if (closeBtn) closeBtn.addEventListener('click', closeGuideModal);

    const search = document.getElementById('guide-search');
    if (search) search.addEventListener('input', () => filterGuide(search.value));

    // Never auto-open: the guide stays available via the help button only.
    const modal = document.getElementById('system-guide-modal');
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) closeGuideModal(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display !== 'none') closeGuideModal();
        });
    }
});
</script>
