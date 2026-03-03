<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PaymentInformationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\FollowSellerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Payment\AamarpayController;
use App\Http\Controllers\Payment\AuthorizenetController;
use App\Http\Controllers\Payment\BkashController;
use App\Http\Controllers\Payment\CybersourceController;
use App\Http\Controllers\Payment\InstamojoController;
use App\Http\Controllers\Payment\IyzicoController;
use App\Http\Controllers\Payment\MercadopagoController;
use App\Http\Controllers\Payment\NagadController;
use App\Http\Controllers\Payment\NgeniusController;
use App\Http\Controllers\Payment\PayhereController;
use App\Http\Controllers\Payment\PaykuController;
use App\Http\Controllers\Payment\PaymobController;
use App\Http\Controllers\Payment\PaypalController;
use App\Http\Controllers\Payment\PaystackController;
use App\Http\Controllers\Payment\RazorpayController;
use App\Http\Controllers\Payment\SslcommerzController;
use App\Http\Controllers\Payment\StripeController;
use App\Http\Controllers\Payment\TapController;
use App\Http\Controllers\Payment\VoguepayController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SizeChartController;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::controller(DemoController::class)->group(function () {
    Route::get('/demo/cron_1', 'cron_1');
    Route::get('/demo/cron_2', 'cron_2');
    Route::get('/convert_assets', 'convert_assets');
    Route::get('/convert_category', 'convert_category');
    Route::get('/convert_tax', 'convertTaxes');
    Route::get('/set-category', 'setCategoryToProductCategory');
    Route::get('/insert_product_variant_forcefully', 'insert_product_variant_forcefully');
    Route::get('/update_seller_id_in_orders/{id_min}/{id_max}', 'update_seller_id_in_orders');
    Route::get('/migrate_attribute_values', 'migrate_attribute_values');
    Route::get('/db-update-languages', [\App\Http\Controllers\DbUpdateController::class, 'updateLanguages']);
});

Route::get('/refresh-csrf', function () {
    return csrf_token();
});

Route::get('/debug-db', function () {
    try {
        \DB::connection()->getPdo();
        return "DB Connection Successful. Host: " . env('DB_HOST');
    } catch (\Exception $e) {
        return "DB Connection Failed. Host: " . env('DB_HOST') . ". Error: " . $e->getMessage();
    }
});

// AIZ Uploader
Route::controller(AizUploadController::class)->group(function () {
    Route::post('/aiz-uploader', 'show_uploader');
    Route::post('/aiz-uploader/upload', 'upload');
    Route::get('/aiz-uploader/get-uploaded-files', 'get_uploaded_files');
    Route::post('/aiz-uploader/get_file_by_ids', 'get_preview_files');
    Route::get('/aiz-uploader/download/{id}', 'attachment_download')->name('download_attachment');
});

Route::group(['middleware' => ['prevent-back-history', 'handle-demo-login']], function () {
    Auth::routes(['verify' => true]);
});

// Login
Route::controller(LoginController::class)->group(function () {
    Route::get('/logout', 'logout')->name('logout');
    Route::get('/social-login/redirect/{provider}', 'redirectToProvider')->name('social.login');
    Route::get('/social-login/{provider}/callback', 'handleProviderCallback')->name('social.callback');
    //Apple Callback
    Route::post('/apple-callback', 'handleAppleCallback');
    Route::get('/account-deletion', 'account_deletion')->name('account_delete');
    Route::get('/handle-demo-login', 'handle_demo_login')->name('handleDemoLogin');
});

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/resend', 'resend')->name('verification.resend');
    Route::get('/verification-confirmation/{code}', 'verification_confirmation')->name('email.verification.confirmation');
});

// Root Redirect
Route::get('/', [App\Http\Controllers\LocalizationController::class, 'redirect']);

// Sitemap Index
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);

// Temporary secure cache-clear route (Moved outside)
Route::get('/force-cache-clear/{key}', function ($key) {
    if ($key == '23471405') { // Using SYSTEM_KEY from .env
        Artisan::call('optimize:clear');
        return "Cache cleared successfully";
    }
    return "Unauthorized";
});

// Database Update Routes (Moved outside)
Route::get('/db-update-languages', [App\Http\Controllers\DbUpdateController::class, 'updateLanguages']);

Route::group(['prefix' => '{locale}', 'where' => ['locale' => '[a-z]{2}']], function () {

    Route::controller(HomeController::class)->group(function () {
        // ... (existing routes)
        // Home Page
        Route::get('/', 'index')->name('home');

        // Localized Sitemap
        Route::get('/sitemap.xml', [SitemapController::class, 'show'])->name('sitemap.localized');

        Route::post('/home/section/featured', 'load_featured_section')->name('home.section.featured');
        Route::post('/home/section/todays-deal', 'load_todays_deal_section')->name('home.section.todays_deal');
        Route::post('/home/section/best-selling', 'load_best_selling_section')->name('home.section.best_selling');
        Route::post('/home/section/newest-products', 'load_newest_product_section')->name('home.section.newest_products');
        Route::post('/home/section/home-categories', 'load_home_categories_section')->name('home.section.home_categories');
        Route::post('/home/section/best-sellers', 'load_best_sellers_section')->name('home.section.best_sellers');
        Route::post('/home/section/preorder-products', 'load_preorder_featured_products_section')->name('home.section.preorder_products');

        //category dropdown menu ajax call
        Route::post('/category/nav-element-list', 'get_category_items')->name('category.elements');

        //Flash Deal Details Page
        Route::get('/flash-deals', 'all_flash_deals')->name('flash-deals')->middleware('portfolio-view');
        Route::get('/flash-deal/{slug}', 'flash_deal_details')->name('flash-deal-details');

        //Todays Deal Details Page
        Route::get('/todays-deal', 'todays_deal')->name('todays-deal')->middleware('portfolio-view');

        //Best Selling Page
        Route::get('/best-selling', 'best_selling')->name('best-selling')->middleware('portfolio-view');
        Route::get('/same-seller-products/{slug}', 'same_sellers_products')->name('same_seller_products')->middleware('portfolio-view');

        //Featured Products Page
        Route::get('/featured-products', 'featured_products')->name('featured-products');

        Route::get('/product/{slug}', 'product')->name('product')->middleware('portfolio-view');
        Route::post('/product/variant-price', 'variant_price')->name('products.variant_price');
        Route::get('/shop/{slug}', 'shop')->name('shop.visit')->middleware('portfolio-view');
        Route::get('/shop/{slug}/{type}', 'filter_shop')->name('shop.visit.type');
        Route::get('/product-reviews', 'product_reviews')->name('products.reviews');

        Route::get('/customer-packages', 'premium_package_index')->name('customer_packages_list_show');

        Route::get('/brands', 'all_brands')->name('brands.all');
        Route::get('/categories', 'all_categories')->name('categories.all')->middleware('portfolio-view');
        Route::get('/sellers', 'all_seller')->name('sellers');
        Route::get('/coupons', 'all_coupons')->name('coupons.all');
        Route::get('/inhouse', 'inhouse_products')->name('inhouse.all');


        // Policies
        Route::get('/seller-policy', 'sellerpolicy')->name('sellerpolicy');
        Route::get('/return-policy', 'returnpolicy')->name('returnpolicy');
        Route::get('/support-policy', 'supportpolicy')->name('supportpolicy');
        Route::get('/terms', 'terms')->name('terms');
        Route::get('/privacy-policy', 'privacypolicy')->name('privacypolicy');

        Route::get('/track-your-order', 'trackOrder')->name('orders.track');
        Route::get('/dashboard', 'dashboard')->name('dashboard');

        // Auth Pages (localized)
        Route::get('/login', 'login')->name('login');
        Route::get('/login', 'login')->name('user.login');
        Route::get('/seller-login', 'login')->name('seller.login');
        Route::get('/delivery-boy-login', 'login')->name('deliveryboy.login');
        Route::get('/registration', 'registration')->name('register');
        Route::get('/registration', 'registration')->name('user.registration');

        // Forgot Password (localized)
        Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
    });

    // Cart Login Submit
    Route::post('/cart-login', [LoginController::class, 'login'])->name('cart.login.submit');

    // Compare
    Route::controller(\App\Http\Controllers\CompareController::class)->group(function () {
        Route::get('/compare', 'index')->name('compare');
        Route::post('/compare/add', 'addToCompare')->name('compare.add');
        Route::post('/compare/add', 'addToCompare')->name('compare.addToCompare');
        Route::get('/compare/reset', 'reset')->name('compare.reset');
        Route::get('/compare/details/{unique_identifier}', 'details')->name('compare.details');
    });

    // Customer Notifications
    Route::get('/all-notifications', [\App\Http\Controllers\NotificationController::class, 'customerIndex'])->name('customer.all-notifications');
    Route::get('/notification-read/{id}', [\App\Http\Controllers\NotificationController::class, 'nonLinkableNotificationRead'])->name('non-linkable-notification-read');

    // Language Switch
    Route::post('/language', [LanguageController::class, 'changeLanguage'])->name('language.change');

    // Currency Switch
    Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');

    // Subscribers
    Route::post('/subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');

    // Size Chart Show
    Route::get('/size-charts-show/{id}', [SizeChartController::class, 'show'])->name('size-charts-show');

    // Classified Product
    Route::controller(CustomerProductController::class)->group(function () {
        Route::get('/customer-products', 'customer_products_listing')->name('customer.products');
        Route::get('/customer-products?category={category_slug}', 'search')->name('customer_products.category');
        Route::get('/customer-products?city={city_id}', 'search')->name('customer_products.city');
        Route::get('/customer-products?q={search}', 'search')->name('customer_products.search');
        Route::get('/customer-product/{slug}', 'customer_product')->name('customer.product');
    });

    // Shops
    Route::resource('shops', ShopController::class)->middleware('handle-demo-login');
    Route::controller(ShopController::class)->group(function () {
        Route::get('/shop/registration/verification', 'verifyRegEmailorPhone')->name('shop-reg.verification');
        Route::post('/shop/registration/verification-code-send', 'sendRegVerificationCode')->name('shop-reg.verification_code_send');
        Route::get('/shop/registration/verify-code/{id}', 'regVerifyCode')->name('shop-reg.verify_code');
        Route::post('/shop/registration/verification-code-confirmation', 'regVerifyCodeConfirmation')->name('shop-reg.verify_code_confirmation');
    });

    // Search
    Route::controller(SearchController::class)->group(function () {
        Route::get('/search', 'index')->name('search');
        Route::get('/search?keyword={search}', 'index')->name('suggestion.search');
        Route::get('/search2', 'index2')->name('suggestion.search2');
        Route::post('/ajax-search', 'ajax_search')->name('search.ajax');
        Route::get('/category/{category_slug}', 'listingByCategory')->name('products.category');
        Route::get('/brand/{brand_slug}', 'listingByBrand')->name('products.brand');
    });

    // Cart
    Route::controller(CartController::class)->group(function () {
        Route::get('/cart', 'index')->name('cart');
        Route::post('/cart/show-cart-modal', 'showCartModal')->name('cart.showCartModal');
        Route::post('/cart/show-variant-canvas', 'selectVariantCanvas')->name('cart.selectVariantCanvas');
        Route::post('/cart/addtocart', 'addToCart')->name('cart.addToCart');
        Route::post('/cart/removeFromCart', 'removeFromCart')->name('cart.removeFromCart');
        Route::post('/cart/updateQuantity', 'updateQuantity')->name('cart.updateQuantity');
        Route::post('/cart/updateCartStatus', 'updateCartStatus')->name('cart.updateCartStatus');
    });

    // Checkout Routs
    Route::group(['prefix' => 'checkout'], function () {
        Route::controller(CheckoutController::class)->group(function () {
            Route::get('/', 'index')->name('checkout');
            Route::get('/shipping_info', 'get_shipping_info')->name('checkout.shipping_info');
            Route::any('/delivery-info', 'store_shipping_info')->name('checkout.store_shipping_infostore');
            Route::post('/payment-select', 'store_delivery_info')->name('checkout.store_delivery_info');
            Route::post('/payment', 'checkout')->name('payment.checkout');
            Route::get('/order-confirmed', 'order_confirmed')->name('order_confirmed');
            Route::post('/apply-coupon-code', 'apply_coupon_code')->name('checkout.apply_coupon_code');
            Route::post('/remove-coupon-code', 'remove_coupon_code')->name('checkout.remove_coupon_code');
            Route::post('/guest-customer-info-check', 'guestCustomerInfoCheck')->name('guest_customer_info_check');
            Route::post('/updateDeliveryAddress', 'updateDeliveryAddress')->name('checkout.updateDeliveryAddress');
            Route::post('/updateBillingAddress', 'updateBillingAddress')->name('checkout.updateBillingAddress');
            Route::post('/updateDeliveryInfo', 'updateDeliveryInfo')->name('checkout.updateDeliveryInfo');
        });
    });

    Route::group(['middleware' => ['customer', 'verified', 'unbanned']], function () {

        // Purchase History
        Route::resource('purchase_history', PurchaseHistoryController::class);
        Route::controller(PurchaseHistoryController::class)->group(function () {
            Route::get('/purchase_history/details/{id}', 'purchase_history_details')->name('purchase_history.details');
            Route::get('/purchase_history/destroy/{id}', 'order_cancel')->name('purchase_history.destroy');
            Route::get('digital-purchase-history', 'digital_index')->name('digital_purchase_history.index');
            Route::get('/digital-products/download/{id}', 'download')->name('digital-products.download');

            Route::get('/re-order/{id}', 're_order')->name('re_order');
            Route::get('/purchase_history_filter', 'filterOrders')->name('purchase_history.filter');
        });

        // Wishlist
        Route::resource('wishlists', WishlistController::class);
        Route::post('/wishlists/remove', [WishlistController::class, 'remove'])->name('wishlists.remove');

        //Follow
        Route::controller(FollowSellerController::class)->group(function () {
            Route::get('/followed-seller', 'index')->name('followed_seller');
            Route::get('/followed-seller/store', 'store')->name('followed_seller.store');
            Route::get('/followed-seller/remove', 'remove')->name('followed_seller.remove');
        });

        // Wallet
        Route::controller(WalletController::class)->group(function () {
            Route::get('/wallet', 'index')->name('wallet.index');
            Route::post('/recharge', 'recharge')->name('wallet.recharge');
            Route::get('/wallet_payment_email_test', 'wallet_payment_email_test')->name('wallet.wallet_payment_email_test');
        });

        // Support Ticket
        Route::resource('support_ticket', SupportTicketController::class);
        Route::post('support_ticket/reply', [SupportTicketController::class, 'seller_store'])->name('support_ticket.seller_store');

        // Customer Package
        Route::post('/customer-packages/purchase', [CustomerPackageController::class, 'purchase_package'])->name('customer_packages.purchase');

        // Customer Product
        Route::resource('customer_products', CustomerProductController::class);
        Route::controller(CustomerProductController::class)->group(function () {
            Route::get('/customer_products/{id}/edit', 'edit')->name('customer_products.edit');
            Route::post('/customer_products/published', 'updatePublished')->name('customer_products.published');
            Route::post('/customer_products/status', 'updateStatus')->name('customer_products.update.status');
            Route::get('/customer_products/destroy/{id}', 'destroy')->name('customer_products.destroy');
        });

        // Product Review
        Route::post('/product-review-modal', [ReviewController::class, 'product_review_modal'])->name('product_review_modal');

        Route::post('/order/re-payment', [CheckoutController::class, 'orderRePayment'])->name('order.re_payment');

        // Product Query
        Route::resource('product-queries', ProductQueryController::class);
    });

    Route::controller(PageController::class)->group(function () {
        //mobile app balnk page for webview
        Route::get('/mobile-page/{slug}', 'mobile_custom_page')->name('mobile.custom-pages');

        //Custom page
        Route::get('/{slug}', 'show_custom_page')->name('custom-pages.show_custom_page');
    });

    Route::controller(ContactController::class)->group(function () {
        Route::post('/contact', 'contact')->name('contact');
    });

    Route::controller(PaymentInformationController::class)->group(function () {
        Route::post('/payment-informations/store', 'store')->name('payment_informations.store');
        Route::get('/payment-informations/edit/{id}', 'edit')->name('payment_informations.edit');
        Route::post('/payment-informations/update/{id}', 'update')->name('payment_informations.update');
        Route::get('/payment-informations/destroy/{id}', 'destroy')->name('payment_informations.destroy');
        Route::get('/payment-informations/set-default/{id}', 'set_default')->name('payment_informations.set_default');

    });

    Route::controller(BlogController::class)->group(function () {
        Route::get('/blog', 'all_blog')->name('blog');
        Route::get('/blog/{slug}', 'blog_details')->name('blog.details');
        Route::post('/blog/generate-slug', 'generateSlug')->name('generate.slug');

    });
});

Route::controller(PageController::class)->group(function () {
    // Re-added for the cases where no locale is present, but they will be redirected anyway by middleware
    Route::get('/{slug}', 'show_custom_page')->name('custom-pages.show_without_locale');
});
