<?php
require_once __DIR__ . '/backend/includes/functions.php';
require_once __DIR__ . '/backend/includes/user_functions.php';
$user = requireUserLogin();
$addresses = getUserAddresses($user['id']);
$defaultAddress = !empty($addresses) ? $addresses[0] : null;

// Fetch unique orders for dashboard
$recentOrders = getUserOrders($user['id'], 3);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	<title>حساب کاربری داشبورد — Red Parts</title>
	<link rel="icon" type="image/png" href="images/favicon.png"><!-- fonts -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap">
	<!-- css -->
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="vendor/owl-carousel/assets/owl.carousel.min.css">
	<link rel="stylesheet" href="vendor/photoswipe/photoswipe.css">
	<link rel="stylesheet" href="vendor/photoswipe/default-skin/default-skin.css">
	<link rel="stylesheet" href="vendor/select2/css/select2.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/style.header-spaceship-variant-one.css" media="(min-width: 1200px)">
	<link rel="stylesheet" href="css/style.mobile-header-variant-one.css" media="(max-width: 1199px)">
	<!-- font - fontawesome -->
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-97489509-8"></script>
	<script>window.dataLayer = window.dataLayer || []; function gtag() { dataLayer.push(argumen t s); } gtag("js", new Da te()); gtag("config", "UA-97489509-8");</script>
	<style>
		/* Global Farsi font for all content */
		body {
			font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif;
		}

		/* Ensure all text elements use Vazirmatn */
		p,
		div,
		span,
		a,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		li,
		td,
		th,
		label,
		input,
		textarea,
		select,
		button,
		.card,
		.dashboard,
		.profile-card,
		.address-card,
		.account-nav {
			font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif;
		}

		.main-menu__link strong {
			font-weight: 700 !important;
			font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif !important;
			letter-spacing: 0;
		}

		.main-menu__link {
			font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif;
		}

		.departments__button-title {
			font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif;
			font-weight: 600;
		}
	</style>

</head>

<body><!-- site -->
	<div class="site"><!-- site__mobile-header -->
		<header class="site__mobile-header">
			<div class="mobile-header">
				<div class="container">
					<div class="mobile-header__body"><button class="mobile-header__menu-button" type="button"><svg
								width="18px" height="14px">
								<path
									d="M-0,8L-0,6L18,6L18,8L-0,8ZM-0,-0L18,-0L18,2L-0,2L-0,-0ZM14,14L-0,14L-0,12L14,12L14,14Z" />
							</svg></button> <a class="mobile-header__logo" href=""><!-- mobile-logo --> <svg width="130"
								height="20">
								<path class="mobile-header__logo-part-one" d="M40,19.9c-0.3,0-0.7,0.1-1,0.1h-4.5c-0.8,0-1.5-0.7-1.5-1.5v-17C33,0.7,33.7,0,34.5,0H39c0.3,0,0.7,0,1,0.1

	c4.5,0.5,8,4.3,8,8.9v2C48,15.6,44.5,19.5,40,19.9z M44,9.5C44,6.7,41.8,4,39,4h-0.8C37.5,4,37,4.5,37,5.2v9.6

	c0,0.7,0.5,1.2,1.2,1.2H39c2.8,0,5-2.7,5-5.5V9.5z M29.5,20h-11c-0.8,0-1.5-0.7-1.5-1.5v-17C17,0.7,17.7,0,18.5,0h11

	C30.3,0,31,0.7,31,1.5v1C31,3.3,30.3,4,29.5,4H21v4h6.5C28.3,8,29,8.7,29,9.5v1c0,0.8-0.7,1.5-1.5,1.5H21v4h8.5

	c0.8,0,1.5,0.7,1.5,1.5v1C31,19.3,30.3,20,29.5,20z M14.8,17.8c0.6,1-0.1,2.3-1.3,2.3h-2L8,14H4v4.5C4,19.3,3.3,20,2.5,20h-1

	C0.7,20,0,19.3,0,18.5v-17C0,0.7,0.7,0,1.5,0H8c0.3,0,0.7,0,1,0.1c3.4,0.5,6,3.4,6,6.9c0,2.4-1.2,4.5-3.1,5.8L14.8,17.8z M9,4.2

	C8.7,4.1,8.3,4,8,4H5C4.4,4,4,4.4,4,5v4c0,0.6,0.4,1,1,1h3c0.3,0,0.7-0.1,1-0.2c0.3-0.1,0.7-0.3,0.9-0.5C10.6,8.8,11,7.9,11,7

	C11,5.7,10.2,4.6,9,4.2z"></path>
								<path class="mobile-header__logo-part-two" d="M128.6,6h-1c-0.5,0-0.9-0.3-1.2-0.7c-0.2-0.3-0.4-0.6-0.8-0.8c-0.5-0.3-1.4-0.5-2.1-0.5c-1.5,0-2.8,0.9-2.8,2

	c0,0.7,0.5,1.3,1.2,1.6c0.8,0.4,1.1,1.3,0.7,2.1l-0.4,0.9c-0.4,0.7-1.2,1-1.8,0.6c-0.6-0.3-1.2-0.7-1.6-1.2c-1-1.1-1.7-2.5-1.7-4

	c0-3.3,2.9-6,6.5-6c2.8,0,5.5,1.7,6.4,4C130.3,4.9,129.6,6,128.6,6z M113.5,4H109v14.5c0,0.8-0.7,1.5-1.5,1.5h-1

	c-0.8,0-1.5-0.7-1.5-1.5V4h-4.5C99.7,4,99,3.3,99,2.5v-1c0-0.8,0.7-1.5,1.5-1.5h13c0.8,0,1.5,0.7,1.5,1.5v1C115,3.3,114.3,4,113.5,4

	z M97.8,17.8c0.6,1-0.1,2.3-1.3,2.3h-2L91,14h-4v4.5c0,0.8-0.7,1.5-1.5,1.5h-1c-0.8,0-1.5-0.7-1.5-1.5v-17C83,0.7,83.7,0,84.5,0H91

	c0.3,0,0.7,0,1,0.1c3.4,0.5,6,3.4,6,6.9c0,2.4-1.2,4.5-3.1,5.8L97.8,17.8z M92,4.2C91.7,4.1,91.3,4,91,4h-3c-0.6,0-1,0.4-1,1v4

	c0,0.6,0.4,1,1,1h3c0.3,0,0.7-0.1,1-0.2c0.3-0.1,0.7-0.3,0.9-0.5C93.6,8.8,94,7.9,94,7C94,5.7,93.2,4.6,92,4.2z M79.5,20h-1.1

	c-0.6,0-1.2-0.4-1.4-1l-1.5-4h-6.1L68,19c-0.2,0.6-0.8,1-1.4,1h-1.1c-1,0-1.8-1-1.4-2l6.2-17c0.2-0.6,0.8-1,1.4-1h1.6

	c0.6,0,1.2,0.4,1.4,1l6.2,17C81.3,19,80.5,20,79.5,20z M72.5,6.6L70.9,11h3.2L72.5,6.6z M58,14h-4v4.5c0,0.8-0.7,1.5-1.5,1.5h-1

	c-0.8,0-1.5-0.7-1.5-1.5v-17C50,0.7,50.7,0,51.5,0H58c3.9,0,7,3.1,7,7S61.9,14,58,14z M61,7c0-1.3-0.8-2.4-2-2.8

	C58.7,4.1,58.3,4,58,4h-3c-0.5,0-1,0.4-1,1v4c0,0.6,0.5,1,1,1h3c0.3,0,0.7-0.1,1-0.2c0.3-0.1,0.7-0.3,0.9-0.5C60.6,8.8,61,7.9,61,7z

	 M118.4,14h1c0.5,0,0.9,0.3,1.2,0.7c0.2,0.3,0.4,0.6,0.8,0.8c0.5,0.3,1.4,0.5,2.1,0.5c1.5,0,2.8-0.9,2.8-2c0-0.7-0.5-1.3-1.2-1.6

	c-0.8-0.4-1.1-1.3-0.7-2.1l0.4-0.9c0.4-0.7,1.2-1,1.8-0.6c0.6,0.3,1.2,0.7,1.6,1.2c1,1.1,1.7,2.5,1.7,4c0,3.3-2.9,6-6.5,6

	c-2.8,0-5.5-1.7-6.4-4C116.7,15.1,117.4,14,118.4,14z"></path>
							</svg><!-- mobile-logo / end --></a>
						<div class="mobile-header__search mobile-search">
							<form class="mobile-search__body"><input type="text" class="mobile-search__input"
									placeholder="Enter keyword or part number"> <button type="button"
									class="mobile-search__vehicle-picker" aria-label="انتخاب وسیله نقلیه"><svg
										width="20" height="20">
										<path d="M6.6,2c2,0,4.8,0,6.8,0c1,0,2.9,0.8,3.6,2.2C17.7,5.7,17.9,7,18.4,7C20,7,20,8,20,8v1h-1v7.5c0,0.8-0.7,1.5-1.5,1.5h-1

	c-0.8,0-1.5-0.7-1.5-1.5V16H5v0.5C5,17.3,4.3,18,3.5,18h-1C1.7,18,1,17.3,1,16.5V16V9H0V8c0,0,0.1-1,1.6-1C2.1,7,2.3,5.7,3,4.2

	C3.7,2.8,5.6,2,6.6,2z M13.3,4H6.7c-0.8,0-1.4,0-2,0.7c-0.5,0.6-0.8,1.5-1,2C3.6,7.1,3.5,7.9,3.7,8C4.5,8.4,6.1,9,10,9

	c4,0,5.4-0.6,6.3-1c0.2-0.1,0.2-0.8,0-1.2c-0.2-0.4-0.5-1.5-1-2C14.7,4,14.1,4,13.3,4z M4,10c-0.4-0.3-1.5-0.5-2,0

	c-0.4,0.4-0.4,1.6,0,2c0.5,0.5,4,0.4,4,0C6,11.2,4.5,10.3,4,10z M14,12c0,0.4,3.5,0.5,4,0c0.4-0.4,0.4-1.6,0-2c-0.5-0.5-1.3-0.3-2,0

	C15.5,10.2,14,11.3,14,12z" />
									</svg> <span class="mobile-search__vehicle-picker-label">Vehicle</span></button>
								<button type="submit" class="mobile-search__button mobile-search__button--search"><svg
										width="20" height="20">
										<path d="M19.2,17.8c0,0-0.2,0.5-0.5,0.8c-0.4,0.4-0.9,0.6-0.9,0.6s-0.9,0.7-2.8-1.6c-1.1-1.4-2.2-2.8-3.1-3.9C10.9,14.5,9.5,15,8,15

	c-3.9,0-7-3.1-7-7s3.1-7,7-7s7,3.1,7,7c0,1.5-0.5,2.9-1.3,4c1.1,0.8,2.5,2,4,3.1C20,16.8,19.2,17.8,19.2,17.8z M8,3C5.2,3,3,5.2,3,8

	c0,2.8,2.2,5,5,5c2.8,0,5-2.2,5-5C13,5.2,10.8,3,8,3z" />
									</svg></button> <button type="button"
									class="mobile-search__button mobile-search__button--close"><svg width="20"
										height="20">
										<path d="M16.7,16.7L16.7,16.7c-0.4,0.4-1,0.4-1.4,0L10,11.4l-5.3,5.3c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L8.6,10L3.3,4.7

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L10,8.6l5.3-5.3c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L11.4,10l5.3,5.3

	C17.1,15.7,17.1,16.3,16.7,16.7z" />
									</svg></button>
								<div class="mobile-search__field"></div>
							</form>
						</div>
						<div class="mobile-header__indicators">
							<div class="mobile-indicator mobile-indicator--search d-md-none"><button type="button"
									class="mobile-indicator__button"><span class="mobile-indicator__icon"><svg
											width="20" height="20">
											<path d="M19.2,17.8c0,0-0.2,0.5-0.5,0.8c-0.4,0.4-0.9,0.6-0.9,0.6s-0.9,0.7-2.8-1.6c-1.1-1.4-2.2-2.8-3.1-3.9C10.9,14.5,9.5,15,8,15

	c-3.9,0-7-3.1-7-7s3.1-7,7-7s7,3.1,7,7c0,1.5-0.5,2.9-1.3,4c1.1,0.8,2.5,2,4,3.1C20,16.8,19.2,17.8,19.2,17.8z M8,3C5.2,3,3,5.2,3,8

	c0,2.8,2.2,5,5,5c2.8,0,5-2.2,5-5C13,5.2,10.8,3,8,3z" />
										</svg></span></button></div>
							<div class="mobile-indicator d-none d-md-block"><a href="account-login.html"
									class="mobile-indicator__button"><span class="mobile-indicator__icon"><svg
											width="20" height="20">
											<path d="M20,20h-2c0-4.4-3.6-8-8-8s-8,3.6-8,8H0c0-4.2,2.6-7.8,6.3-9.3C4.9,9.6,4,7.9,4,6c0-3.3,2.7-6,6-6s6,2.7,6,6

	c0,1.9-0.9,3.6-2.3,4.7C17.4,12.2,20,15.8,20,20z M14,6c0-2.2-1.8-4-4-4S6,3.8,6,6s1.8,4,4,4S14,8.2,14,6z" />
										</svg></span></a></div>
							<div class="mobile-indicator d-none d-md-block"><a href="wishlist.html"
									class="mobile-indicator__button"><span class="mobile-indicator__icon"><svg
											width="20" height="20">
											<path d="M14,3c2.2,0,4,1.8,4,4c0,4-5.2,10-8,10S2,11,2,7c0-2.2,1.8-4,4-4c1,0,1.9,0.4,2.7,1L10,5.2L11.3,4C12.1,3.4,13,3,14,3 M14,1

	c-1.5,0-2.9,0.6-4,1.5C8.9,1.6,7.5,1,6,1C2.7,1,0,3.7,0,7c0,5,6,12,10,12s10-7,10-12C20,3.7,17.3,1,14,1L14,1z" />
										</svg></span></a></div>
							<div class="mobile-indicator"><a href="cart.html" class="mobile-indicator__button"><span
										class="mobile-indicator__icon"><svg width="20" height="20">
											<circle cx="7" cy="17" r="2" />
											<circle cx="15" cy="17" r="2" />
											<path d="M20,4.4V5l-1.8,6.3c-0.1,0.4-0.5,0.7-1,0.7H6.7c-0.4,0-0.8-0.3-1-0.7L3.3,3.9C3.1,3.3,2.6,3,2.1,3H0.4C0.2,3,0,2.8,0,2.6

	V1.4C0,1.2,0.2,1,0.4,1h2.5c1,0,1.8,0.6,2.1,1.6L5.1,3l2.3,6.8c0,0.1,0.2,0.2,0.3,0.2h8.6c0.1,0,0.3-0.1,0.3-0.2l1.3-4.4

	C17.9,5.2,17.7,5,17.5,5H9.4C9.2,5,9,4.8,9,4.6V3.4C9,3.2,9.2,3,9.4,3h9.2C19.4,3,20,3.6,20,4.4z" />
										</svg> <span class="mobile-indicator__counter">3</span></span></a></div>
						</div>
					</div>
				</div>
			</div>
		</header><!-- site__mobile-header / end --><!-- site__header -->
		<header class="site__header">
			<div class="header">
				<div class="header__megamenu-area megamenu-area"></div>
				<div class="header__topbar-start-bg"></div>
				<div class="header__topbar-start">
					<div class="topbar topbar--spaceship-start">
						<div class="topbar__item-text d-none d-xxl-flex">شماره پشتیبانی : 09360590157</div>
						<div class="topbar__item-text"><a class="topbar__link" href="about-us.html">درباره ما</a></div>
						<div class="topbar__item-text"><a class="topbar__link" href="contact-us-v1.html">تماس با ما</a>
						</div>
						<div class="topbar__item-text"><a class="topbar__link" href="track-order.html">پیگیری سفارش</a>
						</div>
					</div>
				</div>
				<div class="header__topbar-end-bg"></div>
				<div class="header__topbar-end">
					<div class="topbar topbar--spaceship-end">
						<div class="topbar__item-button"><a href="#" class="topbar__button"><span
									class="topbar__button-label">سیستم گارانتی</span></a></div>
					</div>
				</div>
				<div class="header__navbar">
					<div class="header__navbar-departments">
						<div class="departments"><button class="departments__button" type="button"><span
									class="departments__button-icon"><svg width="16px" height="12px">
										<path
											d="M0,7L0,5L16,5L16,7L0,7ZM0,0L16,0L16,2L0,2L0,0ZM12,12L0,12L0,10L12,10L12,12Z" />
									</svg> </span><span class="departments__button-title">منو</span> <span
									class="departments__button-arrow"><svg width="9px" height="6px">
										<path
											d="M0.2,0.4c0.4-0.4,1-0.5,1.4-0.1l2.9,3l2.9-3c0.4-0.4,1.1-0.4,1.4,0.1c0.3,0.4,0.3,0.9-0.1,1.3L4.5,6L0.3,1.6C-0.1,1.3-0.1,0.7,0.2,0.4z" />
									</svg></span></button>
							<div class="departments__menu">
								<div class="departments__arrow"></div>
								<div class="departments__body">
									<ul class="departments__list">
										<li class="departments__list-padding" role="presentation"></li>
										<li
											class="departments__item departments__item--submenu--megamenu departments__item--has-submenu">
											<a href="" class="departments__item-link">چراغ‌ها و روشنایی <span
													class="departments__item-arrow"><svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></span></a>
											<div class="departments__item-menu">
												<div
													class="megamenu departments__megamenu departments__megamenu--size--xl">
													<div class="megamenu__image"><img
															src="images/departments/departments-2.jpg" alt=""></div>
													<div class="row">
														<div class="col-1of5">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">قطعات
																		بدنه</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Bumpers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Hoods</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Grilles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Door Handles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Covers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tailgates</a></li>
																	</ul>
																</li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		تعلیق</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">فرمان</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		سوخت</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">گیربکس</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">فیلتر
																		هوا</a></li>
															</ul>
														</div>
														<div class="col-1of5">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">چراغ‌ها
																		و روشنایی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Headlights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tail Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Turn Signals</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Switches & Relays</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Corner Lights</a></li>
																	</ul>
																</li>
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Brakes
																		& سیستم تعلیق</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Discs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Wheel Hubs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Air سیستم تعلیق</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Ball Joints</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Pad Sets</a></li>
																	</ul>
																</li>
															</ul>
														</div>
														<div class="col-1of5">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">لوازم
																		داخلی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Floor Mats</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Gauges</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Consoles & Organizers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Mobile Electronics</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">فرمان Wheels</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Cargo Accessories</a></li>
																	</ul>
																</li>
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Engine
																		& Drivetrain</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">فیلتر هوا</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Oxygen Sensors</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Heating</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Exhaust</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Cranks & Pistons</a></li>
																	</ul>
																</li>
															</ul>
														</div>
														<div class="col-1of5">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Tools &
																		گاراژ</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Repair Manuals</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Care</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Code Readers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tool Boxes</a></li>
																	</ul>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li
											class="departments__item departments__item--submenu--megamenu departments__item--has-submenu">
											<a href="" class="departments__item-link">لوازم داخلی <span
													class="departments__item-arrow"><svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></span></a>
											<div class="departments__item-menu">
												<div
													class="megamenu departments__megamenu departments__megamenu--size--lg">
													<div class="megamenu__image"><img
															src="images/departments/departments-1.jpg" alt=""></div>
													<div class="row">
														<div class="col-3">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">قطعات
																		بدنه</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Bumpers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Hoods</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Grilles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Door Handles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Covers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tailgates</a></li>
																	</ul>
																</li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		تعلیق</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">فرمان</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		سوخت</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">گیربکس</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">فیلتر
																		هوا</a></li>
															</ul>
														</div>
														<div class="col-3">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">چراغ‌ها
																		و روشنایی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Headlights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tail Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Turn Signals</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Switches & Relays</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Corner Lights</a></li>
																	</ul>
																</li>
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Brakes
																		& سیستم تعلیق</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Discs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Wheel Hubs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Air سیستم تعلیق</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Ball Joints</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Pad Sets</a></li>
																	</ul>
																</li>
															</ul>
														</div>
														<div class="col-3">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">لوازم
																		داخلی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Floor Mats</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Gauges</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Consoles & Organizers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Mobile Electronics</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">فرمان Wheels</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Cargo Accessories</a></li>
																	</ul>
																</li>
															</ul>
														</div>
														<div class="col-3">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Tools &
																		گاراژ</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Repair Manuals</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Care</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Code Readers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tool Boxes</a></li>
																	</ul>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li
											class="departments__item departments__item--submenu--megamenu departments__item--has-submenu">
											<a href="" class="departments__item-link">Switches & Relays <span
													class="departments__item-arrow"><svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></span></a>
											<div class="departments__item-menu">
												<div
													class="megamenu departments__megamenu departments__megamenu--size--md">
													<div class="megamenu__image"><img
															src="images/departments/departments-3.jpg" alt=""></div>
													<div class="row">
														<div class="col-4">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">قطعات
																		بدنه</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Bumpers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Hoods</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Grilles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Door Handles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Covers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tailgates</a></li>
																	</ul>
																</li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		تعلیق</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">فرمان</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		سوخت</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">گیربکس</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">فیلتر
																		هوا</a></li>
															</ul>
														</div>
														<div class="col-4">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">چراغ‌ها
																		و روشنایی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Headlights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tail Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Turn Signals</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Switches & Relays</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Corner Lights</a></li>
																	</ul>
																</li>
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">Brakes
																		& سیستم تعلیق</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Discs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Wheel Hubs</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Air سیستم تعلیق</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Ball Joints</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Brake Pad Sets</a></li>
																	</ul>
																</li>
															</ul>
														</div>
														<div class="col-4">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">لوازم
																		داخلی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Floor Mats</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Gauges</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Consoles & Organizers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Mobile Electronics</a></li>
																	</ul>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li
											class="departments__item departments__item--submenu--megamenu departments__item--has-submenu">
											<a href="" class="departments__item-link">Tires & Wheels <span
													class="departments__item-arrow"><svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></span></a>
											<div class="departments__item-menu">
												<div
													class="megamenu departments__megamenu departments__megamenu--size--nl">
													<div class="megamenu__image"><img
															src="images/departments/departments-4.jpg" alt=""></div>
													<div class="row">
														<div class="col-6">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">قطعات
																		بدنه</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Bumpers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Hoods</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Grilles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Door Handles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Covers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tailgates</a></li>
																	</ul>
																</li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		تعلیق</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">فرمان</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		سوخت</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">گیربکس</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">فیلتر
																		هوا</a></li>
															</ul>
														</div>
														<div class="col-6">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">چراغ‌ها
																		و روشنایی</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Headlights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tail Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Turn Signals</a></li>
																	</ul>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li
											class="departments__item departments__item--submenu--megamenu departments__item--has-submenu">
											<a href="" class="departments__item-link">Tools & گاراژ <span
													class="departments__item-arrow"><svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></span></a>
											<div class="departments__item-menu">
												<div
													class="megamenu departments__megamenu departments__megamenu--size--sm">
													<div class="row">
														<div class="col-12">
															<ul
																class="megamenu__links megamenu-links megamenu-links--root">
																<li
																	class="megamenu-links__item megamenu-links__item--has-submenu">
																	<a class="megamenu-links__item-link" href="">قطعات
																		بدنه</a>
																	<ul class="megamenu-links">
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Bumpers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Hoods</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Grilles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Fog Lights</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Door Handles</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Car Covers</a></li>
																		<li class="megamenu-links__item"><a
																				class="megamenu-links__item-link"
																				href="">Tailgates</a></li>
																	</ul>
																</li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		تعلیق</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">فرمان</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">سیستم
																		سوخت</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link"
																		href="">گیربکس</a></li>
																<li class="megamenu-links__item"><a
																		class="megamenu-links__item-link" href="">فیلتر
																		هوا</a></li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</li>
										<li class="departments__item"><a href=""
												class="departments__item-link">Clutches</a></li>
										<li class="departments__item"><a href="" class="departments__item-link">سیستم
												سوخت</a></li>
										<li class="departments__item"><a href=""
												class="departments__item-link">فرمان</a></li>
										<li class="departments__item"><a href="" class="departments__item-link">سیستم
												تعلیق</a></li>
										<li class="departments__item"><a href="" class="departments__item-link">قطعات
												بدنه</a></li>
										<li class="departments__item"><a href=""
												class="departments__item-link">گیربکس</a></li>
										<li class="departments__item"><a href="" class="departments__item-link">فیلتر
												هوا</a></li>
										<li class="departments__list-padding" role="presentation"></li>
									</ul>
									<div class="departments__menu-container"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="header__navbar-menu">
						<div class="main-menu">
							<ul class="main-menu__list">
								<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
									<a href="index.html" class="main-menu__link">خانه <svg width="7px" height="5px">
											<path
												d="M0.280,0.282 C0.645,-0.084 1.238,-0.077 1.596,0.297 L3.504,2.310 L5.413,0.297 C5.770,-0.077 6.363,-0.084 6.728,0.282 C7.080,0.634 7.088,1.203 6.746,1.565 L3.504,5.007 L0.262,1.565 C-0.080,1.203 -0.072,0.634 0.280,0.282 Z" />
										</svg></a>
									<div class="main-menu__submenu">
										<ul class="menu">
											<li class="menu__item"><a href="index.html" class="menu__link">خانه One</a>
											</li>
											<li class="menu__item"><a href="index2.html" class="menu__link">خانه Two</a>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="header-spaceship-variant-one.html" class="menu__link">Header
													Spaceship <span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a
																href="header-spaceship-variant-one.html"
																class="menu__link">نسخه یک</a></li>
														<li class="menu__item"><a
																href="header-spaceship-variant-two.html"
																class="menu__link">نسخه دو</a></li>
														<li class="menu__item"><a
																href="header-spaceship-variant-three.html"
																class="menu__link">نسخه سه</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="header-classic-variant-one.html" class="menu__link">Header
													Classic <span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="header-classic-variant-one.html"
																class="menu__link">نسخه یک</a></li>
														<li class="menu__item"><a href="header-classic-variant-two.html"
																class="menu__link">نسخه دو</a></li>
														<li class="menu__item"><a
																href="header-classic-variant-three.html"
																class="menu__link">نسخه سه</a></li>
														<li class="menu__item"><a
																href="header-classic-variant-four.html"
																class="menu__link">نسخه چهار</a></li>
														<li class="menu__item"><a
																href="header-classic-variant-five.html"
																class="menu__link">نسخه پنج</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="mobile-header-variant-one.html" class="menu__link">Mobile
													Header <span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="mobile-header-variant-one.html"
																class="menu__link">نسخه یک</a></li>
														<li class="menu__item"><a href="mobile-header-variant-two.html"
																class="menu__link">نسخه دو</a></li>
													</ul>
												</div>
											</li>
										</ul>
									</div>
								</li>
								<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
									<a href="shop-grid-4-columns-sidebar.html" class="main-menu__link">فروشگاه <svg
											width="7px" height="5px">
											<path
												d="M0.280,0.282 C0.645,-0.084 1.238,-0.077 1.596,0.297 L3.504,2.310 L5.413,0.297 C5.770,-0.077 6.363,-0.084 6.728,0.282 C7.080,0.634 7.088,1.203 6.746,1.565 L3.504,5.007 L0.262,1.565 C-0.080,1.203 -0.072,0.634 0.280,0.282 Z" />
										</svg></a>
									<div class="main-menu__submenu">
										<ul class="menu">
											<li class="menu__item menu__item--has-submenu"><a
													href="category-4-columns-sidebar.html" class="menu__link">Category
													<span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="category-3-columns-sidebar.html"
																class="menu__link">۳ ستونه سایدبار</a></li>
														<li class="menu__item"><a href="category-4-columns-sidebar.html"
																class="menu__link">۴ ستونه سایدبار</a></li>
														<li class="menu__item"><a href="category-5-columns-sidebar.html"
																class="menu__link">۵ ستونه سایدبار</a></li>
														<li class="menu__item"><a href="category-4-columns-full.html"
																class="menu__link">۴ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="category-5-columns-full.html"
																class="menu__link">۵ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="category-6-columns-full.html"
																class="menu__link">۶ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="category-7-columns-full.html"
																class="menu__link">۷ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="category-right-sidebar.html"
																class="menu__link">سایدبار راست</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="shop-grid-4-columns-sidebar.html" class="menu__link">فروشگاه
													Grid <span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="shop-grid-6-columns-full.html"
																class="menu__link">۶ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="shop-grid-5-columns-full.html"
																class="menu__link">۵ ستونه تمام عرض</a></li>
														<li class="menu__item"><a href="shop-grid-4-columns-full.html"
																class="menu__link">۴ ستونه تمام عرض</a></li>
														<li class="menu__item"><a
																href="shop-grid-4-columns-sidebar.html"
																class="menu__link">۴ ستونه سایدبار</a></li>
														<li class="menu__item"><a
																href="shop-grid-3-columns-sidebar.html"
																class="menu__link">۳ ستونه سایدبار</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item"><a href="shop-list.html" class="menu__link">فروشگاه
													List</a></li>
											<li class="menu__item"><a href="shop-table.html" class="menu__link">فروشگاه
													Table</a></li>
											<li class="menu__item"><a href="shop-right-sidebar.html"
													class="menu__link">فروشگاه Right Sidebar</a></li>
											<li class="menu__item menu__item--has-submenu"><a href="product-full.html"
													class="menu__link">Product <span class="menu__arrow"><svg
															width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="product-full.html"
																class="menu__link">تمام عرض</a></li>
														<li class="menu__item"><a href="product-sidebar.html"
																class="menu__link">سایدبار چپ</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item"><a href="cart.html" class="menu__link">سبد خرید</a>
											</li>
											<li class="menu__item"><a href="checkout.html" class="menu__link">تسویه
													حساب</a></li>
											<li class="menu__item"><a href="order-success.html" class="menu__link">سفارش
													موفق</a></li>
											<li class="menu__item"><a href="wishlist.html" class="menu__link">لیست
													علاقه‌مندی‌ها</a></li>
											<li class="menu__item"><a href="compare.html" class="menu__link">مقایسه</a>
											</li>
											<li class="menu__item"><a href="track-order.html" class="menu__link">پیگیری
													سفارش</a></li>
										</ul>
									</div>
								</li>
								<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
									<a href="blog-classic-right-sidebar.html" class="main-menu__link">وبلاگ <svg
											width="7px" height="5px">
											<path
												d="M0.280,0.282 C0.645,-0.084 1.238,-0.077 1.596,0.297 L3.504,2.310 L5.413,0.297 C5.770,-0.077 6.363,-0.084 6.728,0.282 C7.080,0.634 7.088,1.203 6.746,1.565 L3.504,5.007 L0.262,1.565 C-0.080,1.203 -0.072,0.634 0.280,0.282 Z" />
										</svg></a>
									<div class="main-menu__submenu">
										<ul class="menu">
											<li class="menu__item menu__item--has-submenu"><a
													href="blog-classic-right-sidebar.html" class="menu__link">وبلاگ
													Classic <span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="blog-classic-left-sidebar.html"
																class="menu__link">سایدبار چپ</a></li>
														<li class="menu__item"><a href="blog-classic-right-sidebar.html"
																class="menu__link">سایدبار راست</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="blog-list-right-sidebar.html" class="menu__link">وبلاگ List
													<span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="blog-list-left-sidebar.html"
																class="menu__link">سایدبار چپ</a></li>
														<li class="menu__item"><a href="blog-list-right-sidebar.html"
																class="menu__link">سایدبار راست</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="blog-grid-right-sidebar.html" class="menu__link">وبلاگ Grid
													<span class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="blog-grid-left-sidebar.html"
																class="menu__link">سایدبار چپ</a></li>
														<li class="menu__item"><a href="blog-grid-right-sidebar.html"
																class="menu__link">سایدبار راست</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item menu__item--has-submenu"><a
													href="post-full-width.html" class="menu__link">Post Page <span
														class="menu__arrow"><svg width="6px" height="9px">
															<path
																d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
														</svg></span></a>
												<div class="menu__submenu">
													<ul class="menu">
														<li class="menu__item"><a href="post-full-width.html"
																class="menu__link">تمام عرض</a></li>
														<li class="menu__item"><a href="post-left-sidebar.html"
																class="menu__link">سایدبار چپ</a></li>
														<li class="menu__item"><a href="post-right-sidebar.html"
																class="menu__link">سایدبار راست</a></li>
													</ul>
												</div>
											</li>
											<li class="menu__item"><a href="post-without-image.html"
													class="menu__link">پست بدون تصویر</a></li>
										</ul>
									</div>
								</li>
								<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
									<a href="account-login.html" class="main-menu__link">حساب کاربری <svg width="7px"
											height="5px">
											<path
												d="M0.280,0.282 C0.645,-0.084 1.238,-0.077 1.596,0.297 L3.504,2.310 L5.413,0.297 C5.770,-0.077 6.363,-0.084 6.728,0.282 C7.080,0.634 7.088,1.203 6.746,1.565 L3.504,5.007 L0.262,1.565 C-0.080,1.203 -0.072,0.634 0.280,0.282 Z" />
										</svg></a>
									<div class="main-menu__submenu">
										<ul class="menu">
											<li class="menu__item"><a href="account-login.html" class="menu__link">ورود
													& Register</a></li>
											<li class="menu__item"><a href="account-dashboard.html"
													class="menu__link">داشبورد</a></li>
											<li class="menu__item"><a href="account-garage.html"
													class="menu__link">گاراژ</a></li>
											<li class="menu__item"><a href="account-profile.html"
													class="menu__link">ویرایش پروفایل</a></li>
											<li class="menu__item"><a href="account-orders.html"
													class="menu__link">تاریخچه سفارشات</a></li>
											<li class="menu__item"><a href="account-order-details.html"
													class="menu__link">جزئیات سفارش</a></li>
											<li class="menu__item"><a href="account-addresses.html"
													class="menu__link">دفترچه آدرس</a></li>
											<li class="menu__item"><a href="account-edit-address.html"
													class="menu__link">ویرایش آدرس</a></li>
											<li class="menu__item"><a href="account-password.html"
													class="menu__link">Change رمز عبور</a></li>
										</ul>
									</div>
								</li>
								<li class="main-menu__item main-menu__item--submenu--menu main-menu__item--has-submenu">
									<a href="about-us.html" class="main-menu__link">صفحات <svg width="7px" height="5px">
											<path
												d="M0.280,0.282 C0.645,-0.084 1.238,-0.077 1.596,0.297 L3.504,2.310 L5.413,0.297 C5.770,-0.077 6.363,-0.084 6.728,0.282 C7.080,0.634 7.088,1.203 6.746,1.565 L3.504,5.007 L0.262,1.565 C-0.080,1.203 -0.072,0.634 0.280,0.282 Z" />
										</svg></a>
									<div class="main-menu__submenu">
										<ul class="menu">
											<li class="menu__item"><a href="about-us.html" class="menu__link">درباره
													ما</a></li>
											<li class="menu__item"><a href="contact-us-v1.html" class="menu__link">تماس
													با ما نسخه ۱</a></li>
											<li class="menu__item"><a href="contact-us-v2.html" class="menu__link">تماس
													با ما نسخه ۲</a></li>
											<li class="menu__item"><a href="404.html" class="menu__link">۴۰۴</a></li>
											<li class="menu__item"><a href="terms.html" class="menu__link">قوانین و
													مقررات</a></li>
											<li class="menu__item"><a href="faq.html" class="menu__link">سوالات
													متداول</a></li>
											<li class="menu__item"><a href="components.html"
													class="menu__link">کامپوننت‌ها</a></li>
											<li class="menu__item"><a href="typography.html"
													class="menu__link">تایپوگرافی</a></li>
										</ul>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="header__logo"><a href="index.html" class="logo">
						<div class="logo__slogan"></div>
						<div class="logo__image"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;">
						</div>
					</a></div>
				<div class="header__search">
					<div class="search">
						<form action="" class="search__body">
							<div class="search__shadow"></div><input class="search__input" type="text"
								placeholder="کلمه کلیدی یا شماره قطعه را وارد کنید"> <button
								class="search__button search__button--start" type="button"><span
									class="search__button-icon"><svg width="20" height="20">
										<path d="M6.6,2c2,0,4.8,0,6.8,0c1,0,2.9,0.8,3.6,2.2C17.7,5.7,17.9,7,18.4,7C20,7,20,8,20,8v1h-1v7.5c0,0.8-0.7,1.5-1.5,1.5h-1

	c-0.8,0-1.5-0.7-1.5-1.5V16H5v0.5C5,17.3,4.3,18,3.5,18h-1C1.7,18,1,17.3,1,16.5V16V9H0V8c0,0,0.1-1,1.6-1C2.1,7,2.3,5.7,3,4.2

	C3.7,2.8,5.6,2,6.6,2z M13.3,4H6.7c-0.8,0-1.4,0-2,0.7c-0.5,0.6-0.8,1.5-1,2C3.6,7.1,3.5,7.9,3.7,8C4.5,8.4,6.1,9,10,9

	c4,0,5.4-0.6,6.3-1c0.2-0.1,0.2-0.8,0-1.2c-0.2-0.4-0.5-1.5-1-2C14.7,4,14.1,4,13.3,4z M4,10c-0.4-0.3-1.5-0.5-2,0

	c-0.4,0.4-0.4,1.6,0,2c0.5,0.5,4,0.4,4,0C6,11.2,4.5,10.3,4,10z M14,12c0,0.4,3.5,0.5,4,0c0.4-0.4,0.4-1.6,0-2c-0.5-0.5-1.3-0.3-2,0

	C15.5,10.2,14,11.3,14,12z" />
									</svg> </span><span class="search__button-title">انتخاب وسیله نقلیه</span></button>
							<button class="search__button search__button--end" type="submit"><span
									class="search__button-icon"><svg width="20" height="20">
										<path d="M19.2,17.8c0,0-0.2,0.5-0.5,0.8c-0.4,0.4-0.9,0.6-0.9,0.6s-0.9,0.7-2.8-1.6c-1.1-1.4-2.2-2.8-3.1-3.9C10.9,14.5,9.5,15,8,15

	c-3.9,0-7-3.1-7-7s3.1-7,7-7s7,3.1,7,7c0,1.5-0.5,2.9-1.3,4c1.1,0.8,2.5,2,4,3.1C20,16.8,19.2,17.8,19.2,17.8z M8,3C5.2,3,3,5.2,3,8

	c0,2.8,2.2,5,5,5c2.8,0,5-2.2,5-5C13,5.2,10.8,3,8,3z" />
									</svg></span></button>
							<div class="search__box"></div>
							<div class="search__decor">
								<div class="search__decor-start"></div>
								<div class="search__decor-end"></div>
							</div>
							<div class="search__dropdown search__dropdown--suggestions suggestions">
								<div class="suggestions__group">
									<div class="suggestions__group-title">Products</div>
									<div class="suggestions__group-content"><a
											class="suggestions__item suggestions__product" href="">
											<div class="suggestions__product-image image image--type--product">
												<div class="image__body"><img class="image__tag"
														src="images/products/product-2-40x40.jpg" alt=""></div>
											</div>
											<div class="suggestions__product-info">
												<div class="suggestions__product-name">Brandix Brake Kit BDX-750Z370-S
												</div>
												<div class="suggestions__product-rating">
													<div class="suggestions__product-rating-stars">
														<div class="rating">
															<div class="rating__body">
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
															</div>
														</div>
													</div>
													<div class="suggestions__product-rating-label">5 on 22 reviews</div>
												</div>
											</div>
											<div class="suggestions__product-price">224.00</div>
										</a><a class="suggestions__item suggestions__product" href="">
											<div class="suggestions__product-image image image--type--product">
												<div class="image__body"><img class="image__tag"
														src="images/products/product-3-40x40.jpg" alt=""></div>
											</div>
											<div class="suggestions__product-info">
												<div class="suggestions__product-name">Left Headlight Of Brandix Z54
												</div>
												<div class="suggestions__product-rating">
													<div class="suggestions__product-rating-stars">
														<div class="rating">
															<div class="rating__body">
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star"></div>
																<div class="rating__star"></div>
															</div>
														</div>
													</div>
													<div class="suggestions__product-rating-label">3 on 14 reviews</div>
												</div>
											</div>
											<div class="suggestions__product-price">349.00</div>
										</a><a class="suggestions__item suggestions__product" href="">
											<div class="suggestions__product-image image image--type--product">
												<div class="image__body"><img class="image__tag"
														src="images/products/product-4-40x40.jpg" alt=""></div>
											</div>
											<div class="suggestions__product-info">
												<div class="suggestions__product-name">Glossy Gray 19" آلومینیوم Wheel
													AR-19</div>
												<div class="suggestions__product-rating">
													<div class="suggestions__product-rating-stars">
														<div class="rating">
															<div class="rating__body">
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star rating__star--active"></div>
																<div class="rating__star"></div>
															</div>
														</div>
													</div>
													<div class="suggestions__product-rating-label">4 on 26 reviews</div>
												</div>
											</div>
											<div class="suggestions__product-price">589.00</div>
										</a></div>
								</div>
								<div class="suggestions__group">
									<div class="suggestions__group-title">Categories</div>
									<div class="suggestions__group-content"><a
											class="suggestions__item suggestions__category" href="">چراغ‌ها و
											روشنایی</a> <a class="suggestions__item suggestions__category" href="">Fuel
											System & Filters</a> <a class="suggestions__item suggestions__category"
											href="">قطعات بدنه & Mirrors</a> <a
											class="suggestions__item suggestions__category" href="">Interior
											Accessories</a></div>
								</div>
							</div>
							<div class="search__dropdown search__dropdown--vehicle-picker vehicle-picker">
								<div class="search__dropdown-arrow"></div>
								<div class="vehicle-picker__panel vehicle-picker__panel--list vehicle-picker__panel--active"
									data-panel="factories">
									<div class="vehicle-picker__panel-body">
										<div class="vehicle-picker__text">برای یافتن قطعات مناسب، وسیله نقلیه خود را
											انتخاب کنید</div>
										<div class="vehicles-list">
											<div class="vehicles-list__body">
												<!-- Vehicles will be loaded dynamically from API -->
											</div>
										</div>
										<div class="vehicle-picker__actions"><button type="button"
												class="btn btn-primary btn-sm" data-to-panel="form">افزودن وسیله
												نقلیه</button></div>
									</div>
								</div>
								<div class="vehicle-picker__panel vehicle-picker__panel--form" data-panel="form">
									<div class="vehicle-picker__panel-body">
										<div class="vehicle-form vehicle-form--layout--search">
											<div class="vehicle-form__item vehicle-form__item--select"><select
													class="form-control form-control-select2" aria-label="Brand"
													disabled="disabled">
													<option value="none">انتخاب برند</option>
													<option>Audi</option>
													<option>BMW</option>
													<option>Ferrari</option>
													<option>Ford</option>
													<option>KIA</option>
													<option>Nissan</option>
													<option>Tesla</option>
													<option>Toyota</option>
												</select></div>
											<div class="vehicle-form__item vehicle-form__item--select"><select
													class="form-control form-control-select2" aria-label="Model"
													disabled="disabled">
													<option value="none">انتخاب مدل</option>
													<option>Explorer</option>
													<option>Focus S</option>
													<option>Fusion SE</option>
													<option>Mustang</option>
												</select></div>
											<div class="vehicle-form__item vehicle-form__item--select"><select
													class="form-control form-control-select2" aria-label="Engine"
													disabled="disabled">
													<option value="none">انتخاب موتور</option>
													<option>Gas 1.6L 125 hp AT/L4</option>
													<option>Diesel 2.5L 200 hp AT/L5</option>
													<option>Diesel 3.0L 250 hp MT/L5</option>
												</select></div>
											<div class="vehicle-form__divider">Or</div>
											<div class="vehicle-form__item"><input type="text" class="form-control"
													placeholder="شماره VIN را وارد کنید" aria-label="شماره VIN"></div>
										</div>
										<div class="vehicle-picker__actions">
											<div class="search__car-selector-link"><a href=""
													data-to-panel="list">بازگشت به لیست وسایل نقلیه</a></div><button
												type="button" class="btn btn-primary btn-sm" disabled="disabled">افزودن
												وسیله نقلیه</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="header__indicators">
					<div class="indicator"><a href="wishlist.html" class="indicator__button"><span
								class="indicator__icon"><svg width="32" height="32">
									<path d="M23,4c3.9,0,7,3.1,7,7c0,6.3-11.4,15.9-14,16.9C13.4,26.9,2,17.3,2,11c0-3.9,3.1-7,7-7c2.1,0,4.1,1,5.4,2.6l1.6,2l1.6-2

	C18.9,5,20.9,4,23,4 M23,2c-2.8,0-5.4,1.3-7,3.4C14.4,3.3,11.8,2,9,2c-5,0-9,4-9,9c0,8,14,19,16,19s16-11,16-19C32,6,28,2,23,2L23,2

	z" />
								</svg></span></a></div>
					<div class="indicator indicator--trigger--click"><a href="account-login.html"
							class="indicator__button"><span class="indicator__icon"><svg width="32" height="32">
									<path d="M16,18C9.4,18,4,23.4,4,30H2c0-6.2,4-11.5,9.6-13.3C9.4,15.3,8,12.8,8,10c0-4.4,3.6-8,8-8s8,3.6,8,8c0,2.8-1.5,5.3-3.6,6.7

	C26,18.5,30,23.8,30,30h-2C28,23.4,22.6,18,16,18z M22,10c0-3.3-2.7-6-6-6s-6,2.7-6,6s2.7,6,6,6S22,13.3,22,10z" />
								</svg> </span><span class="indicator__title">سلام، ورود</span> <span
								class="indicator__value">حساب کاربری من</span></a>
						<div class="indicator__content">
							<div class="account-menu">
								<form class="account-menu__form">
									<div class="account-menu__form-title">ورود به حساب کاربری شما</div>
									<div class="form-group"><label for="header-signin-email" class="sr-only">آدرس
											ایمیل</label> <input id="header-signin-email" type="email"
											class="form-control form-control-sm" placeholder="آدرس ایمیل"></div>
									<div class="form-group"><label for="header-signin-password" class="sr-only">رمز
											عبور</label>
										<div class="account-menu__form-forgot"><input id="header-signin-password"
												type="password" class="form-control form-control-sm"
												placeholder="رمز عبور"> <a href=""
												class="account-menu__form-forgot-link">فراموش کرده‌اید؟</a></div>
									</div>
									<div class="form-group account-menu__form-button"><button type="submit"
											class="btn btn-primary btn-sm">ورود</button></div>
									<div class="account-menu__form-link"><a href="account-login.html">ایجاد حساب
											کاربری</a></div>
								</form>
								<div class="account-menu__divider"></div><a href="" class="account-menu__user">
									<div class="account-menu__user-avatar"><img src="images/avatars/avatar-4.jpg"
											alt=""></div>
									<div class="account-menu__user-info">
										<div class="account-menu__user-name">
											<?= htmlspecialchars($user['name'] ?? 'کاربر مهمان') ?>
										</div>
										<div class="account-menu__user-email">
											<?= htmlspecialchars($user['email'] ?? $user['phone']) ?>
										</div>
									</div>
								</a>
								<div class="account-menu__divider"></div>
								<ul class="account-menu__links">
									<li><a href="account-dashboard.html">داشبورد</a></li>
									<li><a href="account-dashboard.html">گاراژ</a></li>
									<li><a href="account-profile.html">ویرایش پروفایل</a></li>
									<li><a href="account-orders.html">تاریخچه سفارشات</a></li>
									<li><a href="account-addresses.html">آدرس‌ها</a></li>
								</ul>
								<div class="account-menu__divider"></div>
								<ul class="account-menu__links">
									<li><a href="account-login.html">خروج</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="indicator indicator--trigger--click"><a href="cart.html" class="indicator__button"><span
								class="indicator__icon"><svg width="32" height="32">
									<circle cx="10.5" cy="27.5" r="2.5" />
									<circle cx="23.5" cy="27.5" r="2.5" />
									<path d="M26.4,21H11.2C10,21,9,20.2,8.8,19.1L5.4,4.8C5.3,4.3,4.9,4,4.4,4H1C0.4,4,0,3.6,0,3s0.4-1,1-1h3.4C5.8,2,7,3,7.3,4.3

	l3.4,14.3c0.1,0.2,0.3,0.4,0.5,0.4h15.2c0.2,0,0.4-0.1,0.5-0.4l3.1-10c0.1-0.2,0-0.4-0.1-0.4C29.8,8.1,29.7,8,29.5,8H14

	c-0.6,0-1-0.4-1-1s0.4-1,1-1h15.5c0.8,0,1.5,0.4,2,1c0.5,0.6,0.6,1.5,0.4,2.2l-3.1,10C28.5,20.3,27.5,21,26.4,21z" />
								</svg> <span class="indicator__counter">3</span> </span><span
								class="indicator__title">سبد خرید</span> <span
								class="indicator__value">250.00</span></a>
						<div class="indicator__content">
							<div class="dropcart">
								<ul class="dropcart__list">
									<li class="dropcart__item">
										<div class="dropcart__item-image image image--type--product"><a
												class="image__body" href="product-full.html"><img class="image__tag"
													src="images/products/product-4-70x70.jpg" alt=""></a></div>
										<div class="dropcart__item-info">
											<div class="dropcart__item-name"><a href="product-full.html">Glossy Gray 19"
													آلومینیوم Wheel AR-19</a></div>
											<ul class="dropcart__item-features">
												<li>رنگ: زرد</li>
												<li>جنس: آلومینیوم</li>
											</ul>
											<div class="dropcart__item-meta">
												<div class="dropcart__item-quantity">2</div>
												<div class="dropcart__item-price">699.00</div>
											</div>
										</div><button type="button" class="dropcart__item-remove"><svg width="10"
												height="10">
												<path d="M8.8,8.8L8.8,8.8c-0.4,0.4-1,0.4-1.4,0L5,6.4L2.6,8.8c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L3.6,5L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L5,3.6l2.4-2.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L6.4,5l2.4,2.4

	C9.2,7.8,9.2,8.4,8.8,8.8z" />
											</svg></button>
									</li>
									<li class="dropcart__divider" role="presentation"></li>
									<li class="dropcart__item">
										<div class="dropcart__item-image image image--type--product"><a
												class="image__body" href="product-full.html"><img class="image__tag"
													src="images/products/product-2-70x70.jpg" alt=""></a></div>
										<div class="dropcart__item-info">
											<div class="dropcart__item-name"><a href="product-full.html">Brandix Brake
													Kit BDX-750Z370-S</a></div>
											<div class="dropcart__item-meta">
												<div class="dropcart__item-quantity">1</div>
												<div class="dropcart__item-price">849.00</div>
											</div>
										</div><button type="button" class="dropcart__item-remove"><svg width="10"
												height="10">
												<path d="M8.8,8.8L8.8,8.8c-0.4,0.4-1,0.4-1.4,0L5,6.4L2.6,8.8c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L3.6,5L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L5,3.6l2.4-2.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L6.4,5l2.4,2.4

	C9.2,7.8,9.2,8.4,8.8,8.8z" />
											</svg></button>
									</li>
									<li class="dropcart__divider" role="presentation"></li>
									<li class="dropcart__item">
										<div class="dropcart__item-image image image--type--product"><a
												class="image__body" href="product-full.html"><img class="image__tag"
													src="images/products/product-5-70x70.jpg" alt=""></a></div>
										<div class="dropcart__item-info">
											<div class="dropcart__item-name"><a href="product-full.html">Twin Exhaust
													Pipe From Brandix Z54</a></div>
											<ul class="dropcart__item-features">
												<li>رنگ: True Red</li>
											</ul>
											<div class="dropcart__item-meta">
												<div class="dropcart__item-quantity">3</div>
												<div class="dropcart__item-price">1210.00</div>
											</div>
										</div><button type="button" class="dropcart__item-remove"><svg width="10"
												height="10">
												<path d="M8.8,8.8L8.8,8.8c-0.4,0.4-1,0.4-1.4,0L5,6.4L2.6,8.8c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L3.6,5L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L5,3.6l2.4-2.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L6.4,5l2.4,2.4

	C9.2,7.8,9.2,8.4,8.8,8.8z" />
											</svg></button>
									</li>
									<li class="dropcart__divider" role="presentation"></li>
								</ul>
								<div class="dropcart__totals">
									<table>
										<tr>
											<th>جمع کل</th>
											<td>5877.00</td>
										</tr>
										<tr>
											<th>ارسال</th>
											<td>25.00</td>
										</tr>
										<tr>
											<th>مالیات</th>
											<td>0.00</td>
										</tr>
										<tr>
											<th>مجموع</th>
											<td>5902.00</td>
										</tr>
									</table>
								</div>
								<div class="dropcart__actions"><a href="cart.html" class="btn btn-secondary">مشاهده سبد
										خرید</a> <a href="checkout.html" class="btn btn-primary">تسویه حساب</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header><!-- site__header / end --><!-- site__body -->
		<div class="site__body">
			<div class="block-space block-space--layout--after-header"></div>
			<div class="block">
				<div class="container container--max--xl">
					<div class="row">
						<div class="col-12 col-lg-3 d-flex">
							<div class="account-nav flex-grow-1">
								<h4 class="account-nav__title">Navigation</h4>
								<ul class="account-nav__list">
									<li class="account-nav__item account-nav__item--active"><a
											href="account-dashboard.html">داشبورد</a></li>
									<li class="account-nav__item"><a href="account-garage.html">گاراژ</a></li>
									<li class="account-nav__item"><a href="account-profile.html">ویرایش پروفایل</a></li>
									<li class="account-nav__item"><a href="account-orders.html">تاریخچه سفارشات</a></li>
									<li class="account-nav__item"><a href="account-order-details.html">جزئیات سفارش</a>
									</li>
									<li class="account-nav__item"><a href="account-addresses.html">آدرس‌ها</a></li>
									<li class="account-nav__item"><a href="account-edit-address.html">ویرایش آدرس</a>
									</li>
									<li class="account-nav__divider" role="presentation"></li>
									<li class="account-nav__item"><a href="account-login.html">خروج</a></li>
								</ul>
							</div>
						</div>
						<div class="col-12 col-lg-9 mt-4 mt-lg-0">
							<div class="dashboard">
								<div class="dashboard__profile card profile-card">
									<div class="card-body profile-card__body">
										<?php
										$userName = $user['name'] ?? ((!empty($user['first_name']) && !empty($user['last_name'])) ? $user['first_name'] . ' ' . $user['last_name'] : 'کاربر مهمان');
										$userEmail = $user['email'] ?? $user['phone'] ?? '';
										?>
										<div class="profile-card__name">
											<?= htmlspecialchars($userName) ?>
										</div>
										<div class="profile-card__email">
											<?= htmlspecialchars($userEmail) ?>
										</div>
										<div class="profile-card__edit"><a href="account-profile.html"
												class="btn btn-secondary btn-sm">ویرایش پروفایل</a></div>
									</div>
								</div>
								<div class="dashboard__address card address-card address-card--featured">
									<div class="address-card__badge tag-badge tag-badge--theme">پیش‌فرض</div>
									<div class="address-card__body">
										<?php if ($defaultAddress): ?>
											<div class="address-card__name">
												<?= htmlspecialchars($defaultAddress['recipient_name'] ?: ($user['name'] ?? ((!empty($user['first_name']) && !empty($user['last_name'])) ? $user['first_name'] . ' ' . $user['last_name'] : 'کاربر مهمان'))) ?>
											</div>
											<div class="address-card__row">
												<?php
												$locationParts = [];
												if (!empty($defaultAddress['province']))
													$locationParts[] = htmlspecialchars($defaultAddress['province']);
												if (!empty($defaultAddress['city']))
													$locationParts[] = htmlspecialchars($defaultAddress['city']);
												echo implode(' - ', $locationParts);
												?>
												<?php if (!empty($defaultAddress['address'])): ?><br><?= htmlspecialchars($defaultAddress['address']) ?><?php endif; ?>
												<?php if (!empty($defaultAddress['postal_code'])): ?><br>کد پستی:
													<?= htmlspecialchars($defaultAddress['postal_code']) ?>	<?php endif; ?>
											</div>
											<?php if (!empty($defaultAddress['landline'] ?? $defaultAddress['phone'] ?? '')): ?>
												<div class="address-card__row">
													<div class="address-card__row-title">تلفن ثابت</div>
													<div class="address-card__row-content">
														<?= htmlspecialchars($defaultAddress['landline'] ?? $defaultAddress['phone'] ?? '') ?>
													</div>
												</div>
											<?php endif; ?>
											<?php if (!empty($user['email'])): ?>
												<div class="address-card__row">
													<div class="address-card__row-title">آدرس ایمیل</div>
													<div class="address-card__row-content">
														<?= htmlspecialchars($user['email']) ?>
													</div>
												</div>
											<?php endif; ?>
										<?php else: ?>
											<div class="address-card__row">
												هنوز آدرسی ثبت نکرده‌اید.
											</div>
										<?php endif; ?>
										<div class="address-card__footer"><a href="account-addresses.html">مدیریت
												آدرس‌ها</a></div>
									</div>
								</div>
								<div class="dashboard__orders card">
									<div class="card-header">
										<h5>آخرین سفارشات</h5>
									</div>
									<div class="card-divider"></div>
									<div class="card-table">
										<div class="table-responsive-sm">
											<table>
												<thead>
													<tr>
														<th>شماره سفارش</th>
														<th>تاریخ</th>
														<th>وضعیت</th>
														<th>مجموع</th>
													</tr>
												</thead>
												<tbody>
													<?php if (!empty($recentOrders)): ?>
														<?php foreach ($recentOrders as $order): ?>
															<tr>
																<td><a
																		href="account/order-details.php?id=<?= $order['id'] ?>">#<?= $order['id'] ?></a>
																</td>
																<td><?= date('Y/m/d', strtotime($order['created_at'])) ?></td>
																<td><?= getOrderStatusText($order['status']) ?></td>
																<td><?= number_format($order['total']) ?> تومان</td>
															</tr>
														<?php endforeach; ?>
													<?php else: ?>
														<tr>
															<td colspan="4" class="text-center py-4">هنوز هیچ سفارشی ثبت
																نکرده‌اید.</td>
														</tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="block-space block-space--layout--before-footer"></div>
		</div><!-- site__body / end --><!-- site__footer -->
		<footer class="site__footer">
			<div class="site-footer">
				<div class="decor site-footer__decor decor--type--bottom">
					<div class="decor__body">
						<div class="decor__start"></div>
						<div class="decor__end"></div>
						<div class="decor__center"></div>
					</div>
				</div>
				<div class="site-footer__widgets">
					<div class="container">
						<div class="row">
							<div class="col-12 col-xl-4">
								<div class="site-footer__widget footer-contacts">
									<h5 class="footer-contacts__title">تماس با ما</h5>
									<div class="footer-contacts__text">Lorem ipsum dolor sit amet, consectetur
										adipiscing elit. Integer in feugiat lorem.</div>
									<address class="footer-contacts__contacts">
										<dl>
											<dt>Phone Number</dt>
											<dd>09360590157</dd>
										</dl>
										<dl>
											<dt>Email Address</dt>
											<dd>hsn@gmail.com</dd>
										</dl>
										<dl>
											<dt>Our Location</dt>
											<dd>715 Fake Street, New York 10021 USA</dd>
										</dl>
										<dl>
											<dt>Working Hours</dt>
											<dd>Mon-Sat 10:00pm - 7:00pm</dd>
										</dl>
									</address>
								</div>
							</div>
							<div class="col-6 col-md-3 col-xl-2">
								<div class="site-footer__widget footer-links">
									<h5 class="footer-links__title">Information</h5>
									<ul class="footer-links__list">
										<li class="footer-links__item"><a href="" class="footer-links__link">درباره
												ما</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Delivery
												Information</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Privacy
												Policy</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Brands</a>
										</li>
										<li class="footer-links__item"><a href="" class="footer-links__link">تماس با
												ما</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Returns</a>
										</li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Site
												Map</a></li>
									</ul>
								</div>
							</div>
							<div class="col-6 col-md-3 col-xl-2">
								<div class="site-footer__widget footer-links">
									<h5 class="footer-links__title">حساب کاربری من</h5>
									<ul class="footer-links__list">
										<li class="footer-links__item"><a href="" class="footer-links__link">Store
												Location</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">تاریخچه
												سفارشات</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Wish
												List</a></li>
										<li class="footer-links__item"><a href=""
												class="footer-links__link">Newsletter</a></li>
										<li class="footer-links__item"><a href=""
												class="footer-links__link">Specials</a></li>
										<li class="footer-links__item"><a href="" class="footer-links__link">Gift
												Certificates</a></li>
										<li class="footer-links__item"><a href=""
												class="footer-links__link">Affiliate</a></li>
									</ul>
								</div>
							</div>
							<div class="col-12 col-md-6 col-xl-4">
								<div class="site-footer__widget footer-newsletter">
									<h5 class="footer-newsletter__title">Newsletter</h5>
									<div class="footer-newsletter__text">Enter your email address below to subscribe to
										our newsletter and keep up to date with discounts and special offers.</div>
									<form action="" class="footer-newsletter__form"><label class="sr-only"
											for="footer-newsletter-address">Email Address</label> <input type="text"
											class="footer-newsletter__form-input" id="footer-newsletter-address"
											placeholder="Email Address..."> <button
											class="footer-newsletter__form-button">Subscribe</button></form>
									<div class="footer-newsletter__text footer-newsletter__text--social">Follow us on
										social networks</div>
									<div class="footer-newsletter__social-links social-links">
										<ul class="social-links__list">
											<li class="social-links__item social-links__item--facebook"><a
													href="https://themeforest.net/user/kos9" target="_blank"><i
														class="fab fa-facebook-f"></i></a></li>
											<li class="social-links__item social-links__item--twitter"><a
													href="https://themeforest.net/user/kos9" target="_blank"><i
														class="fab fa-twitter"></i></a></li>
											<li class="social-links__item social-links__item--youtube"><a
													href="https://themeforest.net/user/kos9" target="_blank"><i
														class="fab fa-youtube"></i></a></li>
											<li class="social-links__item social-links__item--instagram"><a
													href="https://themeforest.net/user/kos9" target="_blank"><i
														class="fab fa-instagram"></i></a></li>
											<li class="social-links__item social-links__item--rss"><a
													href="https://themeforest.net/user/kos9" target="_blank"><i
														class="fas fa-rss"></i></a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="site-footer__bottom">
					<div class="container">
						<div class="site-footer__bottom-row">
							<div class="site-footer__copyright"><!-- copyright --> Powered by HTML — Designed by <a
									href="https://themeforest.net/user/kos9"
									target="_blank">Kos</a><!-- copyright / end --></div>
							<div class="site-footer__payments"><img src="images/payments.png" alt=""></div>
						</div>
					</div>
				</div>
			</div>
		</footer><!-- site__footer / end -->
	</div><!-- site / end --><!-- mobile-menu -->
	<div class="mobile-menu">
		<div class="mobile-menu__backdrop"></div>
		<div class="mobile-menu__body"><button class="mobile-menu__close" type="button"><svg width="12" height="12">
					<path d="M10.8,10.8L10.8,10.8c-0.4,0.4-1,0.4-1.4,0L6,7.4l-3.4,3.4c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L4.6,6L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L6,4.6l3.4-3.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L7.4,6l3.4,3.4

	C11.2,9.8,11.2,10.4,10.8,10.8z" />
				</svg></button>
			<div class="mobile-menu__panel">
				<div class="mobile-menu__panel-header">
					<div class="mobile-menu__panel-title">منو</div>
				</div>
				<div class="mobile-menu__panel-body">
					<div class="mobile-menu__settings-list">
						<div class="mobile-menu__setting" data-mobile-menu-item><button
								class="mobile-menu__setting-button" title="Language" data-mobile-menu-trigger><span
									class="mobile-menu__setting-icon"><img src="images/languages/language-5.png" alt="">
								</span><span class="mobile-menu__setting-title">Italian</span> <span
									class="mobile-menu__setting-arrow"><svg width="6px" height="9px">
										<path
											d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
									</svg></span></button>
							<div class="mobile-menu__setting-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">زبان</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>
													<div class="mobile-menu__links-image"><img
															src="images/languages/language-1.png" alt=""></div>English
												</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>
													<div class="mobile-menu__links-image"><img
															src="images/languages/language-2.png" alt=""></div>French
												</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>
													<div class="mobile-menu__links-image"><img
															src="images/languages/language-3.png" alt=""></div>German
												</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>
													<div class="mobile-menu__links-image"><img
															src="images/languages/language-4.png" alt=""></div>Russian
												</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>
													<div class="mobile-menu__links-image"><img
															src="images/languages/language-5.png" alt=""></div>Italian
												</button></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="mobile-menu__setting" data-mobile-menu-item><button
								class="mobile-menu__setting-button" title="Currency" data-mobile-menu-trigger><span
									class="mobile-menu__setting-icon mobile-menu__setting-icon--currency">$ </span><span
									class="mobile-menu__setting-title">US Dollar</span> <span
									class="mobile-menu__setting-arrow"><svg width="6px" height="9px">
										<path
											d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z" />
									</svg></span></button>
							<div class="mobile-menu__setting-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">واحد پول</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>€ Euro</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>£ Pound Sterling</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>$ US Dollar</button></li>
											<li data-mobile-menu-item><button type="button" class=""
													data-mobile-menu-trigger>₽ Russian Ruble</button></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="mobile-menu__divider"></div>
					<div class="mobile-menu__indicators"><a class="mobile-menu__indicator" href="wishlist.html"><span
								class="mobile-menu__indicator-icon"><svg width="20" height="20">
									<path d="M14,3c2.2,0,4,1.8,4,4c0,4-5.2,10-8,10S2,11,2,7c0-2.2,1.8-4,4-4c1,0,1.9,0.4,2.7,1L10,5.2L11.3,4C12.1,3.4,13,3,14,3 M14,1

	c-1.5,0-2.9,0.6-4,1.5C8.9,1.6,7.5,1,6,1C2.7,1,0,3.7,0,7c0,5,6,12,10,12s10-7,10-12C20,3.7,17.3,1,14,1L14,1z" />
								</svg> </span><span class="mobile-menu__indicator-title">لیست علاقه‌مندی‌ها</span>
						</a><a class="mobile-menu__indicator" href="account-dashboard.html"><span
								class="mobile-menu__indicator-icon"><svg width="20" height="20">
									<path d="M20,20h-2c0-4.4-3.6-8-8-8s-8,3.6-8,8H0c0-4.2,2.6-7.8,6.3-9.3C4.9,9.6,4,7.9,4,6c0-3.3,2.7-6,6-6s6,2.7,6,6

	c0,1.9-0.9,3.6-2.3,4.7C17.4,12.2,20,15.8,20,20z M14,6c0-2.2-1.8-4-4-4S6,3.8,6,6s1.8,4,4,4S14,8.2,14,6z" />
								</svg> </span><span class="mobile-menu__indicator-title">حساب کاربری</span> </a><a
							class="mobile-menu__indicator" href="cart.html"><span
								class="mobile-menu__indicator-icon"><svg width="20" height="20">
									<circle cx="7" cy="17" r="2" />
									<circle cx="15" cy="17" r="2" />
									<path d="M20,4.4V5l-1.8,6.3c-0.1,0.4-0.5,0.7-1,0.7H6.7c-0.4,0-0.8-0.3-1-0.7L3.3,3.9C3.1,3.3,2.6,3,2.1,3H0.4C0.2,3,0,2.8,0,2.6

	V1.4C0,1.2,0.2,1,0.4,1h2.5c1,0,1.8,0.6,2.1,1.6L5.1,3l2.3,6.8c0,0.1,0.2,0.2,0.3,0.2h8.6c0.1,0,0.3-0.1,0.3-0.2l1.3-4.4

	C17.9,5.2,17.7,5,17.5,5H9.4C9.2,5,9,4.8,9,4.6V3.4C9,3.2,9.2,3,9.4,3h9.2C19.4,3,20,3.6,20,4.4z" />
								</svg> <span class="mobile-menu__indicator-counter">3</span> </span><span
								class="mobile-menu__indicator-title">سبد خرید</span> </a><a
							class="mobile-menu__indicator" href="account-garage.html"><span
								class="mobile-menu__indicator-icon"><svg width="20" height="20">
									<path d="M6.6,2c2,0,4.8,0,6.8,0c1,0,2.9,0.8,3.6,2.2C17.7,5.7,17.9,7,18.4,7C20,7,20,8,20,8v1h-1v7.5c0,0.8-0.7,1.5-1.5,1.5h-1

	c-0.8,0-1.5-0.7-1.5-1.5V16H5v0.5C5,17.3,4.3,18,3.5,18h-1C1.7,18,1,17.3,1,16.5V16V9H0V8c0,0,0.1-1,1.6-1C2.1,7,2.3,5.7,3,4.2

	C3.7,2.8,5.6,2,6.6,2z M13.3,4H6.7c-0.8,0-1.4,0-2,0.7c-0.5,0.6-0.8,1.5-1,2C3.6,7.1,3.5,7.9,3.7,8C4.5,8.4,6.1,9,10,9

	c4,0,5.4-0.6,6.3-1c0.2-0.1,0.2-0.8,0-1.2c-0.2-0.4-0.5-1.5-1-2C14.7,4,14.1,4,13.3,4z M4,10c-0.4-0.3-1.5-0.5-2,0

	c-0.4,0.4-0.4,1.6,0,2c0.5,0.5,4,0.4,4,0C6,11.2,4.5,10.3,4,10z M14,12c0,0.4,3.5,0.5,4,0c0.4-0.4,0.4-1.6,0-2c-0.5-0.5-1.3-0.3-2,0

	C15.5,10.2,14,11.3,14,12z" />
								</svg> </span><span class="mobile-menu__indicator-title">گاراژ</span></a></div>
					<div class="mobile-menu__divider"></div>
					<ul class="mobile-menu__links">
						<li data-mobile-menu-item><a href="index.html" class="" data-mobile-menu-trigger>خانه <svg
									width="7" height="11">
									<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
								</svg></a>
							<div class="mobile-menu__links-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">خانه</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><a href="index.html" class=""
													data-mobile-menu-trigger>خانه One</a></li>
											<li data-mobile-menu-item><a href="index2.html" class=""
													data-mobile-menu-trigger>خانه Two</a></li>
											<li data-mobile-menu-item><a href="header-spaceship-variant-one.html"
													class="" data-mobile-menu-trigger>Header Spaceship <svg width="7"
														height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">هدر فضایی</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="header-spaceship-variant-one.html"
																		class="" data-mobile-menu-trigger>نسخه یک</a>
																</li>
																<li data-mobile-menu-item><a
																		href="header-spaceship-variant-two.html"
																		class="" data-mobile-menu-trigger>نسخه دو</a>
																</li>
																<li data-mobile-menu-item><a
																		href="header-spaceship-variant-three.html"
																		class="" data-mobile-menu-trigger>نسخه سه</a>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="header-classic-variant-one.html" class=""
													data-mobile-menu-trigger>Header Classic <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">هدر کلاسیک</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="header-classic-variant-one.html" class=""
																		data-mobile-menu-trigger>نسخه یک</a></li>
																<li data-mobile-menu-item><a
																		href="header-classic-variant-two.html" class=""
																		data-mobile-menu-trigger>نسخه دو</a></li>
																<li data-mobile-menu-item><a
																		href="header-classic-variant-three.html"
																		class="" data-mobile-menu-trigger>نسخه سه</a>
																</li>
																<li data-mobile-menu-item><a
																		href="header-classic-variant-four.html" class=""
																		data-mobile-menu-trigger>نسخه چهار</a></li>
																<li data-mobile-menu-item><a
																		href="header-classic-variant-five.html" class=""
																		data-mobile-menu-trigger>نسخه پنج</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="mobile-header-variant-one.html" class=""
													data-mobile-menu-trigger>Mobile Header <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">هدر موبایل</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="mobile-header-variant-one.html" class=""
																		data-mobile-menu-trigger>نسخه یک</a></li>
																<li data-mobile-menu-item><a
																		href="mobile-header-variant-two.html" class=""
																		data-mobile-menu-trigger>نسخه دو</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</li>
						<li data-mobile-menu-item><a href="shop-grid-4-columns-sidebar.html" class=""
								data-mobile-menu-trigger>فروشگاه <svg width="7" height="11">
									<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
								</svg></a>
							<div class="mobile-menu__links-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">فروشگاه</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><a href="category.html" class=""
													data-mobile-menu-trigger>Category <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">دسته‌بندی</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="category-3-columns-sidebar.html" class=""
																		data-mobile-menu-trigger>۳ ستونه سایدبار</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-4-columns-sidebar.html" class=""
																		data-mobile-menu-trigger>۴ ستونه سایدبار</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-5-columns-sidebar.html" class=""
																		data-mobile-menu-trigger>۵ ستونه سایدبار</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-4-columns-full.html" class=""
																		data-mobile-menu-trigger>۴ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-5-columns-full.html" class=""
																		data-mobile-menu-trigger>۵ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-6-columns-full.html" class=""
																		data-mobile-menu-trigger>۶ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-7-columns-full.html" class=""
																		data-mobile-menu-trigger>۷ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="category-right-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار راست</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="shop-grid-4-columns-sidebar.html"
													class="" data-mobile-menu-trigger>فروشگاه Grid <svg width="7"
														height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">فروشگاه Grid</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="shop-grid-6-columns-full.html" class=""
																		data-mobile-menu-trigger>۶ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="shop-grid-5-columns-full.html" class=""
																		data-mobile-menu-trigger>۵ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="shop-grid-4-columns-full.html" class=""
																		data-mobile-menu-trigger>۴ ستونه تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="shop-grid-4-columns-sidebar.html" class=""
																		data-mobile-menu-trigger>۴ ستونه سایدبار</a>
																</li>
																<li data-mobile-menu-item><a
																		href="shop-grid-3-columns-sidebar.html" class=""
																		data-mobile-menu-trigger>۳ ستونه سایدبار</a>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="shop-list.html" class=""
													data-mobile-menu-trigger>فروشگاه List</a></li>
											<li data-mobile-menu-item><a href="shop-table.html" class=""
													data-mobile-menu-trigger>فروشگاه Table</a></li>
											<li data-mobile-menu-item><a href="shop-right-sidebar.html" class=""
													data-mobile-menu-trigger>فروشگاه Right Sidebar</a></li>
											<li data-mobile-menu-item><a href="product-full.html" class=""
													data-mobile-menu-trigger>Product <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">محصول</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a href="product-full.html"
																		class="" data-mobile-menu-trigger>تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a href="product-sidebar.html"
																		class="" data-mobile-menu-trigger>سایدبار چپ</a>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="cart.html" class=""
													data-mobile-menu-trigger>سبد خرید</a></li>
											<li data-mobile-menu-item><a href="checkout.html" class=""
													data-mobile-menu-trigger>تسویه حساب</a></li>
											<li data-mobile-menu-item><a href="order-success.html" class=""
													data-mobile-menu-trigger>سفارش موفق</a></li>
											<li data-mobile-menu-item><a href="wishlist.html" class=""
													data-mobile-menu-trigger>لیست علاقه‌مندی‌ها</a></li>
											<li data-mobile-menu-item><a href="compare.html" class=""
													data-mobile-menu-trigger>مقایسه</a></li>
											<li data-mobile-menu-item><a href="track-order.html" class=""
													data-mobile-menu-trigger>پیگیری سفارش</a></li>
										</ul>
									</div>
								</div>
							</div>
						</li>
						<li data-mobile-menu-item><a href="blog-classic-right-sidebar.html" class=""
								data-mobile-menu-trigger>وبلاگ <svg width="7" height="11">
									<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
								</svg></a>
							<div class="mobile-menu__links-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">وبلاگ</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><a href="blog-classic-right-sidebar.html" class=""
													data-mobile-menu-trigger>وبلاگ Classic <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">وبلاگ Classic</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="blog-classic-left-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار چپ</a></li>
																<li data-mobile-menu-item><a
																		href="blog-classic-right-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار راست</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="blog-list-right-sidebar.html" class=""
													data-mobile-menu-trigger>وبلاگ List <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">وبلاگ List</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="blog-list-left-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار چپ</a></li>
																<li data-mobile-menu-item><a
																		href="blog-list-right-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار راست</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="blog-grid-right-sidebar.html" class=""
													data-mobile-menu-trigger>وبلاگ Grid <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">وبلاگ Grid</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a
																		href="blog-grid-left-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار چپ</a></li>
																<li data-mobile-menu-item><a
																		href="blog-grid-right-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار راست</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="post-full-width.html" class=""
													data-mobile-menu-trigger>Post Page <svg width="7" height="11">
														<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
													</svg></a>
												<div class="mobile-menu__links-panel" data-mobile-menu-panel>
													<div class="mobile-menu__panel mobile-menu__panel--hidden">
														<div class="mobile-menu__panel-header"><button
																class="mobile-menu__panel-back" type="button"><svg
																	width="7" height="11">
																	<path
																		d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
																</svg></button>
															<div class="mobile-menu__panel-title">صفحه پست</div>
														</div>
														<div class="mobile-menu__panel-body">
															<ul class="mobile-menu__links">
																<li data-mobile-menu-item><a href="post-full-width.html"
																		class="" data-mobile-menu-trigger>تمام عرض</a>
																</li>
																<li data-mobile-menu-item><a
																		href="post-left-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار چپ</a></li>
																<li data-mobile-menu-item><a
																		href="post-right-sidebar.html" class=""
																		data-mobile-menu-trigger>سایدبار راست</a></li>
															</ul>
														</div>
													</div>
												</div>
											</li>
											<li data-mobile-menu-item><a href="post-without-image.html" class=""
													data-mobile-menu-trigger>پست بدون تصویر</a></li>
										</ul>
									</div>
								</div>
							</div>
						</li>
						<li data-mobile-menu-item><a href="account-login.html" class="" data-mobile-menu-trigger>حساب
								کاربری <svg width="7" height="11">
									<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
								</svg></a>
							<div class="mobile-menu__links-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">حساب کاربری</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><a href="account-login.html" class=""
													data-mobile-menu-trigger>ورود & Register</a></li>
											<li data-mobile-menu-item><a href="account-dashboard.html" class=""
													data-mobile-menu-trigger>داشبورد</a></li>
											<li data-mobile-menu-item><a href="account-garage.html" class=""
													data-mobile-menu-trigger>گاراژ</a></li>
											<li data-mobile-menu-item><a href="account-profile.html" class=""
													data-mobile-menu-trigger>ویرایش پروفایل</a></li>
											<li data-mobile-menu-item><a href="account-orders.html" class=""
													data-mobile-menu-trigger>تاریخچه سفارشات</a></li>
											<li data-mobile-menu-item><a href="account-order-details.html" class=""
													data-mobile-menu-trigger>جزئیات سفارش</a></li>
											<li data-mobile-menu-item><a href="account-addresses.html" class=""
													data-mobile-menu-trigger>دفترچه آدرس</a></li>
											<li data-mobile-menu-item><a href="account-edit-address.html" class=""
													data-mobile-menu-trigger>ویرایش آدرس</a></li>
											<li data-mobile-menu-item><a href="account-password.html" class=""
													data-mobile-menu-trigger>Change رمز عبور</a></li>
										</ul>
									</div>
								</div>
							</div>
						</li>
						<li data-mobile-menu-item><a href="about-us.html" class="" data-mobile-menu-trigger>صفحات <svg
									width="7" height="11">
									<path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9

	C-0.1,9.8-0.1,10.4,0.3,10.7z" />
								</svg></a>
							<div class="mobile-menu__links-panel" data-mobile-menu-panel>
								<div class="mobile-menu__panel mobile-menu__panel--hidden">
									<div class="mobile-menu__panel-header"><button class="mobile-menu__panel-back"
											type="button"><svg width="7" height="11">
												<path
													d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z" />
											</svg></button>
										<div class="mobile-menu__panel-title">صفحات</div>
									</div>
									<div class="mobile-menu__panel-body">
										<ul class="mobile-menu__links">
											<li data-mobile-menu-item><a href="about-us.html" class=""
													data-mobile-menu-trigger>درباره ما</a></li>
											<li data-mobile-menu-item><a href="contact-us-v1.html" class=""
													data-mobile-menu-trigger>تماس با ما نسخه ۱</a></li>
											<li data-mobile-menu-item><a href="contact-us-v2.html" class=""
													data-mobile-menu-trigger>تماس با ما نسخه ۲</a></li>
											<li data-mobile-menu-item><a href="404.html" class=""
													data-mobile-menu-trigger>۴۰۴</a></li>
											<li data-mobile-menu-item><a href="terms.html" class=""
													data-mobile-menu-trigger>قوانین و مقررات</a></li>
											<li data-mobile-menu-item><a href="faq.html" class=""
													data-mobile-menu-trigger>سوالات متداول</a></li>
											<li data-mobile-menu-item><a href="components.html" class=""
													data-mobile-menu-trigger>کامپوننت‌ها</a></li>
											<li data-mobile-menu-item><a href="typography.html" class=""
													data-mobile-menu-trigger>تایپوگرافی</a></li>
										</ul>
									</div>
								</div>
							</div>
						</li>
						<li data-mobile-menu-item><a
								href="https://themeforest.net/item/redparts-auto-parts-html-template/24735474"
								class="highlight" target="_blank" data-mobile-menu-trigger>خرید قالب</a></li>
					</ul>
					<div class="mobile-menu__spring"></div>
					<div class="mobile-menu__divider"></div><a class="mobile-menu__contacts" href="">
						<div class="mobile-menu__contacts-subtitle">تماس رایگان ۲۴/۷</div>
						<div class="mobile-menu__contacts-title">09360590157</div>
					</a>
				</div>
			</div>
		</div>
	</div><!-- mobile-menu / end --><!-- quickview-modal -->
	<div id="quickview-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>
	<!-- quickview-modal / end --><!-- add-vehicle-modal -->
	<div id="add-vehicle-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="vehicle-picker-modal modal-dialog modal-dialog-centered">
			<div class="modal-content"><button type="button" class="vehicle-picker-modal__close"><svg width="12"
						height="12">
						<path d="M10.8,10.8L10.8,10.8c-0.4,0.4-1,0.4-1.4,0L6,7.4l-3.4,3.4c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L4.6,6L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L6,4.6l3.4-3.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L7.4,6l3.4,3.4

	C11.2,9.8,11.2,10.4,10.8,10.8z" />
					</svg></button>
				<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active">
					<div class="vehicle-picker-modal__title card-title">افزودن وسیله نقلیه</div>
					<div class="vehicle-form vehicle-form--layout--modal">
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Brand" disabled="disabled">
								<option value="none">انتخاب برند</option>
								<option>Audi</option>
								<option>BMW</option>
								<option>Ferrari</option>
								<option>Ford</option>
								<option>KIA</option>
								<option>Nissan</option>
								<option>Tesla</option>
								<option>Toyota</option>
							</select></div>
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Model" disabled="disabled">
								<option value="none">انتخاب مدل</option>
								<option>Explorer</option>
								<option>Focus S</option>
								<option>Fusion SE</option>
								<option>Mustang</option>
							</select></div>
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Engine" disabled="disabled">
								<option value="none">انتخاب موتور</option>
								<option>Gas 1.6L 125 hp AT/L4</option>
								<option>Diesel 2.5L 200 hp AT/L5</option>
								<option>Diesel 3.0L 250 hp MT/L5</option>
							</select></div>
						<div class="vehicle-form__divider">Or</div>
						<div class="vehicle-form__item"><input type="text" class="form-control"
								placeholder="شماره VIN را وارد کنید" aria-label="شماره VIN"></div>
					</div>
					<div class="vehicle-picker-modal__actions"><button type="button"
							class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button> <button
							type="button" class="btn btn-sm btn-primary">افزودن وسیله نقلیه</button></div>
				</div>
			</div>
		</div>
	</div><!-- add-vehicle-modal / end --><!-- vehicle-picker-modal -->
	<div id="vehicle-picker-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="vehicle-picker-modal modal-dialog modal-dialog-centered">
			<div class="modal-content"><button type="button" class="vehicle-picker-modal__close"><svg width="12"
						height="12">
						<path d="M10.8,10.8L10.8,10.8c-0.4,0.4-1,0.4-1.4,0L6,7.4l-3.4,3.4c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L4.6,6L1.2,2.6

	c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L6,4.6l3.4-3.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L7.4,6l3.4,3.4

	C11.2,9.8,11.2,10.4,10.8,10.8z" />
					</svg></button>
				<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active" data-panel="factories">
					<div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div>
					<div class="vehicle-picker-modal__text">برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید</div>
					<div class="vehicles-list">
						<div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div>
					</div>
					<div class="vehicle-picker-modal__actions"><button type="button"
							class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button> <button
							type="button" class="btn btn-sm btn-primary" data-to-panel="form">افزودن وسیله
							نقلیه</button>
						<div class="vehicle-picker-modal__panel" data-panel="vehicles">
							<div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div>
							<div class="vehicle-picker-modal__text">وسیله نقلیه خود را انتخاب کنید</div>
							<div class="vehicles-list">
								<div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API -->
								</div>
							</div>
							<div class="vehicle-picker-modal__actions"><button type="button"
									class="btn btn-sm btn-secondary" data-back-to-factories>بازگشت</button> <button
									type="button"
									class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button>
							</div>
						</div>
					</div>
				</div>
				<div class="vehicle-picker-modal__panel" data-panel="form">
					<div class="vehicle-picker-modal__title card-title">افزودن وسیله نقلیه</div>
					<div class="vehicle-form vehicle-form--layout--modal">
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Brand" disabled="disabled">
								<option value="none">انتخاب برند</option>
								<option>Audi</option>
								<option>BMW</option>
								<option>Ferrari</option>
								<option>Ford</option>
								<option>KIA</option>
								<option>Nissan</option>
								<option>Tesla</option>
								<option>Toyota</option>
							</select></div>
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Model" disabled="disabled">
								<option value="none">انتخاب مدل</option>
								<option>Explorer</option>
								<option>Focus S</option>
								<option>Fusion SE</option>
								<option>Mustang</option>
							</select></div>
						<div class="vehicle-form__item vehicle-form__item--select"><select
								class="form-control form-control-select2" aria-label="Engine" disabled="disabled">
								<option value="none">انتخاب موتور</option>
								<option>Gas 1.6L 125 hp AT/L4</option>
								<option>Diesel 2.5L 200 hp AT/L5</option>
								<option>Diesel 3.0L 250 hp MT/L5</option>
							</select></div>
						<div class="vehicle-form__divider">Or</div>
						<div class="vehicle-form__item"><input type="text" class="form-control"
								placeholder="شماره VIN را وارد کنید" aria-label="شماره VIN"></div>
					</div>
					<div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary"
							data-to-panel="list">بازگشت به لیست</button> <button type="button"
							class="btn btn-sm btn-primary">افزودن وسیله نقلیه</button></div>
				</div>
			</div>
		</div>
	</div><!-- vehicle-picker-modal / end --><!-- photoswipe -->
	<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="pswp__bg"></div>
		<div class="pswp__scroll-wrap">
			<div class="pswp__container">
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
			</div>
			<div class="pswp__ui pswp__ui--hidden">
				<div class="pswp__top-bar">
					<div class="pswp__counter"></div><button class="pswp__button pswp__button--close"
						title="Close (Esc)"></button><!--<button class="pswp__button pswp__button&#45;&#45;share" title="Share"></button>-->
					<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button> <button
						class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
					<div class="pswp__preloader">
						<div class="pswp__preloader__icn">
							<div class="pswp__preloader__cut">
								<div class="pswp__preloader__donut"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
					<div class="pswp__share-tooltip"></div>
				</div><button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
				<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
				<div class="pswp__caption">
					<div class="pswp__caption__center"></div>
				</div>
			</div>
		</div>
	</div><!-- photoswipe / end --><!-- scripts -->
	<script src="vendor/jquery/jquery.min.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="vendor/owl-carousel/owl.carousel.min.js"></script>
	<script src="vendor/nouislider/nouislider.min.js"></script>
	<script src="vendor/photoswipe/photoswipe.min.js"></script>
	<script src="vendor/photoswipe/photoswipe-ui-default.min.js"></script>
	<script src="vendor/select2/js/select2.min.js"></script>
	<script src="js/number.js"></script>
	<script src="js/main.js"></script>
</body>

</html>

</html>