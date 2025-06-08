<?php use App\Helpers\Helper; ?>
<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="<?= Helper::adminUrl('') ?>" class="logo">
                <img src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/img/logo.png" 
                     alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item <?= Helper::urlIs('admin/dashboard') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('dashboard') ?>" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">E-Commerce</h4>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/products') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('products') ?>">
                        <i class="fa fa-box"></i>
                        <p>Products</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/categories') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('categories') ?>">
                        <i class="fas fa-table"></i>
                        <p>Categories</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/brands') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('brands') ?>">
                        <i class="fa fa-tag"></i>
                        <p>Brands</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/orders') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('orders') ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Orders</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/customers') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('customers') ?>">
                        <i class="fas fa-users"></i>
                        <p>Customers</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Content</h4>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/slider') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('slider') ?>">
                        <i class="fas fa-th-list"></i>
                        <p>Slider</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/pages') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('pages') ?>">
                        <i class="fas fa-file"></i>
                        <p>Pages</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">System</h4>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/admins') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('admins') ?>">
                        <i class="fas fa-user-shield"></i>
                        <p>Admin Users</p>
                    </a>
                </li>
                <li class="nav-item <?= Helper::urlIs('admin/settings') ? 'active' : '' ?>">
                    <a href="<?= Helper::adminUrl('settings') ?>">
                        <i class="fa fa-wrench"></i>
                        <p>Settings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= Helper::adminUrl('logout') ?>">
                        <i class="fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div> 