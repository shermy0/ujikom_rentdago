<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\ProfileCustomerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerRequestController;
use App\Http\Controllers\AccountSwitchController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Seller\MyPageSellerController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerProductRentalController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Seller\ScanQRController;
use App\Http\Controllers\CustomerShopController;
use App\Http\Controllers\Seller\SellerNotificationController;
use App\Http\Controllers\Seller\CourierController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CustomerPenaltyController;
use App\Http\Controllers\Customer\PickupTrackingController;
use App\Http\Controllers\Kurir\DeliveryTrackingController;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Search routes (public)
Route::get('/search', [HomeController::class, 'index'])->name('search');
Route::get('/search/suggest', [HomeController::class, 'suggest'])->name('search.suggest');

// Check rent availability (public or auth)
Route::post('/check-rent-availability', [HomeController::class, 'checkAvailability'])
    ->name('customer.rent.check');

/* =======================
| AUTH (GUEST)
======================= */
Route::middleware(['guest'])->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/register', fn() => view('auth.register'))->name('auth.register');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('auth.forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot.password.post');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
        ->name('auth.reset-password-form');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordFromLink'])
        ->name('auth.reset.password.post');
});

/* =======================
| AUTH ROUTES
======================= */
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.post');
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);

Route::get('/verify-otp', function () {
    if (!request()->has('phone')) {
        return redirect()->route('auth.register')
            ->with('error', 'Nomor telepon tidak ditemukan');
    }
    return view('auth.verify-otp');
})->name('auth.verify.otp');

Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify.otp.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

/* =======================
| AUTHENTICATED USER ROUTES
======================= */
Route::middleware(['auth', 'role:customer'])->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileCustomerController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileCustomerController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileCustomerController::class, 'update'])->name('profile.update');

    // Reset Password Routes
    Route::get('/profile/reset-password', [ProfileCustomerController::class, 'showResetPassword'])
        ->name('profile.reset.password');
    Route::put('/profile/reset-password', [ProfileCustomerController::class, 'resetPassword'])
        ->name('profile.reset.password');

    // Request OTP untuk reset password
    Route::post('/profile/request-password-reset-otp', [ProfileCustomerController::class, 'requestPasswordResetOtp'])
        ->name('profile.request.password.reset.otp');

    // Form reset password baru (setelah verifikasi OTP)
    Route::get('/profile/reset-password-form', [ProfileCustomerController::class, 'showResetPasswordForm'])
        ->name('profile.reset.password.form');

    // Update password baru (setelah verifikasi OTP)
    Route::put('/profile/update-password-with-otp', [ProfileCustomerController::class, 'updatePasswordWithOtp'])
        ->name('profile.update.password.with.otp');

    // Route untuk verifikasi OTP profile
    Route::get('/customer/profile/verify-otp', [ProfileCustomerController::class, 'showVerifyOtp'])
        ->name('profile.verify.otp');
    Route::post('/profile/verify-otp', [ProfileCustomerController::class, 'verifyOtp'])
        ->name('profile.verify.otp.post');
    Route::post('/profile/resend-otp', [ProfileCustomerController::class, 'resendOtp'])
        ->name('profile.resend.otp');

    // Account switch (khusus seller)
    Route::post('/account/switch', [AccountSwitchController::class, 'switch'])->name('account.switch');

    // API Routes for Customers (Session based)
    Route::prefix('api')->group(function () {
        Route::get('/notifications/all', [\App\Http\Controllers\Customer\CustomerNotificationController::class, 'getAllNotifications']);
        Route::post('/notifications/mark-read', [\App\Http\Controllers\Customer\CustomerNotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Customer\CustomerNotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/clear-all', [\App\Http\Controllers\Customer\CustomerNotificationController::class, 'clearAll']);
    });
});

/* =======================
| ADMIN ROUTES
======================= */
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile routes
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [ProfileController::class, 'update'])->name('update');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        });

        // Users
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Shops Map (placed before resource to avoid conflict with {shop} param)
        Route::get('/shops/map', [App\Http\Controllers\Admin\ShopMapController::class, 'index'])->name('shops.map');
        Route::resource('shops', ShopController::class)->only(['index', 'show']);


        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/update', [SettingController::class, 'update'])->name('update');
        });

        // Seller Requests
        Route::get('/seller-requests', [SellerRequestController::class, 'index'])->name('seller-requests.index');
        Route::get('/seller-requests/{id}', [SellerRequestController::class, 'show'])->name('seller-requests.show');
        Route::post('/seller-requests/{id}/approve', [SellerRequestController::class, 'approve'])->name('seller-requests.approve');
        Route::post('/seller-requests/{id}/reject', [SellerRequestController::class, 'reject'])->name('seller-requests.reject');

        // Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{id}', [ProductController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
            Route::delete('/images/{id}', [ProductController::class, 'deleteImage'])->name('images.destroy');
        });
        Route::post('shops/{shop}/toggle-status', [App\Http\Controllers\Admin\ShopController::class, 'toggleStatus'])
            ->name('shops.toggle-status');
        // Product Sewa
        Route::prefix('product-sewa')->name('product_sewa.')->group(function () {
            Route::get('/', [RentalController::class, 'index'])->name('index');
            Route::get('/create', [RentalController::class, 'create'])->name('create');
            Route::post('/', [RentalController::class, 'store'])->name('store');
            Route::get('/{id}', [RentalController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RentalController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RentalController::class, 'update'])->name('update');
            Route::delete('/{id}', [RentalController::class, 'destroy'])->name('destroy');
        });

        // Orders (Read-only)
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
        });

        // Reports / Laporan
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('pdf');
        });
    });

/* =======================
| SELLER ROUTES
======================= */
Route::middleware(['auth', 'role:seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        Route::prefix('couriers')->name('couriers.')->group(function () {
            Route::get('/', [CourierController::class, 'index'])->name('index');
            Route::get('/create', [CourierController::class, 'create'])->name('create');
            Route::post('/', [CourierController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [CourierController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CourierController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [CourierController::class, 'toggleStatus'])->name('toggle');
            Route::delete('/{id}', [CourierController::class, 'destroy'])->name('destroy');
        });



        Route::post(
            '/courier-assignments/{order}/assign-courier',
            [SellerOrderController::class, 'assignCourier']
        )->name('courier-assignments.assign-courier');

        Route::post(
            '/courier-assignments/{order}/reassign-courier',
            [\App\Http\Controllers\Seller\CourierAssignmentController::class, 'manualReassignCourier']
        )->name('courier-assignments.reassign-courier');

        Route::post(
            '/courier-assignments/{order}/assign-return-courier',
            [\App\Http\Controllers\Seller\CourierAssignmentController::class, 'assignReturnCourier']
        )->name('courier-assignments.assign-return-courier');

        Route::get('/dashboard', [SellerController::class, 'index'])->name('dashboard.index');
        Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');


        Route::prefix('api')->name('api.')->group(function () {
            // ✅ Unified endpoint (INI SAJA)
            Route::get('/notifications/all', [SellerNotificationController::class, 'getAllNotifications'])
                ->name('notifications.all');

            Route::post('/notifications/mark-read', [SellerNotificationController::class, 'markAsRead'])
                ->name('notifications.mark-read');

            Route::post('/notifications/mark-all-read', [SellerNotificationController::class, 'markAllAsRead'])
                ->name('notifications.mark-all-read');

            // ✅ TAMBAHKAN 3 ROUTE INI UNTUK ORDER BADGE
            Route::get('/orders/unread-count', [SellerNotificationController::class, 'getUnreadOrderCount'])
                ->name('orders.unread-count');

            Route::post('/orders/mark-read', [SellerNotificationController::class, 'markOrdersAsRead'])
                ->name('orders.mark-read');

            Route::post('/orders/{id}/mark-read', [SellerNotificationController::class, 'markSingleOrderAsRead'])
                ->name('orders.mark-single-read');
        });


        // Orders
        Route::get('/orders', [SellerController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [SellerController::class, 'showOrder'])->name('orders.show');

        // Courier Assignments
        Route::get('/courier-assignments', [SellerController::class, 'courierAssignments'])->name('courier-assignments');
        Route::get('/courier-assignments/{id}/assign', [SellerController::class, 'showAssignCourier'])->name('courier-assignments.assign-page');


        // My Page Routes
        Route::prefix('mypage')->name('mypage.')->group(function () {
            Route::get('/', [MyPageSellerController::class, 'index'])->name('index');
            Route::get('/settings', [MyPageSellerController::class, 'settings'])->name('settings');
            Route::post('/toggle-shop-status', [MyPageSellerController::class, 'toggleShopStatus'])->name('toggle-shop-status');

            // Edit Akun
            Route::get('/edit-account', [MyPageSellerController::class, 'editAccount'])->name('edit-account');
            Route::post('/update-account', [MyPageSellerController::class, 'updateAccount'])->name('update-account');

            // Create & Edit Toko
            Route::get('/create-shop', [MyPageSellerController::class, 'createShop'])->name('create-shop');
            Route::post('/store-shop', [MyPageSellerController::class, 'storeShop'])->name('store-shop');
            Route::get('/edit-shop', [MyPageSellerController::class, 'editShop'])->name('edit-shop');
            Route::post('/update-shop', [MyPageSellerController::class, 'updateShop'])->name('update-shop');
        });


        // Products Routes
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [SellerProductController::class, 'index'])->name('index');
            Route::get('/create', [SellerProductController::class, 'create'])->name('create');
            Route::post('/', [SellerProductController::class, 'store'])->name('store');
            Route::get('/{id}', [SellerProductController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SellerProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SellerProductController::class, 'update'])->name('update');
            Route::delete('/{id}', [SellerProductController::class, 'destroy'])->name('destroy');
            Route::delete('/images/{id}', [SellerProductController::class, 'deleteImage'])->name('images.destroy');

        });



        // Rentals Routes
        Route::prefix('rentals')->name('rentals.')->group(function () {
            Route::get('/', [SellerProductRentalController::class, 'index'])->name('index');
            Route::get('/create', [SellerProductRentalController::class, 'create'])->name('create');
            Route::post('/', [SellerProductRentalController::class, 'store'])->name('store');
            Route::get('/{id}', [SellerProductRentalController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SellerProductRentalController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SellerProductRentalController::class, 'update'])->name('update');
            Route::delete('/{id}', [SellerProductRentalController::class, 'destroy'])->name('destroy');
        });

        //voucher seller route
        Route::prefix('vouchers')->name('vouchers.')->group(function () {
            Route::get('/', [App\Http\Controllers\Seller\SellerVoucherController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Seller\SellerVoucherController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Seller\SellerVoucherController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Seller\SellerVoucherController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Seller\SellerVoucherController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Seller\SellerVoucherController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Seller\SellerVoucherController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [App\Http\Controllers\Seller\SellerVoucherController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Scan QR Routes
        Route::prefix('scan')->name('scan.')->group(function () {
            Route::get('/', [ScanQRController::class, 'index'])->name('index');
            Route::post('/verify', [ScanQRController::class, 'verify'])->name('verify');

            // Step 2 - halaman foto serah barang
            Route::get('/handover-proof', function () {
                return view('seller.scan.handover-proof');
            })->name('handover-proof');

            // Upload foto serah barang
            Route::post(
                '/handover-proof/upload',
                [ScanQRController::class, 'uploadStartProof']
            )->name('uploadStartProof');

            Route::post('/start', [ScanQRController::class, 'start'])->name('start');
            Route::post('/complete', [ScanQRController::class, 'complete'])->name('complete');
        });

        Route::post('/scan/return', [ScanQRController::class, 'returnItem'])->name('scan.return');
    });

/* =======================
| CUSTOMER ROUTES
======================= */
Route::middleware(['auth', 'role:customer'])->group(function () {

    // Seller Request Routes
    Route::get('/seller-request/create', [SellerRequestController::class, 'create'])->name('seller-request.create');
    Route::post('/seller-request', [SellerRequestController::class, 'store'])->name('seller-request.store');
    Route::get('/my-seller-request', [SellerRequestController::class, 'myRequest'])->name('seller-request.my');

    // Product Detail
    Route::get('/produk/{slug}/{product}', [HomeController::class, 'show'])->name('customer.product.detail');
    // Tambahkan route checkout (hanya show saja, store sudah ada)
    Route::get('/checkout/{product}', [HomeController::class, 'checkout'])
        ->name('customer.checkout');
});

// Customer Orders & Shop (auth, tidak perlu role spesifik)
// Customer Orders & Shop (auth, tidak perlu role spesifik)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Orders
    Route::get('/orders/index', [CustomerOrderController::class, 'index'])->name('order.index');
    Route::post('/product/{product}/order', [CustomerOrderController::class, 'store'])->name('order.store');
    Route::get('/order/show/{id}', [CustomerOrderController::class, 'show'])->name('order.show');
    Route::get('/order/{id}/payment', [CustomerOrderController::class, 'show'])->name('order.payment');
    Route::get('/order/payment/finish', [CustomerOrderController::class, 'finish'])->name('order.payment.finish');
    Route::post('/order/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('order.cancel');

    //DENDA
    Route::get('/penalty/{id}/pay', [CustomerPenaltyController::class, 'pay'])
        ->name('penalty.pay');

    // ✅ Regenerate Token (posisi di sini, TANPA /customer lagi)
    Route::post('/order/{id}/regenerate-token', [CustomerOrderController::class, 'regenerateToken'])
        ->name('order.regenerate-token');

    // ✅ Request Return Pickup
    Route::post('/order/{id}/request-return', [CustomerOrderController::class, 'requestReturnPickup'])
        ->name('order.request-return');

    // Pickup Flow Routes
    Route::post('/order/{id}/pickup/start', [PickupTrackingController::class, 'startTracking'])
        ->name('order.pickup.start');
    Route::post('/order/{id}/pickup/arrive', [PickupTrackingController::class, 'stopTracking'])
        ->name('order.pickup.arrive');

    // Tracking update
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::post('/update', [PickupTrackingController::class, 'updateLocation'])->name('update');
    });

    // Shop Profile (bisa diakses semua authenticated user)
    Route::get('/shop/{slug}', [CustomerShopController::class, 'show'])->name('shop.profile');



    //Vouchers customer route
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        // My Vouchers - customer.vouchers.my
        Route::get('/my', [App\Http\Controllers\Customer\CustomerVoucherController::class, 'myVouchers'])
            ->name('my');

        // ✅ TAMBAHKAN ROUTE INI
        Route::get('/available', [App\Http\Controllers\Customer\CustomerVoucherController::class, 'getAvailable'])
            ->name('available');

        // Validate Voucher - customer.vouchers.validate
        Route::post('/validate', [App\Http\Controllers\Customer\CustomerVoucherController::class, 'validate'])
            ->name('validate');

        // Claim Voucher - customer.vouchers.claim
        Route::post('/claim/{voucherId}', [App\Http\Controllers\Customer\CustomerVoucherController::class, 'claim'])
            ->name('claim');
    });

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])
        ->name('addresses.edit');

    Route::put('/addresses/{address}', [AddressController::class, 'update'])
        ->name('addresses.update');
    Route::post(
        '/addresses/{address}/set-default',
        [AddressController::class, 'setDefault']
    )->name('addresses.set-default');
});


/* =======================
| COURIER ROUTES
======================= */
Route::middleware(['auth', 'role:courier'])
    ->prefix('kurir')
    ->name('kurir.')
    ->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Kurir\KurirController::class, 'index'])->name('dashboard');
        Route::get('/orders', [App\Http\Controllers\Kurir\KurirController::class, 'orders'])->name('orders');

        Route::get('/history', [App\Http\Controllers\Kurir\KurirController::class, 'history'])->name('history');
        Route::get('/profile', [App\Http\Controllers\Kurir\KurirController::class, 'profile'])->name('profile');

        // Profile Edit Routes
        Route::get('/profile/edit', [App\Http\Controllers\Kurir\KurirController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile/update', [App\Http\Controllers\Kurir\KurirController::class, 'updateProfile'])->name('profile.update');

        // Password Change Routes
        Route::get('/profile/change-password', [App\Http\Controllers\Kurir\KurirController::class, 'showChangePassword'])->name('profile.change-password');
        Route::put('/profile/update-password', [App\Http\Controllers\Kurir\KurirController::class, 'updatePassword'])->name('profile.update-password');

        // Map Route for Delivery Orders
        Route::get('/orders/{orderId}/map', [App\Http\Controllers\Kurir\KurirController::class, 'showMap'])->name('map');

        // Courier Action Routes (New Flow with Separated Controller)
        Route::post('/start-trip/{orderId}', [DeliveryTrackingController::class, 'startTracking'])->name('start-trip');
        Route::post('/update-location', [DeliveryTrackingController::class, 'updateLocation'])->name('update-location');
        Route::post('/confirm-arrived/{orderId}', [DeliveryTrackingController::class, 'stopTracking'])->name('confirm-arrived');
        Route::post('/hand-over', [App\Http\Controllers\Kurir\KurirController::class, 'handOver'])->name('hand-over');

        // Legacy/Other Routes
        Route::post('/orders/{orderId}/start-delivery', [DeliveryTrackingController::class, 'startTracking'])->name('orders.start'); // Map to same logic
        Route::post('/orders/{orderId}/arrived', [DeliveryTrackingController::class, 'stopTracking'])->name('orders.arrived'); // Map to same logic
        Route::post('/orders/{orderId}/complete-delivery', [App\Http\Controllers\Kurir\KurirController::class, 'handOver'])->name('orders.complete'); // Map to same logic

        // Courier Action Routes
        Route::post('/orders/{orderId}/reject', [App\Http\Controllers\Kurir\KurirController::class, 'rejectDelivery'])->name('orders.reject');
        Route::post('/orders/{orderId}/accept', [App\Http\Controllers\Kurir\KurirController::class, 'acceptDelivery'])->name('orders.accept');
        Route::post('/orders/{orderId}/start-return', [App\Http\Controllers\Kurir\KurirController::class, 'startReturn'])->name('orders.start-return');
        Route::post('/orders/{orderId}/confirm-return', [App\Http\Controllers\Kurir\KurirController::class, 'confirmReturn'])->name('orders.confirm-return');

        // ✅ TAMBAHKAN SCAN QR ROUTES DI SINI
        Route::get('/delivery-photo/{shipmentId}', [App\Http\Controllers\Kurir\KurirQrController::class, 'showPhotoPage'])->name('delivery-photo.show');
        Route::post('/delivery-photo/complete', [App\Http\Controllers\Kurir\KurirQrController::class, 'completeDelivery'])->name('delivery-photo.complete');

        // Pickup Routes
        Route::get('/orders/{orderId}/pickup-scan', [App\Http\Controllers\Kurir\PickupController::class, 'showScan'])->name('pickup.scan');
        Route::post('/pickup/verify', [App\Http\Controllers\Kurir\PickupController::class, 'verifyPickup'])->name('pickup.verify');


        // API Routes for Courier Notifications
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/notifications/all', [\App\Http\Controllers\Kurir\CourierNotificationController::class, 'getAllNotifications'])->name('notifications.all');
            Route::post('/notifications/mark-read', [\App\Http\Controllers\Kurir\CourierNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Kurir\CourierNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::post('/notifications/clear-all', [\App\Http\Controllers\Kurir\CourierNotificationController::class, 'clearAll'])->name('notifications.clear-all');
        });
    });

    // Route mock callback midtrans untuk fungsi denda
Route::post('/dev/midtrans/penalty-callback', [\App\Http\Controllers\Customer\CustomerPenaltyController::class, 'simulateMidtransCallback']);
