<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        if (config('nativephp-internal.running') && config('app.debug')) {
            config(['database.connections.nativephp.database' => database_path('database.sqlite')]);
        }

        Model::saving(function ($model) {
            if (! str_contains(request()->path() ?? '', 'sync') && ! app()->runningInConsole()) {
                if (array_key_exists('synced', $model->getAttributes()) && ! $model->isDirty('synced')) {
                    $model->synced = false;
                }
            }
        });
    }
}
