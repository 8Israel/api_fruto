<?php

namespace App\Providers;

use App\Services\SupabaseService;
use Illuminate\Support\ServiceProvider;
use Supabase\Storage\StorageClient;

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
        //
    }
}
