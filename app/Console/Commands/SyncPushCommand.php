<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Exception;
use Illuminate\Console\Command;

class SyncPushCommand extends Command
{
    protected $signature = 'sync:push';

    protected $description = 'Envoie les opérations locales (ventes, caisses, livraisons, stocks) vers le serveur distant mysql_remote';

    public function handle(SyncService $syncService): int
    {
        $this->info('🚀 Démarrage du PUSH vers mysql_remote...');

        try {
            $result = $syncService->push();
            $this->info('✅ '.$result['message']);
            $this->table(
                ['Entité', 'Enregistrements téléversés'],
                collect($result['summary'])->map(fn ($val, $key) => [ucfirst($key), $val])->toArray()
            );
            $this->info("⏱️ Durée : {$result['duration_seconds']}s");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Échec du PUSH : '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
