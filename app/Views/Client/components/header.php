<?php use App\Helpers\Helper; 

// Get current path for active menu
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = str_replace('/php-test', '', $currentPath); // Remove base path if exists

// Function to check if menu item is active
function isActiveMenu($path, $currentPath) {
    if ($path === '/' || $path === '/home') {
        return $currentPath === '/' || $currentPath === '/home';
    }
    return $currentPath === $path;
}
?>

<!-- Header -->
<header>
    <!-- Header desktop -->
    <div class="container-menu-desktop">
        <!-- Topbar -->
        <div class="top-bar">
            <div class="content-topbar flex-sb-m h-full container">
                <div class="left-top-bar">
                    Free shipping for standard order over $100
                </div>

                <div class="right-top-bar flex-w h-full">
                    <a href="<?= Helper::url('help') ?>" class="flex-c-m trans-04 p-lr-25">
                        Help & FAQs
                    </a>

                    <a href="<?= Helper::url('account') ?>" class="flex-c-m trans-04 p-lr-25">
                        My Account
                    </a>

                    <a href="<?= Helper::url('language') ?>" class="flex-c-m trans-04 p-lr-25">
                        EN
                    </a>

                    <a href="<?= Helper::url('currency') ?>" class="flex-c-m trans-04 p-lr-25">
                        USD
                    </a>
                </div>
            </div>
        </div>

        <div class="wrap-menu-desktop">
            <nav class="limiter-menu-desktop container">
                
                <!-- Logo desktop -->		
                <a href="<?= Helper::url('') ?>" class="logo">
                    <img src="<?= Helper::asset('images/icons/logo-01.png') ?>" alt="IMG-LOGO">
                </a>

                <!-- Menu desktop -->
                <div class="menu-desktop">
                    <ul class="main-menu">
                        <li class="<?= isActiveMenu('/', $currentPath) ? 'active-menu' : '' ?>">
                            <a href="<?= Helper::url('') ?>">Home</a>
                        </li>

                        <li class="<?= isActiveMenu('/shop', $currentPath) ? 'active-menu' : '' ?>">
                            <a href="<?= Helper::url('shop') ?>">Shop</a>
                        </li>

                        <li class="label1 <?= isActiveMenu('/checkout', $currentPath) ? 'active-menu' : '' ?>" data-label1="hot">
                            <a href="<?= Helper::url('checkout') ?>">Checkout</a>
                        </li>

                        <li class="<?= isActiveMenu('/blog', $currentPath) ? 'active-menu' : '' ?>">
                            <a href="<?= Helper::url('blog') ?>">Blog</a>
                        </li>

                        <li class="<?= isActiveMenu('/about', $currentPath) ? 'active-menu' : '' ?>">
                            <a href="<?= Helper::url('about') ?>">About</a>
                        </li>

                        <li class="<?= isActiveMenu('/contact', $currentPath) ? 'active-menu' : '' ?>">
                            <a href="<?= Helper::url('contact') ?>">Contact</a>
                        </li>
                    </ul>
                </div>	

                <!-- Icon header -->
                <div class="wrap-icon-header flex-w flex-r-m">
                    <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
                        <i class="zmdi zmdi-search"></i>
                    </div>

                    <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="<?= $cart_count ?? 2 ?>">
                        <i class="zmdi zmdi-shopping-cart"></i>
                    </div>

                    <a href="<?= Helper::url('wishlist') ?>" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" data-notify="<?= $wishlist_count ?? 0 ?>">
                        <i class="zmdi zmdi-favorite-outline"></i>
                    </a>
                </div>
            </nav>
        </div>	
    </div>

    <!-- Header Mobile -->
    <div class="wrap-header-mobile">
        <!-- Logo moblie -->		
        <div class="logo-mobile">
            <a href="<?= Helper::url('') ?>"><img src="<?= Helper::asset('images/icons/logo-01.png') ?>" alt="IMG-LOGO"></a>
        </div>

        <!-- Icon header -->
        <div class="wrap-icon-header flex-w flex-r-m m-r-15">
            <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
                <i class="zmdi zmdi-search"></i>
            </div>

            <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart" data-notify="<?= $cart_count ?? 2 ?>">
                <i class="zmdi zmdi-shopping-cart"></i>
            </div>

            <a href="<?= Helper::url('wishlist') ?>" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti" data-notify="<?= $wishlist_count ?? 0 ?>">
                <i class="zmdi zmdi-favorite-outline"></i>
            </a>
        </div>

        <!-- Button show menu -->
        <div class="btn-show-menu-mobile hamburger hamburger--squeeze">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </div>
    </div>

    <!-- Menu Mobile -->
    <div class="menu-mobile">
        <ul class="topbar-mobile">
            <li>
                <div class="left-top-bar">
                    Free shipping for standard order over $100
                </div>
            </li>

            <li>
                <div class="right-top-bar flex-w h-full">
                    <a href="<?= Helper::url('help') ?>" class="flex-c-m p-lr-10 trans-04">
                        Help & FAQs
                    </a>

                    <a href="<?= Helper::url('account') ?>" class="flex-c-m p-lr-10 trans-04">
                        My Account
                    </a>

                    <a href="<?= Helper::url('language') ?>" class="flex-c-m p-lr-10 trans-04">
                        EN
                    </a>

                    <a href="<?= Helper::url('currency') ?>" class="flex-c-m p-lr-10 trans-04">
                        USD
                    </a>
                </div>
            </li>
        </ul>

        <ul class="main-menu-m">
            <li class="<?= isActiveMenu('/', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('') ?>">Home</a>
            </li>

            <li class="<?= isActiveMenu('/shop', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('shop') ?>">Shop</a>
            </li>

            <li class="<?= isActiveMenu('/checkout', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('checkout') ?>">Checkout</a>
            </li>

            <li class="<?= isActiveMenu('/blog', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('blog') ?>">Blog</a>
            </li>

            <li class="<?= isActiveMenu('/about', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('about') ?>">About</a>
            </li>

            <li class="<?= isActiveMenu('/contact', $currentPath) ? 'active-menu' : '' ?>">
                <a href="<?= Helper::url('contact') ?>">Contact</a>
            </li>
        </ul>
    </div>

    <!-- Modal Search -->
    <div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
        <div class="container-search-header">
            <button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
                <img src="<?= Helper::asset('images/icons/icon-close2.png') ?>" alt="CLOSE">
            </button>

            <form class="wrap-search-header flex-w p-l-15">
                <button class="flex-c-m trans-04">
                    <i class="zmdi zmdi-search"></i>
                </button>
                <input class="plh3" type="text" name="search" placeholder="Search...">
            </form>
        </div>
    </div>
</header> 