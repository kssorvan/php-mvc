<?php use App\Helpers\Helper; ?>

<!-- Login -->
<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="bor10 p-lr-40 p-t-40 p-b-40">
                    <h3 class="mtext-111 cl2 txt-center p-b-30">
                        Sign In
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

                    <form method="POST" action="/onestore/login">
                        <div class="p-b-20">
                            <label class="stext-102 cl3 p-b-5">Email Address *</label>
                            <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="email" name="email" required>
                        </div>

                        <div class="p-b-25">
                            <label class="stext-102 cl3 p-b-5">Password *</label>
                            <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="password" required>
                        </div>

                        <div class="flex-w flex-sb-m p-b-25">
                            <div class="flex-w">
                                <input type="checkbox" id="remember_me" name="remember_me" class="m-r-8">
                                <label for="remember_me" class="stext-113 cl6">
                                    Remember me
                                </label>
                            </div>
                            
                            <a href="/forgot-password" class="stext-113 cl6 hov-cl1 trans-04">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer w-full">
                            Sign In
                        </button>

                        <div class="txt-center p-t-25">
                            <span class="stext-113 cl6">
                                Don't have an account?
                            </span>
                            <a href="/onestore/register" class="stext-113 cl2 hov-cl1 trans-04">
                                Create Account
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
</style> 