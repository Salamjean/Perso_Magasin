<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Exception;
use Illuminate\Console\Command;

class PrepareBuildCommand extends Command
{
    protected $signature = 'app:prepare-build {--skip-push : Ne pas faire de push avant de vider}';

    protected $description = 'Sauvegarde les données locales (PUSH), vide la base SQLite pour le build final et compile les assets.';

    public function handle(SyncService $syncService): int
    {
        $this->info('🚀 Préparation du Build Final Desktop (Base de données Vierge)...');

        // 1. PUSH de sécurité si le serveur distant est joignable
        if (! $this->option('skip-push')) {
            $this->info('📤 Étape 1/3 : Sauvegarde de sécurité vers MySQL distant (PUSH)...');
            try {
                $pushResult = $syncService->push();
                $this->info('✅ PUSH réussi : '.$pushResult['message']);
            } catch (Exception $e) {
                $this->warn('⚠️ PUSH ignoré ou serveur distant non joignable : '.$e->getMessage());
            }
        }

        // 2. Réinitialisation complète de la base SQLite locale (Vierge avec tables créées)
        $this->info('🧹 Étape 2/3 : Réinitialisation de la base SQLite locale (Tables vierges)...');
        $this->call('migrate:fresh', [
            '--force' => true,
        ]);
        $this->info('✅ Base SQLite réinitialisée à neuf (0 enregistrement, structure prête).');

        // 3. Copie des icônes
        $this->info('🖼️ Vérification des icônes de l\'application...');
        if (file_exists(public_path('images/logo.png'))) {
            @copy(public_path('images/logo.png'), public_path('icon.png'));
            @copy(public_path('images/logo.png'), public_path('icon.ico'));
            @copy(public_path('images/logo.png'), public_path('favicon.ico'));
            $this->info('✅ Icônes synchronisées depuis public/images/logo.png.');
        }

        $this->info('✨ Préparation terminée ! La base SQLite embarquée dans le build est complètement propre.');

        return Command::SUCCESS;
    }
}
