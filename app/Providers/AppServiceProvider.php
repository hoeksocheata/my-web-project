<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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

        // ពេលវេលាទាក់ទង (diffForHumans) ជាភាសាខ្មែរ
        \Carbon\Carbon::setLocale('km');
    }
}
