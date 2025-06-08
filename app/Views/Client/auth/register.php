<?php use App\Helpers\Helper; ?>

<!-- Register -->
<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="bor10 p-lr-40 p-t-40 p-b-40">
                    <h3 class="mtext-111 cl2 txt-center p-b-30">
                        Create Account
                    </h3>

                    <?php if (Helper::hasFlash('error')): ?>
                        <div class="alert alert-danger m-b-20">
                            <?= Helper::getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Helper::hasFlash('success')): ?>
                        <div class="alert alert-success m-b-20">
                            <?= Helper::getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/onestore/register">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <label class="stext-102 cl3 p-b-5">First Name *</label>
                                    <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="text" name="firstName" required value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <label class="stext-102 cl3 p-b-5">Last Name *</label>
                                    <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="text" name="lastName" required value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="p-b-20">
                            <label class="stext-102 cl3 p-b-5">Email Address *</label>
                            <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="p-b-20">
                            <label class="stext-102 cl3 p-b-5">Phone Number</label>
                            <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <label class="stext-102 cl3 p-b-5">Password *</label>
                                    <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="password" required>
                                    <small class="stext-115 cl6">Minimum 8 characters with uppercase, lowercase, and number</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <label class="stext-102 cl3 p-b-5">Confirm Password *</label>
                                    <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>

                        <div class="p-b-25">
                            <div class="flex-w">
                                <input type="checkbox" id="agree_terms" name="agree_terms" required class="m-r-8">
                                <label for="agree_terms" class="stext-113 cl6">
                                    I agree to the <a href="/terms-conditions" class="cl2 hov-cl1">Terms & Conditions</a> and <a href="/privacy-policy" class="cl2 hov-cl1">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer w-full">
                            Create Account
                        </button>

                        <div class="txt-center p-t-25">
                            <span class="stext-113 cl6">
                                Already have an account?
                            </span>
                            <a href="/onestore/login" class="stext-113 cl2 hov-cl1 trans-04">
                                Sign In
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alert {
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 20px;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.justify-content-center {
    justify-content: center;
}

.w-full {
    width: 100%;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin-left: -15px;
    margin-right: -15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
}

.col-lg-6 {
    flex: 0 0 50%;
    max-width: 50%;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .col-md-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style> 