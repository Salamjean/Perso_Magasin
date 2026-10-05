<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Exception;
use Illuminate\Console\Command;

class SyncPullCommand extends Command
{
    protected $signature = 'sync:pull';

    protected $description = 'Télécharge les données de référence et catalogue depuis le serveur distant mysql_remote vers la base locale';

    public function handle(SyncService $syncService): int
    {
        $this->info('🚀 Démarrage du PULL depuis mysql_remote...');

        try {
            $result = $syncService->pull();
            $this->info('✅ '.$result['message']);
            $this->table(
                ['Entité', 'Enregistrements synchronisés'],
                collect($result['summary'])->map(fn ($val, $key) => [ucfirst($key), $val])->toArray()
            );
            $this->info("⏱️ Durée : {$result['duration_seconds']}s");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Échec du PULL : '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
