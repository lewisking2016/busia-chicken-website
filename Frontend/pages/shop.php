<?php
/**
 * E-Commerce Shop Page
 * Premium Minimalist Redesign
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

<!-- Shop Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-image: url('/Frontend/images/adbg.png'); background-size: cover; background-position: center; background-repeat: no-repeat; border-bottom: 1px solid rgba(255,255,255,0.12); position: relative;">
    <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.35);"></div>
    <div class="container" style="position: relative; z-index: 1; text-align: center; color: #FFFFFF;">
        <h1 style="margin-bottom: var(--space-sm); color: #FFFFFF;">Online Shop</h1>
        <p style="font-size: 1.125rem; color: rgba(255,255,255,0.9);">Browse and purchase premium chicken products and feeds.</p>
    </div>
</section>

<!-- Nutrition Section -->
<section style="padding: var(--space-4xl) 0; background-color: #F8FAFC;">
    <div class="container" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3xl); align-items: center;">
        <div>
            <div style="display: inline-block; padding: 0.5rem 1rem; border-radius: var(--radius-pill); background: var(--primary); color: white; font-weight: 700; margin-bottom: var(--space-lg);">
                Nutrition
            </div>
            <h2 style="margin-bottom: var(--space-md);">Premium Animal Feeds for Kenyan Poultry</h2>
            <p style="margin-bottom: var(--space-lg); color: var(--gray-600); font-size: 1rem; max-width: 640px; line-height: 1.75;">
                Specially formulated animal feeds designed for optimal growth, productivity, and health. Each formula is balanced with essential nutrients, amino acids, and vitamins for maximum performance.
            </p>
            <ul style="list-style: none; padding: 0; margin: 0 0 var(--space-3xl) 0; display: grid; gap: 0.75rem;">
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Starter, Grower, and Finisher feeds</li>
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Premium Layer Mash with calcium</li>
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Available in 50kg bulk bags</li>
            </ul>
            <a href="/Frontend/pages/shop.php?category=feeds" class="btn btn-primary">Shop Feeds</a>
        </div>
        <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-lg);">
            <img src="/Frontend/images/download (8).png" alt="Premium animal feeds" style="width: 100%; display: block; object-fit: cover; min-height: 400px;">
        </div>
    </div>
</section>

<!-- Product Showcase Section -->
<section style="padding: var(--space-4xl) 0; background-color: #FFFFFF;">
    <div class="container" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3xl); align-items: center;">
        <div>
            <div style="display: inline-block; padding: 0.5rem 1rem; border-radius: var(--radius-pill); background: var(--primary); color: white; font-weight: 700; margin-bottom: var(--space-lg);">
                Product Section
            </div>
            <h2 style="margin-bottom: var(--space-md);">Featured Poultry Products</h2>
            <p style="margin-bottom: var(--space-lg); color: var(--gray-600); font-size: 1rem; max-width: 640px; line-height: 1.75;">
                Discover reliable poultry products and feed blends that support Kenyan farmers with strong growth, high productivity, and healthy stock.
            </p>
            <ul style="list-style: none; padding: 0; margin: 0 0 var(--space-3xl) 0; display: grid; gap: 0.75rem;">
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Trusted live chicken and chick breeds</li>
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Balanced starter and grower feed formulas</li>
                <li style="display: flex; gap: 0.75rem; align-items: flex-start;"><span style="color: var(--primary); font-weight: 700;">•</span> Fresh eggs and farm-ready products</li>
            </ul>
            <a href="/Frontend/pages/shop.php#products" class="btn btn-outline">Browse Products</a>
        </div>
        <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-lg);">
            <img src="/Frontend/images/Growers Mash.png" alt="Featured poultry products" style="width: 100%; display: block; object-fit: cover; min-height: 400px;">
        </div>
    </div>
</section>

<!-- Shop Content -->
<section style="padding: var(--space-3xl) 0; background-color: var(--white);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: var(--space-3xl);">
            
            <!-- Sidebar: Filters -->
            <aside>
                <div style="position: sticky; top: 100px;">
                    <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-lg); padding-bottom: var(--space-sm); border-bottom: 1px solid var(--gray-200);">
                        <i data-lucide="filter" style="width: 20px; height: 20px; color: var(--primary);"></i>
                        <h3 style="margin: 0; font-size: 1.25rem;">Filters</h3>
                    </div>

                    <!-- Category Filter -->
                    <div style="margin-bottom: var(--space-xl);">
                        <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: var(--space-md); color: var(--dark);">Product Type</h4>
                        <form class="product-filters">
                        <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                            <?php 
                            require_once __DIR__ . '/../../Backend/api/dropdowns.php';
                            $types = getSystemDropdownOptions('product_types');
                            foreach ($types as $t):
                            ?>
                            <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; color: var(--gray-600); font-size: 0.95rem;">
                                <input type="checkbox" name="type" value="<?php echo htmlspecialchars($t['option_value']); ?>" class="form-checkbox"> <?php echo htmlspecialchars($t['option_label']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Availability Filter -->
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: var(--space-md); color: var(--dark);">Availability</h4>
                        <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                            <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; color: var(--gray-600); font-size: 0.95rem;">
                                <input type="checkbox" name="availability" value="in-stock" class="form-checkbox" checked> In Stock
                            </label>
                            <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; color: var(--gray-600); font-size: 0.95rem;">
                                <input type="checkbox" name="availability" value="preorder" class="form-checkbox"> Pre-Order
                            </label>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline" style="width: 100%; margin-top: var(--space-xl); font-size: 0.9rem;" onclick="document.querySelectorAll('.product-filters input').forEach(i=>i.checked=false);document.querySelectorAll('.product-card').forEach(c=>c.style.display='');document.getElementById('products-count').textContent = document.querySelectorAll('.product-card').length;">Reset Filters</button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <div>
                <!-- Sort & View Options -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); padding-bottom: var(--space-md); border-bottom: 1px solid var(--gray-200);">
                    <p style="color: var(--gray-600); margin: 0; font-size: 0.95rem;">Showing <span id="products-count"><?php echo count($products); ?></span> products</p>
                    <div style="display: flex; gap: var(--space-md); align-items: center;">
                        <label style="font-size: 0.9rem; color: var(--gray-600);">Sort by:</label>
                        <select style="padding: 0.5rem 2rem 0.5rem 0.5rem; border-radius: var(--radius-sm); border: 1px solid var(--gray-200); background-color: var(--white); font-size: 0.9rem; color: var(--dark); cursor: pointer; outline: none;">
                            <option>Newest Arrivals</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="product-grid">
                    <?php 
                    if (!empty($products)) {
                        foreach ($products as $index => $product):
                            // Fallback image logic
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
                        <div class="product-image">
                            <?php if ($inStock): ?>
                                <span class="product-badge">In Stock</span>
                            <?php else: ?>
                                <span class="product-badge" style="color: var(--gray-600);">Out of Stock</span>
                            <?php endif; ?>
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-body">
                            <h4 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="product-description" style="color: var(--gray-600);"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 80) . '...', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="product-meta">
                                <span class="product-price">KES <?php echo number_format((float)$product['price'], 0); ?></span>
                            </div>
                            <button class="add-to-cart-btn btn <?php echo $inStock ? 'btn-primary' : 'btn-outline'; ?>" data-id="<?php echo $product['id']; ?>" data-qty="1" style="width: 100%; justify-content: center;" <?php echo !$inStock ? 'disabled' : ''; ?>>
                                <i data-lucide="shopping-cart" style="width: 18px; height: 18px;"></i>
                                <?php echo $inStock ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    } else {
                    ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: var(--space-4xl) 0;">
                        <i data-lucide="package-x" style="width: 48px; height: 48px; color: var(--gray-400); margin-bottom: var(--space-md);"></i>
                        <p style="color: var(--gray-600); font-size: 1.125rem;">No products available at the moment.</p>
                    </div>
                    <?php } ?>
                </div>

                <!-- Pagination -->
                <?php if (count($products) > 12): ?>
                <div style="display: flex; justify-content: center; gap: var(--space-sm); margin-top: var(--space-4xl);">
                    <button class="btn btn-outline" style="padding: 0.5rem 1rem;">Previous</button>
                    <button class="btn btn-primary" style="padding: 0.5rem 1rem;">1</button>
                    <button class="btn btn-outline" style="padding: 0.5rem 1rem;">2</button>
                    <button class="btn btn-outline" style="padding: 0.5rem 1rem;">Next</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>