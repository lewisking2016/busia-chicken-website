<?php
/**
 * E-Commerce Shop Page � Premium Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Shop - Buy Chicken Products & Feeds | Busia Chicken Farm';

include '../includes/header.php';

// Get database connection and load products via shared source
$pdo = getDB();
$products = [];
require_once __DIR__ . '/../includes/product_source.php';
$products = loadDisplayProducts($pdo);
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/adbg.png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Shop</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">The <em>Shop</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Browse and purchase premium chicken products, fresh eggs and feeds � delivered to your door.</p>
    </div>
</section>

<!-- Shop Content -->
<section class="section-pad" style="background:var(--cream-50);" id="products">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="eyebrow">Our Products</span>
            <h2 class="section-title">Shop <em>Poultry &amp; Feeds</em></h2>
            <p class="lead">Fresh eggs, day-old chicks, live birds and premium feeds — ready to order and delivered to your door.</p>
        </div>

        <div class="shop-layout" style="display:grid;grid-template-columns:250px 1fr;gap:clamp(1.6rem,3vw,3rem);align-items:start;">

            <!-- Sidebar: Filters -->
            <aside data-reveal="left">
                <div class="p-card" style="position:sticky;top:calc(var(--nav-h) + 20px);">
                    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1.2rem;padding-bottom:0.8rem;border-bottom:1px solid rgba(11,61,23,0.1);">
                        <i data-lucide="filter" style="width:20px;height:20px;color:var(--brand-600);"></i>
                        <h3 style="margin:0;font-size:1.15rem;">Filters</h3>
                    </div>

                    <div style="margin-bottom:1.6rem;">
                        <h4 style="font-size:0.85rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--brand-900);margin-bottom:0.9rem;">Product Type</h4>
                        <form class="product-filters">
                        <div style="display:flex;flex-direction:column;gap:0.6rem;">
                            <?php
                            require_once __DIR__ . '/../../Backend/api/dropdowns.php';
                            $types = getSystemDropdownOptions('product_types');
                            foreach ($types as $t):
                            ?>
                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;color:var(--gray-600);font-size:0.92rem;">
                                <input type="checkbox" name="type" value="<?php echo htmlspecialchars($t['option_value']); ?>" class="form-checkbox" style="accent-color:var(--brand-600);"> <?php echo htmlspecialchars($t['option_label']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom:1.6rem;">
                        <h4 style="font-size:0.85rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--brand-900);margin-bottom:0.9rem;">Availability</h4>
                        <div style="display:flex;flex-direction:column;gap:0.6rem;">
                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;color:var(--gray-600);font-size:0.92rem;">
                                <input type="checkbox" name="availability" value="in-stock" class="form-checkbox" checked style="accent-color:var(--brand-600);"> In Stock
                            </label>
                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;color:var(--gray-600);font-size:0.92rem;">
                                <input type="checkbox" name="availability" value="preorder" class="form-checkbox" style="accent-color:var(--brand-600);"> Pre-Order
                            </label>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:0.8rem;font-size:0.9rem;" onclick="document.querySelectorAll('.product-filters input').forEach(i=>i.checked=false);document.querySelectorAll('.product-card').forEach(c=>c.style.display='');document.getElementById('products-count').textContent = document.querySelectorAll('.product-card').length;">Reset Filters</button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <div data-reveal="right">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.6rem;padding-bottom:0.9rem;border-bottom:1px solid rgba(11,61,23,0.1);flex-wrap:wrap;gap:0.8rem;">
                    <p style="color:var(--gray-600);margin:0;font-size:0.95rem;">Showing <strong id="products-count"><?php echo count($products); ?></strong> products</p>
                    <div style="display:flex;gap:0.8rem;align-items:center;">
                        <label style="font-size:0.9rem;color:var(--gray-600);">Sort by:</label>
                        <select style="padding:0.5rem 2rem 0.5rem 0.8rem;border-radius:4px;border:1px solid rgba(11,61,23,0.15);background-color:#fff;font-size:0.9rem;color:var(--dark);cursor:pointer;outline:none;">
                            <option>Newest Arrivals</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <div class="product-grid">
                    <?php
                    if (!empty($products)) {
                        foreach ($products as $index => $product):
                            $img = $product['img'] ?? '';
                            if (!$img) {
                                $img = match($product['product_type'] ?? 'feed') {
                                    'feed' => '/Frontend/images/Growers Mash.png',
                                    'eggs' => '/Frontend/images/download (3).png',
                                    'chicks' => '/Frontend/images/download (7).png',
                                    'live_chicken' => '/Frontend/images/download (4).png',
                                    default => '/Frontend/images/Chick Starter Crumbs.png'
                                };
                            }
                            $stock = $product['stock_quantity'] ?? 0;
                            $inStock = $stock > 0;
                    ?>
                        <div class="product-card" data-id="<?php echo $product['id']; ?>" data-type="<?php echo htmlspecialchars($product['product_type'] ?? '', ENT_QUOTES); ?>" data-instock="<?php echo $inStock ? '1' : '0'; ?>">
                        <a href="/Frontend/pages/product-detail.php?id=<?php echo $product['id']; ?>" style="display:block;text-decoration:none;color:inherit;">
                            <div class="product-image">
                                <?php if ($inStock): ?>
                                    <span class="product-badge">In Stock</span>
                                <?php else: ?>
                                    <span class="product-badge" style="color:var(--gray-600);">Out of Stock</span>
                                <?php endif; ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                            </div>
                        </a>
                        <div class="product-body">
                            <h4 class="product-name">
                                <a href="/Frontend/pages/product-detail.php?id=<?php echo $product['id']; ?>" style="color:inherit;text-decoration:none;font-weight:inherit;">
                                    <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h4>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 80) . '...', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="product-meta">
                                <span class="product-price">KES <?php echo number_format((float)$product['price'], 0); ?></span>
                            </div>
                            <button class="add-to-cart-btn btn <?php echo $inStock ? 'btn-primary' : 'btn-outline'; ?>" data-id="<?php echo $product['id']; ?>" data-qty="1" style="width:100%;justify-content:center;" <?php echo !$inStock ? 'disabled' : ''; ?>>
                                <i data-lucide="shopping-cart" style="width:18px;height:18px;"></i>
                                <?php echo $inStock ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    } else {
                    ?>
                    <div style="grid-column:1/-1;text-align:center;padding:var(--space-4xl) 0;">
                        <i data-lucide="package-x" style="width:48px;height:48px;color:var(--gray-400);margin-bottom:var(--space-md);"></i>
                        <p style="color:var(--gray-600);font-size:1.125rem;">No products available at the moment.</p>
                    </div>
                    <?php } ?>
                </div>

                <?php if (count($products) > 12): ?>
                <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:3rem;">
                    <button class="btn btn-outline" style="padding:0.5rem 1rem;">Previous</button>
                    <button class="btn btn-primary" style="padding:0.5rem 1rem;">1</button>
                    <button class="btn btn-outline" style="padding:0.5rem 1rem;">2</button>
                    <button class="btn btn-outline" style="padding:0.5rem 1rem;">Next</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
