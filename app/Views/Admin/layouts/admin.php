<?php use App\Helpers\Helper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?= $page_title ?? 'OneStore Admin' ?></title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <link rel="icon" type="image/png" href="<?= Helper::asset('images/icons/favicon.png') ?>"/>

    <!-- Fonts and icons -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular", 
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/css/plugins.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/css/kaiadmin.min.css">
    
    <!-- Admin Custom Styles -->
    <style>
        .welcome-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stats-icon.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-icon.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-icon.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-icon.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .stats-label {
            color: #6c757d;
            font-weight: 500;
            margin: 0;
        }

        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        .badge-status {
            font-size: 0.75rem;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.125rem;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= Helper::asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="wrapper">
        <!-- Flash Messages -->
        <?php if (!empty($success) || !empty($error) || !empty($flash_messages)): ?>
            <div class="flash-messages">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($flash_messages)): ?>
                    <?php foreach ($flash_messages as $type => $message): ?>
                        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Sidebar -->
        <?php include ROOT_PATH . '/app/Views/Admin/components/sidebar.php'; ?>

        <div class="main-panel">
            <!-- Header -->
            <?php include ROOT_PATH . '/app/Views/Admin/components/header.php'; ?>

            <div class="container">
                <div class="page-inner">
                    <!-- Main Content -->
                    <?= $content ?>
                </div>
            </div>

            <!-- Footer -->
            <?php include ROOT_PATH . '/app/Views/Admin/components/footer.php'; ?>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/core/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/core/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/admin/js/kaiadmin.min.js"></script>

    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= Helper::asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- OneStore Admin Configuration - FIXED for both Development & Production -->
    <script>
        window.OneStoreAdmin = {
            baseUrl: '<?= APP_URL ?>',
            adminUrl: function(path) {
                const cleanPath = (path || '').replace(/^\//, '');
                const baseUrl = '<?= APP_URL ?>';
                return baseUrl + '/admin/' + cleanPath;
            },
            assetUrl: '<?= Helper::asset('') ?>',
            csrfToken: '<?= $csrf_token ?? '' ?>'
        };
    </script>

    <!-- Inline Scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
</body>
</html> 