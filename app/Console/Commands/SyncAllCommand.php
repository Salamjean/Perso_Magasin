<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Exception;
use Illuminate\Console\Command;

class SyncAllCommand extends Command
{
    protected $signature = 'sync:all';

    protected $description = 'Exécute une synchronisation complète bidirectionnelle (PUSH local -> distant puis PULL distant -> local)';

    public function handle(SyncService $syncService): int
    {
        $this->info('🔄 Lancement de la synchronisation complète avec mysql_remote...');

        try {
            $result = $syncService->syncAll();
            $this->info('✅ '.$result['message']);

            $this->info('📤 PUSH (Opérations locales vers distant) :');
            $this->table(
                ['Entité', 'Total'],
                collect($result['push'])->map(fn ($val, $key) => [ucfirst($key), $val])->toArray()
            );

            $this->info('📥 PULL (Catalogue distant vers local) :');
            $this->table(
                ['Entité', 'Total'],
                collect($result['pull'])->map(fn ($val, $key) => [ucfirst($key), $val])->toArray()
            );

            $this->info("⏱️ Durée totale : {$result['duration_seconds']}s");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Erreur lors de la synchronisation : '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
