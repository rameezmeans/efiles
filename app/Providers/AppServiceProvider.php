<?php

namespace App\Providers;

use ECUApp\SharedCode\Models\WorkHours;
use Illuminate\Support\ServiceProvider;

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
        $workHours = WorkHours::all();
        view()->share('workHours', $workHours);
    }
}
