<?php use App\Helpers\Helper; ?>

<!-- Account Dashboard -->
<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <!-- Welcome Section -->
        <div class="row p-b-40">
            <div class="col-md-12">
                <div class="bg-light p-lr-40 p-t-30 p-b-30 bor10">
                    <h3 class="mtext-111 cl2 p-b-10">
                        <i class="fa fa-user m-r-10 cl2"></i>
                        Welcome back, <?= htmlspecialchars($user['name'] ?? 'Customer') ?>!
                    </h3>
                    <p class="stext-113 cl6">
                        Manage your account, view your orders, and update your personal information.
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Account Navigation -->
            <div class="col-md-3 p-b-30">
                <div class="bor10 p-lr-20 p-t-30 p-b-30">
                    <h5 class="mtext-108 cl2 p-b-25">
                        <i class="fa fa-cog m-r-10 cl2"></i>
                        Account Menu
                    </h5>
                    
                    <ul class="account-menu">
                        <li class="p-b-10">
                            <a href="#dashboard" class="stext-102 cl2 hov-cl1 trans-04 account-nav-link active" data-section="dashboard">
                                <i class="fa fa-dashboard m-r-10"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="p-b-10">
                            <a href="#orders" class="stext-102 cl2 hov-cl1 trans-04 account-nav-link" data-section="orders">
                                <i class="fa fa-shopping-bag m-r-10"></i>
                                My Orders
                            </a>
                        </li>
                        <li class="p-b-10">
                            <a href="#profile" class="stext-102 cl2 hov-cl1 trans-04 account-nav-link" data-section="profile">
                                <i class="fa fa-user m-r-10"></i>
                                Profile
                            </a>
                        </li>
                        <li class="p-b-10">
                            <a href="#addresses" class="stext-102 cl2 hov-cl1 trans-04 account-nav-link" data-section="addresses">
                                <i class="fa fa-map-marker m-r-10"></i>
                                Addresses
                            </a>
                        </li>
                        <li class="p-b-10">
                            <a href="#security" class="stext-102 cl2 hov-cl1 trans-04 account-nav-link" data-section="security">
                                <i class="fa fa-lock m-r-10"></i>
                                Security
                            </a>
                        </li>
                        <li class="p-t-15 bor12">
                            <a href="/logout" class="stext-102 cl6 hov-cl1 trans-04" onclick="return confirm('Are you sure you want to logout?')">
                                <i class="fa fa-sign-out m-r-10"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Account Content -->
            <div class="col-md-9 p-b-30">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="account-section active">
                    <div class="bor10 p-lr-40 p-t-40 p-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">
                            <i class="fa fa-dashboard m-r-10 cl2"></i>
                            Account Dashboard
                        </h4>
                        
                        <!-- Quick Stats -->
                        <div class="row p-b-30">
                            <div class="col-md-4 p-b-20">
                                <div class="bg-light p-lr-25 p-t-20 p-b-20 txt-center bor10">
                                    <h5 class="mtext-106 cl2">0</h5>
                                    <p class="stext-115 cl6">Total Orders</p>
                                </div>
                            </div>
                            <div class="col-md-4 p-b-20">
                                <div class="bg-light p-lr-25 p-t-20 p-b-20 txt-center bor10">
                                    <h5 class="mtext-106 cl2">$0.00</h5>
                                    <p class="stext-115 cl6">Total Spent</p>
                                </div>
                            </div>
                            <div class="col-md-4 p-b-20">
                                <div class="bg-light p-lr-25 p-t-20 p-b-20 txt-center bor10">
                                    <h5 class="mtext-106 cl2">0</h5>
                                    <p class="stext-115 cl6">Wishlist Items</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="p-t-20">
                            <h5 class="mtext-108 cl2 p-b-20">Recent Activity</h5>
                            <div class="bg-light p-lr-25 p-t-20 p-b-20 bor10">
                                <p class="stext-113 cl6 txt-center">
                                    <i class="fa fa-info-circle m-r-10"></i>
                                    No recent activity. Start shopping to see your order history here!
                                </p>
                                <div class="txt-center p-t-15">
                                    <a href="/shop" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04">
                                        Start Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div id="orders-section" class="account-section" style="display: none;">
                    <div class="bor10 p-lr-40 p-t-40 p-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">
                            <i class="fa fa-shopping-bag m-r-10 cl2"></i>
                            My Orders
                        </h4>
                        
                        <div class="bg-light p-lr-25 p-t-30 p-b-30 bor10">
                            <div class="txt-center">
                                <i class="fa fa-shopping-cart cl6" style="font-size: 48px;"></i>
                                <h5 class="mtext-108 cl6 p-t-20 p-b-10">No Orders Yet</h5>
                                <p class="stext-113 cl6 p-b-20">
                                    You haven't placed any orders yet. Start shopping to see your order history here.
                                </p>
                                <a href="/shop" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04">
                                    Browse Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="account-section" style="display: none;">
                    <div class="bor10 p-lr-40 p-t-40 p-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">
                            <i class="fa fa-user m-r-10 cl2"></i>
                            Profile Information
                        </h4>
                        
                        <form id="profile-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="p-b-20">
                                        <label class="stext-102 cl3 p-b-5">First Name *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="text" name="firstName" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-b-20">
                                        <label class="stext-102 cl3 p-b-5">Last Name *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="text" name="lastName" value="" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-b-20">
                                <label class="stext-102 cl3 p-b-5">Email Address *</label>
                                <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="p-b-30">
                                <label class="stext-102 cl3 p-b-5">Phone Number</label>
                                <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="tel" name="phone" value="">
                            </div>
                            
                            <button type="submit" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Addresses Section -->
                <div id="addresses-section" class="account-section" style="display: none;">
                    <div class="bor10 p-lr-40 p-t-40 p-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">
                            <i class="fa fa-map-marker m-r-10 cl2"></i>
                            Address Book
                        </h4>
                        
                        <div class="bg-light p-lr-25 p-t-30 p-b-30 bor10">
                            <div class="txt-center">
                                <i class="fa fa-home cl6" style="font-size: 48px;"></i>
                                <h5 class="mtext-108 cl6 p-t-20 p-b-10">No Addresses Saved</h5>
                                <p class="stext-113 cl6 p-b-20">
                                    Add your shipping and billing addresses for faster checkout.
                                </p>
                                <button class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04" onclick="alert('Add Address feature coming soon!')">
                                    Add New Address
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div id="security-section" class="account-section" style="display: none;">
                    <div class="bor10 p-lr-40 p-t-40 p-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">
                            <i class="fa fa-lock m-r-10 cl2"></i>
                            Account Security
                        </h4>
                        
                        <form id="password-form">
                            <div class="p-b-20">
                                <label class="stext-102 cl3 p-b-5">Current Password *</label>
                                <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="current_password" required>
                            </div>
                            
                            <div class="p-b-20">
                                <label class="stext-102 cl3 p-b-5">New Password *</label>
                                <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="new_password" required>
                                <small class="stext-115 cl6">Minimum 8 characters with uppercase, lowercase, and number</small>
                            </div>
                            
                            <div class="p-b-30">
                                <label class="stext-102 cl3 p-b-5">Confirm New Password *</label>
                                <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" type="password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.account-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.account-menu li {
    border-bottom: 1px solid #e6e6e6;
}

.account-menu li:last-child {
    border-bottom: none;
}

.account-nav-link {
    display: block;
    padding: 10px 0;
    text-decoration: none;
}

.account-nav-link.active {
    color: #333 !important;
    font-weight: 600;
}

.account-nav-link:hover {
    color: #717fe0 !important;
}

.account-section {
    display: none;
}

.account-section.active {
    display: block;
}

.bg-light {
    background-color: #f8f9fa;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin-left: -15px;
    margin-right: -15px;
}

.col-md-3 {
    flex: 0 0 25%;
    max-width: 25%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-4 {
    flex: 0 0 33.666667%;
    max-width: 33.666667%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-9 {
    flex: 0 0 75%;
    max-width: 75%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-12 {
    flex: 0 0 100%;
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

@media (max-width: 768px) {
    .col-md-3, .col-md-4, .col-md-6, .col-md-9, .col-md-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Account navigation
    const navLinks = document.querySelectorAll('.account-nav-link');
    const sections = document.querySelectorAll('.account-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links and sections
            navLinks.forEach(nl => nl.classList.remove('active'));
            sections.forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show corresponding section
            const sectionId = this.getAttribute('data-section') + '-section';
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.style.display = 'block';
            }
        });
    });
    
    // Profile form submission
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Profile update feature coming soon!');
        });
    }
    
    // Password form submission
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const newPassword = this.new_password.value;
            const confirmPassword = this.confirm_password.value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return;
            }
            
            alert('Password update feature coming soon!');
        });
    }
});
</script> 