<?php
/**
 * User Login Page
 * صفحه ورود کاربران
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

// Redirect if already logged in
$user = getCurrentUser();
if ($user) {
    header('Location: ../account-dashboard.php');
    exit;
}

$error = '';
$success = '';
$step = 'phone'; // 'phone' or 'otp'
$phone = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_otp'])) {
        $phone = sanitize($_POST['phone'] ?? '');
        $result = requestOTP($phone);

        if ($result['success']) {
            $step = 'otp';
            $success = $result['message'];
            // Store dev code for testing
            if (isset($result['dev_code'])) {
                $devCode = $result['dev_code'];
            }
        } else {
            $error = $result['error'];
            // Store result for displaying SMS status
            $smsErrorResult = $result;
        }
    } elseif (isset($_POST['verify_otp'])) {
        $phone = sanitize($_POST['phone'] ?? '');
        $code = sanitize($_POST['code'] ?? '');
        $result = verifyOTP($phone, $code);

        if ($result['success']) {
            header('Location: ../account-dashboard.php');
            exit;
        } else {
            $step = 'otp';
            $error = $result['error'];
        }
    } elseif (isset($_POST['change_number'])) {
        $step = 'phone';
        $phone = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>ورود / ثبت‌نام | استارتک</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <!-- fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap">
    <!-- css -->
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../vendor/owl-carousel/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="../vendor/photoswipe/photoswipe.css">
    <link rel="stylesheet" href="../vendor/photoswipe/default-skin/default-skin.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/style.header-spaceship-variant-one.css" media="(min-width: 1200px)">
    <link rel="stylesheet" href="../css/style.mobile-header-variant-one.css" media="(max-width: 1199px)">
    <!-- font - fontawesome -->
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }

        .phone-input {
            direction: ltr;
            text-align: left;
            letter-spacing: 1px;
        }

        .otp-input {
            direction: ltr;
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .dev-code {
            background: #fff3cd;
            padding: 5px;
            border-radius: 4px;
            font-family: monospace;
            direction: ltr;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <!-- site -->
    <div class="site">
        <!-- Site Header (simplified for login, or full if needed. Keeping simple to avoid huge bloat, but user asked for sync. 
             I'll replicate the layout structure but keep the header simple for now to avoid broken links in header, 
             or I should include the main header file if it exists. 
             Since I don't have a main header.php, I will use a simplified header that matches the style.) 
        -->
        <!-- Actually user provided full header HTML. I will use a cleaner version appropriate for a login page. -->

        <header class="site__header">
            <div class="header">
                <div class="header__navbar">
                    <div class="header__navbar-menu">
                        <!-- Simplified menu -->
                    </div>
                    <div class="header__logo">
                        <a href="../index.html" class="logo">
                            <div class="logo__image"><img src="../images/sttechLogo.png" alt="Logo"
                                    style="max-height: 52px;"></div>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- site__body -->
        <div class="site__body">
            <div class="block-space block-space--layout--after-header"></div>
            <div class="block">
                <div class="container container--max--lg">
                    <div class="row justify-content-center">
                        <div class="col-md-6 d-flex">
                            <div class="card flex-grow-1 mb-0">
                                <div class="card-body card-body--padding--2">
                                    <h3 class="card-title text-center">ورود / ثبت‌نام</h3>

                                    <?php if ($error): ?>
                                        <div class="alert alert-danger">
                                            <?= $error ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($success): ?>
                                        <div class="alert alert-success">
                                            <?= $success ?>
                                            <?php if (isset($result['is_test_mode']) && $result['is_test_mode']): ?>
                                                <br><small class="text-warning">⚠️ حالت تست: پیامک واقعی ارسال نشد. کد:
                                                    <strong><?= htmlspecialchars($result['dev_code'] ?? '') ?></strong></small>
                                            <?php elseif (isset($result['actually_sent']) && $result['actually_sent']): ?>
                                                <br><small>✓ پیامک با موفقیت ارسال شد</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($step === 'phone'): ?>
                                        <form method="POST">
                                            <div class="form-group">
                                                <label>شماره موبایل</label>
                                                <input type="tel" name="phone" class="form-control phone-input"
                                                    placeholder="09123456789" maxlength="11" required
                                                    value="<?= htmlspecialchars($phone) ?>">
                                                <small class="form-text text-muted">برای ورود یا ثبت‌نام شماره موبایل خود را
                                                    وارد کنید.</small>
                                            </div>
                                            <div class="form-group mb-0">
                                                <button type="submit" name="request_otp"
                                                    class="btn btn-primary mt-3 btn-block w-100">دریافت کد تایید</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
                                            <div class="form-group text-center">
                                                <p>کد تایید به شماره <?= htmlspecialchars($phone) ?> ارسال شد.</p>
                                            </div>
                                            <div class="form-group">
                                                <label>کد تایید</label>
                                                <input type="text" name="code" class="form-control otp-input"
                                                    placeholder="------" maxlength="6" required autofocus>
                                                <div class="text-center mt-2 text-muted small" id="otp-timer-container">
                                                    زمان باقی‌مانده: <span id="otp-timer"
                                                        class="font-weight-bold text-danger">15:00</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <button type="submit" name="verify_otp"
                                                    class="btn btn-primary mt-3 btn-block w-100">تایید و ورود</button>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" name="change_number" class="btn btn-link btn-sm">تغییر
                                                    شماره</button>
                                            </div>
                                        </form>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                var duration = 900; // 15 minutes in seconds
                                                var timerDisplay = document.getElementById('otp-timer');

                                                function startTimer(duration, display) {
                                                    var timer = duration, minutes, seconds;
                                                    var interval = setInterval(function () {
                                                        minutes = parseInt(timer / 60, 10);
                                                        seconds = parseInt(timer % 60, 10);

                                                        minutes = minutes < 10 ? "0" + minutes : minutes;
                                                        seconds = seconds < 10 ? "0" + seconds : seconds;

                                                        display.textContent = minutes + ":" + seconds;

                                                        if (--timer < 0) {
                                                            clearInterval(interval);
                                                            display.textContent = "00:00";
                                                            document.getElementById('otp-timer-container').innerHTML = '<span class="text-danger">زمان اعتبار کد به پایان رسید. <br><a href="#" onclick="document.querySelector(\'[name=change_number]\').click(); return false;">تلاش مجدد</a></span>';
                                                        }
                                                    }, 1000);
                                                }

                                                if (timerDisplay) {
                                                    startTimer(duration, timerDisplay);
                                                }
                                            });
                                        </script>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block-space block-space--layout--before-footer"></div>
        </div>
        <!-- site__body / end -->

        <!-- site__footer -->
        <footer class="site__footer">
            <div class="site-footer">
                <div class="site-footer__bottom">
                    <div class="container">
                        <div class="site-footer__bottom-row">
                            <div class="site-footer__copyright">
                                Powered by StarTech
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/owl-carousel/owl.carousel.min.js"></script>
    <script src="../vendor/nouislider/nouislider.min.js"></script>
    <script src="../vendor/photoswipe/photoswipe.min.js"></script>
    <script src="../vendor/photoswipe/photoswipe-ui-default.min.js"></script>
    <script src="../vendor/select2/js/select2.min.js"></script>
    <script src="../js/number.js"></script>
    <script src="../js/main.js"></script>
</body>

</html>