<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Force HTTPS on Vercel (proxy terminates SSL before PHP)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Force-configure Cloudinary from OS env (bypasses config cache)
        // Config cache may have been created before CLOUDINARY_URL was set
        $cloudinaryUrl = getenv('CLOUDINARY_URL')
            ?: ($_SERVER['CLOUDINARY_URL'] ?? null)
            ?: ($_ENV['CLOUDINARY_URL'] ?? null);
        if ($cloudinaryUrl) {
            config(['cloudinary.cloud_url' => $cloudinaryUrl]);
        }
    }
}
