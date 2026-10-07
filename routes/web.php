<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\PublicContentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('api/public')->name('api.public.')->group(function () {
    Route::get('/site-settings', [PublicApiController::class, 'siteSettings'])->name('site-settings');
    Route::get('/home', [PublicApiController::class, 'home'])->name('home');
    Route::get('/pages/about', [PublicApiController::class, 'about'])->name('pages.about');
    Route::get('/pages/contact', [PublicApiController::class, 'contact'])->name('pages.contact');
    Route::get('/reviews', [PublicApiController::class, 'reviews'])->name('reviews');
    Route::post('/reviews', [PublicApiController::class, 'storeReview'])->name('reviews.store')->middleware('auth:customer');
    Route::get('/products', [PublicApiController::class, 'products'])->name('products');
    Route::get('/products/{slug}', [PublicApiController::class, 'product'])->name('products.show');
    Route::post('/contact', [PublicApiController::class, 'storeContactInquiry'])->name('contact');
    Route::post('/order-tracking', [PublicApiController::class, 'trackOrder'])->name('order-tracking');
});

Route::prefix('api/customer')->name('api.customer.')->group(function () {
    Route::post('/register', [CustomerController::class, 'register'])->name('register');
    Route::post('/login', [CustomerController::class, 'login'])->name('login');
    Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');

    Route::middleware('auth:customer')->group(function () {
        Route::get('/cart', [CustomerController::class, 'cartIndex']);
        Route::post('/cart', [CustomerController::class, 'cartAdd']);
        Route::patch('/cart/{id}', [CustomerController::class, 'cartUpdate']);
        Route::delete('/cart/{id}', [CustomerController::class, 'cartRemove']);
        Route::get('/wishlist', [CustomerController::class, 'wishlistIndex']);
        Route::post('/wishlist/{productId}', [CustomerController::class, 'wishlistToggle']);
        Route::post('/order/whatsapp', [CustomerController::class, 'placeWhatsAppOrder']);

        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('orders');
            Route::get('/wishlist-products', [CustomerDashboardController::class, 'wishlistProducts'])->name('wishlist-products');
            Route::get('/invoices', [CustomerDashboardController::class, 'invoices'])->name('invoices');
            Route::post('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
            Route::put('/password', [CustomerDashboardController::class, 'changePassword'])->name('password.update');
        });

        Route::post('/forgot-password', [CustomerController::class, 'forgotPassword'])->withoutMiddleware('auth:customer');
    });
});

Route::get('/my-account', [CustomerDashboardController::class, 'dashboard'])
    ->middleware('auth:customer')
    ->name('customer.dashboard');

Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/shop', [PublicController::class, 'shop'])->name('public.shop');
Route::get('/product/{slug}', [PublicController::class, 'product'])->name('public.product.show');
Route::get('/cart', [PublicController::class, 'cart'])->name('public.cart');
Route::get('/checkout', [PublicController::class, 'checkout'])->name('public.checkout');
Route::get('/custom-order', [PublicController::class, 'customOrder'])->name('public.custom-order');
Route::get('/reviews', [PublicController::class, 'reviews'])->name('public.reviews');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::get('/order-tracking', [PublicController::class, 'orderTracking'])->name('public.order-tracking');
Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');
Route::get('/privacy-policy', [PublicController::class, 'privacyPolicy'])->name('public.privacy-policy');
Route::get('/shipping-policy', [PublicController::class, 'shippingPolicy'])->name('public.shipping-policy');
Route::get('/returns', [PublicController::class, 'returns'])->name('public.returns');
Route::get('/sitemap', [PublicController::class, 'sitemap'])->name('public.sitemap');
Route::get('/sign-in', [PublicController::class, 'login'])->name('public.sign-in');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->middleware('auth')->name('dashboard.stats');

Route::get('/api/chat/sessions', [\App\Http\Controllers\AiChatController::class, 'index'])->name('chat.sessions.index');
Route::get('/api/chat/sessions/{chatSession}', [\App\Http\Controllers\AiChatController::class, 'show'])->name('chat.sessions.show');
Route::post('/api/chat/message', [\App\Http\Controllers\AiChatController::class, 'store'])->name('chat.message.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings.edit');
    Route::get('/public-content', [PublicContentController::class, 'edit'])->name('public-content.edit');
    Route::post('/public-content/site-settings', [PublicContentController::class, 'updateSiteSettings'])
        ->name('public-content.site-settings.update');
    Route::post('/public-content/seo-settings', [PublicContentController::class, 'updateSeoSettings'])
        ->name('public-content.seo.update');
    Route::post('/public-content/home-page', [PublicContentController::class, 'updateHomePage'])
        ->name('public-content.home.update');
    Route::post('/public-content/about-page', [PublicContentController::class, 'updateAboutPage'])
        ->name('public-content.about.update');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('admin-reviews', ReviewController::class)
        ->only(['index', 'show', 'update'])
        ->parameters(['admin-reviews' => 'adminReview']);
    Route::resource('contact-inquiries', ContactInquiryController::class)
        ->only(['index', 'show', 'update']);
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/business-settings', [ProfileController::class, 'updateBusinessSettings'])
        ->name('profile.business.update');
    Route::post('/profile/cloudinary-settings', [ProfileController::class, 'updateCloudinarySettings'])
        ->name('profile.cloudinary.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', fn () => response()->view('robots')->header('Content-Type', 'text/plain'));

require __DIR__ . '/auth.php';

