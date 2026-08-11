<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SolutionsController;
use App\Support\Routing\LocalizedRoutes;
use Illuminate\Support\Facades\Route;

// Public site: ru is unprefixed (default locale), kk/en/zh live under
// /{locale} — see LocalizedRoutes for why this is two registrations per
// route rather than an optional {locale?} segment.
LocalizedRoutes::register(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/about-us', [AboutUsController::class, 'index'])->name('about-us.index');
    Route::get('/about-us/{slug}', [AboutUsController::class, 'show'])
        ->whereIn('slug', AboutUsController::VALID_SLUGS)
        ->name('about-us.show');

    Route::get('/applications', [ApplicationsController::class, 'index'])->name('applications.index');
    Route::get('/applications/water-wastewater', [ApplicationsController::class, 'waterWastewater'])
        ->name('applications.water-wastewater');

    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');

    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

    Route::get('/news', [NewsController::class, 'index'])->name('news.index');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // Literal category segments MUST be registered before {slug} below —
    // Laravel matches routes in registration order, unlike Next's file
    // router, which prefers static segments over dynamic ones automatically.
    Route::get('/products/pumps', [ProductController::class, 'pumps'])->name('products.pumps');
    Route::get('/products/valves', [ProductController::class, 'valves'])->name('products.valves');
    Route::get('/products/control', [ProductController::class, 'control'])->name('products.control');
    Route::get('/products/water', [ProductController::class, 'water'])->name('products.water');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    // Singular segment ("project") for the show route — matches the live,
    // indexed site exactly; "/projects" (plural) is the index above.
    Route::get('/project/{slug}', [ProjectController::class, 'show'])->name('projects.show');

    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');

    Route::get('/solutions', [SolutionsController::class, 'index'])->name('solutions.index');
});

// next-intl's localePrefix: "as-needed" 307-redirects an explicit /ru/...
// request (ru being the default locale) to the unprefixed URL instead of
// 404ing — /ru/products isn't a registered pattern (ru is deliberately
// excluded from LocalizedRoutes::PREFIXED_LOCALES), but it may be crawled
// or linked externally, so replicate the canonicalization redirect exactly.
$withQueryString = fn (string $path) => $path.(request()->getQueryString() ? '?'.request()->getQueryString() : '');

Route::get('/ru', fn () => redirect()->to($withQueryString('/'), 307));
Route::get('/ru/{path}', fn (string $path) => redirect()->to($withQueryString('/'.$path), 307))
    ->where('path', '.*');

// Admin routes (unlocalized, Russian-only UI) are registered starting
// Stage 7, once the auth guard/middleware they depend on exists.
