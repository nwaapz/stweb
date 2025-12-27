<?php
/**
 * User Wishlist
 * علاقه‌مندی‌های کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();
$wishlistItems = getWishlist($user['id']);

$pageTitle = 'علاقه‌مندی‌ها';
include 'header.php';
?>

<div class="block-space block-space--layout--after-header"></div>

<div class="block">
    <div class="container container--max--xl">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-12 col-lg-3 mb-4">
                <?php include 'sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-heart"></i> علاقه‌مندی‌ها</span>
                        <span class="badge bg-danger"><?= count($wishlistItems) ?> محصول</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($wishlistItems)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-heart fa-3x mb-3"></i>
                                <p>لیست علاقه‌مندی‌های شما خالی است</p>
                                <a href="../shop-grid-4-columns-sidebar.html" class="btn btn-primary">
                                    مشاهده محصولات
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($wishlistItems as $item): ?>
                                    <div class="col-md-6 col-lg-4" data-product-id="<?= $item['product_id'] ?>">
                                        <div class="card h-100">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= $item['image_url'] ?>" class="card-img-top" alt=""
                                                    style="height: 150px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <a href="../product-full.html?product=<?= $item['product_id'] ?>"
                                                        class="text-dark">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                </h6>
                                                <p class="card-text">
                                                    <?php if ($item['has_discount']): ?>
                                                        <del class="text-muted small"><?= $item['formatted_price'] ?></del><br>
                                                        <span class="text-danger"><?= $item['formatted_effective_price'] ?></span>
                                                    <?php else: ?>
                                                        <?= $item['formatted_price'] ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary flex-grow-1 add-to-cart-btn">
                                                        <i class="fas fa-shopping-cart"></i> افزودن به سبد
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger remove-wishlist-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>

<script>
    $(document).ready(function () {
        // Add to cart
        $('.add-to-cart-btn').click(function () {
            const container = $(this).closest('[data-product-id]');
            const productId = container.data('product-id');

            $.ajax({
                url: '../backend/api/cart.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId, quantity: 1 }),
                success: function (response) {
                    if (response.success) {
                        alert('محصول به سبد خرید اضافه شد');
                    }
                }
            });
        });

        // Remove from wishlist
        $('.remove-wishlist-btn').click(function () {
            if (!confirm('آیا از حذف این محصول مطمئن هستید؟')) return;

            const container = $(this).closest('[data-product-id]');
            const productId = container.data('product-id');

            $.ajax({
                url: '../backend/api/wishlist.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId }),
                success: function (response) {
                    if (response.success) {
                        container.fadeOut(300, function () { $(this).remove(); });
                    }
                }
            });
        });
    });
</script>