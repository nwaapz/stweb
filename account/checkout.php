<?php
/**
 * Checkout Page
 * صفحه تکمیل خرید
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();
$cartSummary = getCartSummary($user['id']);
$addresses = getUserAddresses($user['id']);

// Redirect if cart is empty
if (empty($cartSummary['items'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressData = [
        'name' => sanitize($_POST['shipping_name'] ?? ''),
        'landline' => sanitize($_POST['shipping_landline'] ?? $_POST['shipping_phone'] ?? ''),
        'province' => sanitize($_POST['shipping_province'] ?? ''),
        'city' => sanitize($_POST['shipping_city'] ?? ''),
        'address' => sanitize($_POST['shipping_address'] ?? ''),
        'postal_code' => sanitize($_POST['shipping_postal_code'] ?? '')
    ];

    $notes = sanitize($_POST['notes'] ?? '');

    // Validate
    if (
        empty($addressData['name']) || empty($addressData['landline']) ||
        empty($addressData['city']) || empty($addressData['address'])
    ) {
        $error = 'لطفاً تمام فیلدهای الزامی را پر کنید';
    } else {
        $result = createOrder($user['id'], $addressData, $notes);

        if ($result['success']) {
            header('Location: order-success.php?order=' . $result['order_number']);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'تکمیل خرید';
include 'header.php';
?>

<div class="block-space block-space--layout--after-header"></div>

<div class="block">
    <div class="container container--max--xl">
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <!-- Shipping Form -->
                <div class="col-md-7 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-truck"></i> اطلاعات تحویل
                        </div>
                        <div class="card-body">
                            <?php if (!empty($addresses)): ?>
                                <div class="mb-4">
                                    <label class="form-label">انتخاب از آدرس‌های ذخیره شده</label>
                                    <select class="form-select" id="saved-address">
                                        <option value="">آدرس جدید وارد کنید</option>
                                        <?php foreach ($addresses as $addr): ?>
                                            <option value='<?= json_encode($addr) ?>'>
                                                <?= htmlspecialchars($addr['title'] ?: $addr['city']) ?> -
                                                <?= htmlspecialchars($addr['recipient_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <hr>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">نام گیرنده <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_name" class="form-control" required
                                        value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تلفن ثابت <span class="text-danger">*</span></label>
                                    <input type="tel" name="shipping_landline" class="form-control" 
                                           placeholder="مثال: 02112345678" required
                                           pattern="0[1-9][0-9]{1,3}[0-9]{6,8}"
                                           title="شماره تلفن ثابت (نه موبایل). مثال: 02112345678">
                                    <small class="form-text text-muted">لطفاً شماره تلفن ثابت وارد کنید (نه موبایل)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">استان <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_province" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">شهر <span class="text-danger">*</span></label>
                                    <input type="text" name="shipping_city" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">آدرس کامل <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control" rows="3" required
                                    placeholder="خیابان، کوچه، پلاک، واحد"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">کد پستی</label>
                                <input type="text" name="shipping_postal_code" class="form-control" maxlength="10">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">یادداشت سفارش</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="توضیحات اضافی برای سفارش"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-receipt"></i> خلاصه سفارش
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($cartSummary['items'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            <br><small class="text-muted">× <?= $item['quantity'] ?></small>
                                        </div>
                                        <span><?= formatPrice($item['line_total']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="p-3">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td>جمع اقلام:</td>
                                        <td class="text-start"><?= $cartSummary['formatted_subtotal'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>هزینه ارسال:</td>
                                        <td class="text-start text-success">رایگان</td>
                                    </tr>
                                    <tr class="fw-bold fs-5">
                                        <td>مبلغ قابل پرداخت:</td>
                                        <td class="text-start text-primary"><?= $cartSummary['formatted_subtotal'] ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger btn-lg w-100">
                                <i class="fas fa-check-circle"></i> ثبت سفارش
                            </button>
                            <p class="text-muted small text-center mt-2 mb-0">
                                پرداخت در محل تحویل انجام می‌شود
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>

<script>
    $(document).ready(function () {
        $('#saved-address').change(function () {
            const val = $(this).val();
            if (val) {
                const addr = JSON.parse(val);
                $('input[name="shipping_name"]').val(addr.recipient_name || '');
                $('input[name="shipping_landline"]').val(addr.landline || addr.phone || '');
                $('input[name="shipping_province"]').val(addr.province || '');
                $('input[name="shipping_city"]').val(addr.city || '');
                $('textarea[name="shipping_address"]').val(addr.address || '');
                $('input[name="shipping_postal_code"]').val(addr.postal_code || '');
            }
        });
    });
</script>