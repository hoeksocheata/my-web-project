<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // ប្រើ Pagination បែប Bootstrap 5 ឱ្យស៊ីនឹងរចនាបថរបស់គេហទំព័រ
        Paginator::useBootstrapFive();

        // ក្រោយ proxy របស់ Render (HTTPS ត្រូវបានបញ្ចប់នៅ proxy) ត្រូវបង្ខំ URL ជា https
        // បើមិនដូច្នេះទេ CSS/JS នឹងត្រូវជា http ហើយ browser រារាំង (mixed content)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // ពេលវេលាទាក់ទង (diffForHumans) ជាភាសាខ្មែរ
        \Carbon\Carbon::setLocale('km');
    }
}