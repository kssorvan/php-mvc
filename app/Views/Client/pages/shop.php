<?php use App\Helpers\Helper; ?>

<!-- Shop Title -->
<div class="bg0 m-t-23 p-b-140">
    <div class="container">
        <div class="flex-w flex-sb-m p-b-52">
            <div class="flex-w flex-l-m filter-tope-group m-tb-10">
                <button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 how-active1" data-filter="*">
                    All Products
                </button>

                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                    <button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" 
                            data-filter=".<?= strtolower(Helper::sanitize($category['catName'] ?? $category['name'] ?? '')) ?>">
                        <?= Helper::sanitize($category['catName'] ?? $category['name'] ?? 'Unknown') ?>
                    </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="flex-w flex-c-m m-tb-10">
                <div class="flex-c-m stext-106 cl6 size-104 bor4 pointer hov-btn3 trans-04 m-r-8 m-tb-4 js-show-filter">
                    <i class="icon-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-filter-list"></i>
                    <i class="icon-close-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
                    Filter
                </div>

                <div class="flex-c-m stext-106 cl6 size-105 bor4 pointer hov-btn3 trans-04 m-tb-4 js-show-search">
                    <i class="icon-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-search"></i>
                    <i class="icon-close-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
                    Search
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="dis-none panel-filter w-full p-t-10">
            <div class="wrap-filter flex-w bg6 w-full p-lr-40 p-t-27 p-lr-15-sm">
                <div class="filter-col1 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Sort By</div>
                    <ul>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-sort="newest">
                                Newest Products
                            </a>
                        </li>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-sort="price-low">
                                Price: Low to High
                            </a>
                        </li>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-sort="price-high">
                                Price: High to Low
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="filter-col2 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Price</div>
                    <ul>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-price="0-50">
                                $0.00 - $50.00
                            </a>
                        </li>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-price="50-100">
                                $50.00 - $100.00
                            </a>
                        </li>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-price="100-150">
                                $100.00 - $150.00
                            </a>
                        </li>
                        <li class="p-b-6">
                            <a href="#" class="filter-link stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-price="150+">
                                $150.00+
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="filter-col3 p-r-15 p-b-27">
                    <div class="mtext-102 cl2 p-b-15">Tags</div>
                    <div class="flex-w p-t-4 m-r--5">
                        <?php if (!empty($tags)): ?>
                            <?php foreach ($tags as $tag): ?>
                            <a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5" data-tag="<?= Helper::sanitize($tag) ?>">
                                <?= Helper::sanitize($tag) ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="dis-none panel-search w-full p-t-10 p-b-15">
            <div class="bor8 dis-flex p-l-15">
                <button class="size-113 flex-c-m fs-16 cl2 hov-cl1 trans-04">
                    <i class="zmdi zmdi-search"></i>
                </button>
                <input class="mtext-107 cl2 size-114 plh2 p-r-15" type="text" name="search-product" placeholder="Search">
            </div>
        </div>

        <!-- Product -->
        <div class="row isotope-grid" id="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item <?= strtolower($product['catName'] ?? 'general') ?>">
                    <!-- Block2 -->
                    <div class="block2">
                        <div class="block2-pic hov-img0">
                            <img src="<?= Helper::upload($product['image_path'] ?: 'placeholder.jpg') ?>" 
                                 alt="<?= Helper::sanitize($product['productName']) ?>">

                            <button class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 add-to-cart-btn"
                                    data-product-id="<?= $product['productID'] ?>"
                                    data-product-name="<?= Helper::sanitize($product['productName']) ?>"
                                    data-product-price="<?= $product['sale_price'] ?: $product['price'] ?>"
                                    data-product-image="<?= Helper::upload($product['image_path'] ?: 'placeholder.jpg') ?>">
                                Add to Cart
                            </button>
                        </div>

                        <div class="block2-txt flex-w flex-t p-t-14">
                            <div class="block2-txt-child1 flex-col-l">
                                <a href="/product/<?= $product['productID'] ?>" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                    <?= Helper::sanitize($product['productName']) ?>
                                </a>

                                <span class="stext-105 cl3">
                                    <?= Helper::formatCurrency($product['sale_price'] ?: $product['price']) ?>
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                        <span class="old-price text-muted text-decoration-line-through ms-2">
                                            <?= Helper::formatCurrency($product['price']) ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="block2-txt-child2 flex-r p-t-3">
                                <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2" 
                                   data-product-id="<?= $product['productID'] ?>">
                                    <img class="icon-heart1 dis-block trans-04" 
                                         src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/icon-heart-01.png" alt="ICON">
                                    <img class="icon-heart2 dis-block trans-04 ab-t-l" 
                                         src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/icon-heart-02.png" alt="ICON">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center p-t-50 p-b-50">
                        <i class="zmdi zmdi-shopping-cart fs-60 cl6 m-b-20"></i>
                        <h4 class="mtext-111 cl2 p-b-16">No Products Found</h4>
                        <p class="stext-113 cl6">Try adjusting your filters or search terms.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Load more -->
        <?php 
        $per_page = $per_page ?? 12; // Default value if not set
        $current_page = $current_page ?? $currentPage ?? 1; // Handle different variable names
        ?>
        <?php if (!empty($products) && count($products) >= $per_page): ?>
        <div class="flex-c-m flex-w w-full p-t-45">
            <button id="load-more-btn" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04" 
                    data-page="<?= $current_page + 1 ?>">
                Load More
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= Helper::asset('js/product-filter.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize product filtering for shop page with load more functionality
    new ProductFilter({
        gridId: 'product-grid',
        loadMoreBtnId: 'load-more-btn',
        enableLoadMore: true
    });
});
</script> 