<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Observers\SupplierObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use App\Observers\ProductMasterListObserver;

use App\Models\Supplier;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductMasterList;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Supplier::observe(SupplierObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        ProductMasterList::observe(ProductMasterListObserver::class);
    }
}
