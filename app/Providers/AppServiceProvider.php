<?php

namespace App\Providers;

use App\Support\MediaStorage;
use Cloudinary\Cloudinary;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Cloudinary::class, function () {
            $cloudName = config('services.cloudinary.cloud_name');
            $apiKey    = config('services.cloudinary.api_key');
            $apiSecret = config('services.cloudinary.api_secret');

            return new Cloudinary(
                "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}"
            );
        });

        $this->app->singleton(MediaStorage::class, function ($app) {
            return new MediaStorage($app->make(Cloudinary::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
