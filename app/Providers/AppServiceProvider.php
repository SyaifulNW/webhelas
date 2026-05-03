<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use App\Models\Kelas; // Ensure you import the Kelas model


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function() {
            if (is_dir(base_path('public_html'))) {
                return base_path('public_html');
            }
            return base_path('public');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         View::composer('*', function ($view) {
        $view->with('kelas', \App\Models\Kelas::all());
    });
     if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
    }
}
