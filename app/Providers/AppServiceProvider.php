<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Wishlist;
use App\Services\CacheService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Toàn bộ $model->paginate()->links() trong app dùng markup
        // Bootstrap 4 (thuần <ul><li><a>) thay vì Tailwind mặc định,
        // vì CSS admin/khách hàng của project là CSS viết tay,
        // không load Tailwind nên view mặc định bị vỡ giao diện.
        Paginator::useBootstrapFour();

        // Dữ liệu điều hướng dùng chung cho storefront. Gom tại đây để layout
        // không tự query lặp lại và badge luôn phản ánh dữ liệu thật của user.
        View::composer('layouts.app', function ($view) {
            $userId = auth()->id();

            $view->with([
                'layoutCategories' => CacheService::categories(),
                'layoutBrands' => CacheService::brands(),
                'layoutCartCount' => $userId
                    ? CartItem::where('user_id', $userId)->sum('quantity')
                    : 0,
                'layoutWishlistCount' => $userId
                    ? Wishlist::where('user_id', $userId)->count()
                    : 0,
            ]);
        });
    }
}
