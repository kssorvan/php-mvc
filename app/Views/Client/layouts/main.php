<?php use App\Helpers\Helper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buy Now - TemuStore</title>
    
    <meta name="description" content="<?= $meta_description ?? 'Temu - Your trusted e-commerce partner' ?>">
    <meta name="keywords" content="<?= $meta_keywords ?? 'ecommerce, shopping, online store' ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= Helper::asset('images/icons/favicon.png') ?>"/>
    
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900">
    
    <!-- CSS Assets -->
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('fonts/font-awesome-4.7.0/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('fonts/iconic/css/material-design-iconic-font.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('fonts/linearicons-v1.0.0/icon-font.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/animate/animate.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/css-hamburgers/hamburgers.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/animsition/css/animsition.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/select2/select2.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/daterangepicker/daterangepicker.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/slick/slick.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/MagnificPopup/magnific-popup.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('vendor/perfect-scrollbar/perfect-scrollbar.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('css/util.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= Helper::asset('css/main.css') ?>">
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= Helper::asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="animsition">
    <!-- Flash Messages -->
    <?php if (!empty($flash_messages)): ?>
        <div class="flash-messages">
            <?php foreach ($flash_messages as $type => $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <?php include ROOT_PATH . '/app/Views/Client/components/header.php'; ?>

    <!-- Cart Modal -->
    <?php include ROOT_PATH . '/app/Views/Client/components/cart.php'; ?>

    <!-- Breadcrumbs -->
    <?php if (!empty($breadcrumbs)): ?>
        <div class="container">
            <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
                <a href="/" class="stext-109 cl8 hov-cl1 trans-04">Home</a>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <span class="stext-109 cl4">&nbsp;/&nbsp;</span>
                    <?php if ($crumb['url']): ?>
                        <a href="<?= $crumb['url'] ?>" class="stext-109 cl8 hov-cl1 trans-04"><?= $crumb['title'] ?></a>
                    <?php else: ?>
                        <span class="stext-109 cl4"><?= $crumb['title'] ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <?php include ROOT_PATH . '/app/Views/Client/components/footer.php'; ?>

    <!-- Back to Top Button -->
    <?php include ROOT_PATH . '/app/Views/Client/components/back-to-top.php'; ?>

    <!-- JavaScript Assets -->
    <script src="<?= Helper::asset('vendor/jquery/jquery-3.2.1.min.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/animsition/js/animsition.min.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/bootstrap/js/popper.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/select2/select2.min.js') ?>"></script>
    <script>
        $(".js-select2").each(function(){
            $(this).select2({
                minimumResultsForSearch: 20,
                dropdownParent: $(this).next('.dropDownSelect2')
            });
        })
    </script>
    <script src="<?= Helper::asset('vendor/daterangepicker/moment.min.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/daterangepicker/daterangepicker.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/slick/slick.min.js') ?>"></script>
    <script src="<?= Helper::asset('js/slick-custom.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/parallax100/parallax100.js') ?>"></script>
    <script>
        $('.parallax100').parallax100();
    </script>
    <script src="<?= Helper::asset('vendor/MagnificPopup/jquery.magnific-popup.min.js') ?>"></script>
    <script>
        $('.gallery-lb').each(function() { // the containers for all your galleries
            $(this).magnificPopup({
                delegate: 'a', // the selector for gallery item
                type: 'image',
                gallery: {
                    enabled:true
                },
                mainClass: 'mfp-fade'
            });
        });
    </script>
    <script src="<?= Helper::asset('vendor/isotope/isotope.pkgd.min.js') ?>"></script>
    <script src="<?= Helper::asset('vendor/sweetalert/sweetalert.min.js') ?>"></script>
    <script>
        $('.js-addwish-b2').on('click', function(e){
            e.preventDefault();
        });

        $('.js-addwish-b2').each(function(){
            var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
            $(this).on('click', function(){
                swal(nameProduct, "is added to wishlist !", "success");

                $(this).addClass('js-addedwish-b2');
                $(this).off('click');
            });
        });

        $('.js-addwish-detail').each(function(){
            var nameProduct = $(this).parent().parent().parent().find('.js-name-detail').html();

            $(this).on('click', function(){
                swal(nameProduct, "is added to wishlist !", "success");

                $(this).addClass('js-addedwish-detail');
                $(this).off('click');
            });
        });

        /*---------------------------------------------*/

        $('.js-addcart-detail').each(function(){
            var nameProduct = $(this).parent().parent().parent().parent().find('.js-name-detail').html();
            $(this).on('click', function(){
                swal(nameProduct, "is added to cart !", "success");
            });
        });
    </script>
    <script src="<?= Helper::asset('vendor/perfect-scrollbar/perfect-scrollbar.min.js') ?>"></script>
    <script>
        $('.js-pscroll').each(function(){
            $(this).css('position','relative');
            $(this).css('overflow','hidden');
            var ps = new PerfectScrollbar(this, {
                wheelSpeed: 1,
                scrollingThreshold: 1000,
                wheelPropagation: false,
            });

            $(window).on('resize', function(){
                ps.update();
            })
        });
    </script>
    <script src="<?= Helper::asset('js/main.js') ?>"></script>
    
    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= Helper::asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline Scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
    <script>
        // OneStoreClient Configuration - FIXED for both Development & Production
        window.OneStoreClient = window.OneStoreClient || {
            baseUrl: '<?= APP_URL ?>',
            url: function(path) {
                const cleanPath = (path || '').replace(/^\//, '');
                const baseUrl = '<?= APP_URL ?>';
                return baseUrl + (cleanPath ? '/' + cleanPath : '');
            }
        };
    </script>
</body>
</html> 