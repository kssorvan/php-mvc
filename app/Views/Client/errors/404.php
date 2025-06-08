<?php
use App\Helpers\Helper;

$page_title = '404 - Page Not Found | OneStore';
$meta_description = 'The page you are looking for could not be found.';
$body_class = 'error-page';

// Start output buffering for content
ob_start();
?>

<!-- Breadcrumb -->
<div class="container">
    <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
        <a href="<?= Helper::url('') ?>" class="stext-109 cl8 hov-cl1 trans-04">
            Home
            <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
        </a>
        <span class="stext-109 cl4">
            404 Error
        </span>
    </div>
</div>

<!-- 404 Content -->
<section class="bg0 p-t-104 p-b-116">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center">
                    <!-- Error Icon -->
                    <div class="m-b-30">
                        <i class="zmdi zmdi-alert-circle" style="font-size: 120px; color: #717fe0;"></i>
                    </div>
                    
                    <!-- Error Title -->
                    <h1 class="ltext-101 cl2 p-b-16">
                        Page Not Found
                    </h1>
                    
                    <!-- Error Message -->
                    <p class="stext-113 cl6 p-b-26">
                        Sorry, the page you are looking for could not be found. 
                        It might have been moved, deleted, or you entered the wrong URL.
                    </p>
                    
                    <!-- Action Buttons -->
                    <div class="p-t-33">
                        <a href="<?= Helper::url('') ?>" class="flex-c-m stext-101 cl0 size-121 bg3 bor1 hov-btn3 p-lr-15 trans-04 pointer m-tb-10 m-r-8">
                            <i class="fa fa-home m-r-5"></i>
                            Go Home
                        </a>
                        
                        <a href="<?= Helper::url('shop') ?>" class="flex-c-m stext-101 cl2 size-121 bg8 bor1 hov-btn4 p-lr-15 trans-04 pointer m-tb-10">
                            <i class="fa fa-shopping-bag m-r-5"></i>
                            Shop Now
                        </a>
                    </div>
                    
                    <!-- Search Box -->
                    <div class="p-t-40">
                        <form class="flex-w flex-c-m p-tb-13" action="<?= Helper::url('shop') ?>" method="GET">
                            <div class="pos-relative size-113 bor2">
                                <input class="mtext-107 cl2 size-114 plh2 p-r-15" type="text" name="search" placeholder="Search for products...">
                                <button type="submit" class="flex-c-m size-112 ab-t-r fs-16 cl2 hov-cl1 trans-04">
                                    <i class="zmdi zmdi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

// Include the client layout
include ROOT_PATH . '/app/Views/Client/layouts/main.php';
?> 