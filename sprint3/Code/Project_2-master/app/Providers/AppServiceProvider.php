<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\DB;

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
        Paginator::useBootstrap();
        
        date_default_timezone_set('Asia/Bangkok'); // ตั้งค่า PHP Time Zone
        DB::statement("SET time_zone = '+07:00'"); // ตั้งค่า MySQL Time 
        
        view()->composer(
            'layouts.layout', 
            function ($view) {
                $view->with('dn', \App\Models\Program::where('degree_id', '=', 1)->get());
            }
        );
    }
}
