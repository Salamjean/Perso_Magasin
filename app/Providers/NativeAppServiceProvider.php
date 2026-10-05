<?php

namespace App\Providers;

use App\Services\SyncService;
use Illuminate\Support\Facades\DB;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // En mode développement, utiliser la base de données principale database.sqlite
        if (config('app.debug')) {
            config(['database.connections.nativephp.database' => database_path('database.sqlite')]);
            DB::purge('nativephp');
        }

        // Synchronisation automatique des données depuis MySQL distant au boot
        try {
            app(SyncService::class)->syncAll();
        } catch (\Throwable $e) {
            // Mode hors-ligne si serveur distant non joignable
        }

        Window::open()
            ->title('GestMagasin - Gestion Commerciale & Caisse')
            ->url(url('/splash'))
            ->width(1400)
            ->height(900)
            ->minWidth(1024)
            ->minHeight(700)
            ->showDevTools(false)
            ->rememberState();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
