<?php use App\Helpers\Helper; ?>
<!-- Navigation Section -->
<div class="wrap-menu-desktop how-shadow1">
    <nav class="limiter-menu-desktop container">
        <!-- Logo desktop -->
        <a href="<?= Helper::url('') ?>" class="logo">
            <img src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/logo-01.png" alt="OneStore Logo">
        </a>

        <!-- Menu desktop -->
        <div class="menu-desktop">
            <ul class="main-menu">
                <li class="<?= Helper::urlIs('index') ? 'active-menu' : '' ?>">
                    <a href="<?= Helper::url('') ?>">Home</a>
                </li>
                <li class="<?= Helper::urlIs('shop') ? 'active-menu' : '' ?>">
                    <a href="<?= Helper::url('shop') ?>">Shop</a>
                </li>
                <li class="<?= Helper::urlIs('checkout') ? 'active-menu' : '' ?> label1" data-label1="hot">
                    <a href="<?= Helper::url('checkout') ?>">Features</a>
                </li>
                <li class="<?= Helper::urlIs('blog') ? 'active-menu' : '' ?>">
                    <a href="<?= Helper::url('blog') ?>">Blog</a>
                </li>
                <li class="<?= Helper::urlIs('about') ? 'active-menu' : '' ?>">
                    <a href="<?= Helper::url('about') ?>">About</a>
                </li>
                <li class="<?= Helper::urlIs('contact') ? 'active-menu' : '' ?>">
                    <a href="<?= Helper::url('contact') ?>">Contact</a>
                </li>
            </ul>
        </div>

        <!-- Icon header -->
        <div class="wrap-icon-header flex-w flex-r-m">
            <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
                <i class="zmdi zmdi-search"></i>
            </div>

            <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="<?= $cart_count ?>">
                <i class="zmdi zmdi-shopping-cart"></i>
            </div>

            <a href="<?= Helper::url('wishlist') ?>" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti" data-notify="0">
                <i class="zmdi zmdi-favorite-outline"></i>
            </a>
        </div>
    </nav>
</div>

<!-- Header Mobile -->
<div class="wrap-header-mobile">
    <!-- Logo mobile -->
    <div class="logo-mobile">
        <a href="<?= Helper::url('') ?>"><img src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/logo-01.png" alt="OneStore Logo"></a>
    </div>

    <!-- Icon header -->
    <div class="wrap-icon-header flex-w flex-r-m m-r-15">
        <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
            <i class="zmdi zmdi-search"></i>
        </div>

        <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart" id="shoptxt" data-notify="<?= $cart_count ?>">
            <i class="zmdi zmdi-shopping-cart"></i>
        </div>

        <a href="<?= Helper::url('wishlist') ?>" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti" data-notify="0">
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
                <a href="#" class="flex-c-m p-lr-10 trans-04">Help & FAQs</a>
                <?php if ($user): ?>
                    <a href="<?= Helper::url('account') ?>" class="flex-c-m p-lr-10 trans-04"><?= Helper::sanitize($user['name']) ?></a>
                    <a href="<?= Helper::url('logout') ?>" class="flex-c-m p-lr-10 trans-04">Logout</a>
                <?php else: ?>
                    <a href="<?= Helper::url('login') ?>" class="flex-c-m p-lr-10 trans-04">Login</a>
                    <a href="<?= Helper::url('register') ?>" class="flex-c-m p-lr-10 trans-04">Register</a>
                <?php endif; ?>
                <a href="#" class="flex-c-m p-lr-10 trans-04">EN</a>
                <a href="#" class="flex-c-m p-lr-10 trans-04">USD</a>
            </div>
        </li>
    </ul>

    <ul class="main-menu-m">
        <li class="<?= Helper::urlIs('index') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('') ?>">Home</a>
        </li>
        <li class="<?= Helper::urlIs('shop') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('shop') ?>">Shop</a>
        </li>
        <li class="<?= Helper::urlIs('checkout') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('checkout') ?>" class="label1 rs1" data-label1="hot">Features</a>
        </li>
        <li class="<?= Helper::urlIs('blog') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('blog') ?>">Blog</a>
        </li>
        <li class="<?= Helper::urlIs('about') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('about') ?>">About</a>
        </li>
        <li class="<?= Helper::urlIs('contact') ? 'active-menu' : '' ?>">
            <a href="<?= Helper::url('contact') ?>">Contact</a>
        </li>
    </ul>
</div>

<!-- Modal Search -->
<div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
    <div class="container-search-header">
        <button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
            <img src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/icon-close2.png" alt="CLOSE">
        </button>

        <form class="wrap-search-header flex-w p-l-15" action="<?= Helper::url('search') ?>" method="GET">
            <button class="flex-c-m trans-04" type="submit">
                <i class="zmdi zmdi-search"></i>
            </button>
            <input class="plh3" type="text" name="q" placeholder="Search products..." required>
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">
        </form>
    </div>
</div> 