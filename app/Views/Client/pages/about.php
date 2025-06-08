<?php use App\Helpers\Helper; ?>

<!-- Page Title -->
<section class="bg-img1 txt-center p-lr-15 p-tb-92 m-t-23" style="background-image: url('<?= Helper::assets('images/bg-01.jpg') ?>');">
    <h2 class="ltext-105 cl0 txt-center">About</h2>
</section>

<!-- About Content -->
<section class="bg0 p-t-75 p-b-120">
    <div class="container">
        <div class="row p-b-148">
            <div class="col-md-7 col-lg-8">
                <div class="p-t-7 p-r-85 p-r-15-lg p-r-0-md">
                    <h3 class="mtext-111 cl2 p-b-16">Our Story</h3>

                    <p class="stext-113 cl6 p-b-26">
                        <?= $about_content['story'] ?? 'OneStore was founded with a simple mission: to provide quality products at affordable prices with exceptional customer service. Our journey began in 2020 when our founders recognized the need for a reliable e-commerce platform that puts customer satisfaction first.' ?>
                    </p>

                    <p class="stext-113 cl6 p-b-26">
                        <?= $about_content['values'] ?? 'We believe in transparency, quality, and building lasting relationships with our customers. Every product in our catalog is carefully selected to meet our high standards, and our dedicated team works tirelessly to ensure your shopping experience exceeds expectations.' ?>
                    </p>

                    <p class="stext-113 cl6 p-b-26">
                        Any questions? Let us know in store at <?= $contact_info['address'] ?? '8th floor, 379 Hudson St, New York, NY 10018' ?> 
                        or call us on <?= $contact_info['phone'] ?? '(+1) 96 716 6879' ?>
                    </p>
                </div>
            </div>

            <div class="col-11 col-md-5 col-lg-4 m-lr-auto">
                <div class="how-bor1">
                    <div class="hov-img0">
                        <img src="<?= Helper::assets('images/about-01.jpg') ?>" alt="About OneStore">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="order-md-2 col-md-7 col-lg-8 p-b-30">
                <div class="p-t-7 p-l-85 p-l-15-lg p-l-0-md">
                    <h3 class="mtext-111 cl2 p-b-16">Our Mission</h3>

                    <p class="stext-113 cl6 p-b-26">
                        <?= $about_content['mission'] ?? 'Our mission is to democratize access to quality products by leveraging technology to create seamless shopping experiences. We are committed to sustainability, ethical business practices, and supporting our local communities.' ?>
                    </p>

                    <div class="bor16 p-l-29 p-b-9 m-t-22">
                        <p class="stext-114 cl6 p-r-40 p-b-11">
                            "<?= $about_content['quote'] ?? 'Creativity is just connecting things. When you ask creative people how they did something, they feel a little guilty because they didn\'t really do it, they just saw something. It seemed obvious to them after a while.' ?>"
                        </p>

                        <span class="stext-111 cl8">
                            - <?= $about_content['quote_author'] ?? 'Steve Jobs' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="order-md-1 col-11 col-md-5 col-lg-4 m-lr-auto p-b-30">
                <div class="how-bor2">
                    <div class="hov-img0">
                        <img src="<?= Helper::assetss('images/about-02.jpg') ?>" alt="Our Team">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section (Optional) -->
<?php if (!empty($team_members)): ?>
<section class="bg0 p-t-75 p-b-120">
    <div class="container">
        <h3 class="ltext-103 cl5 txt-center p-b-64">Our Team</h3>
        
        <div class="row">
            <?php foreach ($team_members as $member): ?>
            <div class="col-sm-6 col-md-4 p-b-30">
                <div class="team-member txt-center">
                    <div class="team-img">
                        <img src="<?= Helper::upload($member['image'] ?: 'team/placeholder.jpg') ?>" 
                             alt="<?= Helper::sanitize($member['name']) ?>" class="img-fluid rounded">
                    </div>
                    <h4 class="mtext-111 cl2 p-t-15"><?= Helper::sanitize($member['name']) ?></h4>
                    <p class="stext-113 cl6"><?= Helper::sanitize($member['position']) ?></p>
                    <p class="stext-113 cl6 p-t-10"><?= Helper::sanitize($member['bio']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Statistics Section -->
<section class="bg3 p-t-75 p-b-120">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6 p-b-30">
                <div class="txt-center">
                    <h3 class="ltext-102 cl0"><?= number_format($stats['customers'] ?? 10000) ?>+</h3>
                    <p class="stext-107 cl0">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 p-b-30">
                <div class="txt-center">
                    <h3 class="ltext-102 cl0"><?= number_format($stats['orders'] ?? 25000) ?>+</h3>
                    <p class="stext-107 cl0">Orders Completed</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 p-b-30">
                <div class="txt-center">
                    <h3 class="ltext-102 cl0"><?= number_format($stats['products'] ?? 500) ?>+</h3>
                    <p class="stext-107 cl0">Products Available</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 p-b-30">
                <div class="txt-center">
                    <h3 class="ltext-102 cl0"><?= $stats['years'] ?? 4 ?>+</h3>
                    <p class="stext-107 cl0">Years of Experience</p>
                </div>
            </div>
        </div>
    </div>
</section> 