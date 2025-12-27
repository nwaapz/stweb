<?php
/**
 * User Cart
 * سبد خرید کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();
$cartSummary = getCartSummary($user['id']);

$pageTitle = 'سبد خرید';
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
                        <span><i class="fas fa-shopping-cart"></i> سبد خرید</span>
                        <span class="badge bg-primary"><?= $cartSummary['item_count'] ?> محصول</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($cartSummary['items'])): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>سبد خرید شما خالی است</p>
                                <a href="../shop-grid-4-columns-sidebar.html" class="btn btn-primary">
                                    مشاهده محصولات
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="cart-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>محصول</th>
                                            <th>قیمت</th>
                                            <th>تعداد</th>
                                            <th>جمع</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartSummary['items'] as $item): ?>
                                            <tr data-product-id="<?= $item['product_id'] ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['image_url']): ?>
                                                            <img src="<?= $item['image_url'] ?>" alt=""
                                                                style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                                class="me-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <a href="../product-full.html?product=<?= $item['product_id'] ?>"
                                                                class="text-dark">
                                                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($item['has_discount']): ?>
                                                        <del class="text-muted small"><?= formatPrice($item['price']) ?></del><br>
                                                        <span
                                                            class="text-danger"><?= formatPrice($item['effective_price']) ?></span>
                                                    <?php else: ?>
                                                        <?= formatPrice($item['price']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="input-group" style="width: 120px;">
                                                        <button class="btn btn-outline-secondary btn-sm qty-btn"
                                                            data-action="decrease" type="button">-</button>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-center qty-input"
                                                            value="<?= $item['quantity'] ?>" min="1"
                                                            max="<?= $item['stock'] ?: 99 ?>">
                                                        <button class="btn btn-outline-secondary btn-sm qty-btn"
                                                            data-action="increase" type="button">+</button>
                                                    </div>
                                                </td>
                                                <td class="line-total">
                                                    <?= formatPrice($item['line_total']) ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger remove-btn" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Cart Summary -->
                            <div class="p-4 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="../shop-grid-4-columns-sidebar.html" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-right"></i> ادامه خرید
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="mb-3">
                                            <span class="fs-5">جمع کل: </span>
                                            <strong class="fs-4 text-primary"
                                                id="cart-total"><?= $cartSummary['formatted_subtotal'] ?></strong>
                                        </div>
                                        <a href="checkout.php" class="btn btn-danger btn-lg">
                                            <i class="fas fa-credit-card"></i> تکمیل خرید
                                        </a>
                                    </div>
                                </div>
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
        // Update quantity
        $('.qty-btn').click(function () {
            const row = $(this).closest('tr');
            const input = row.find('.qty-input');
            const productId = row.data('product-id');
            let qty = parseInt(input.val());

            if ($(this).data('action') === 'increase') {
                qty++;
            } else {
                qty = Math.max(1, qty - 1);
            }

            input.val(qty);
            updateCart(productId, qty);
        });

        $('.qty-input').change(function () {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const qty = Math.max(1, parseInt($(this).val()) || 1);
            $(this).val(qty);
            updateCart(productId, qty);
        });

        // Remove item
        $('.remove-btn').click(function () {
            if (!confirm('آیا از حذف این محصول مطمئن هستید؟')) return;

            const row = $(this).closest('tr');
            const productId = row.data('product-id');

            $.ajax({
                url: '../backend/api/cart.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId }),
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });

        function updateCart(productId, quantity) {
            $.ajax({
                url: '../backend/api/cart.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId, quantity: quantity }),
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
</script>