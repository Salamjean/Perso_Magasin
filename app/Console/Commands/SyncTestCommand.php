<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Illuminate\Console\Command;

class SyncTestCommand extends Command
{
    protected $signature = 'sync:test';

    protected $description = 'Vérifie l\'état de la connectivité réseau et base de données avec le serveur MySQL distant (mysql_remote)';

    public function handle(SyncService $syncService): int
    {
        $this->info('🔍 Test de la connexion avec mysql_remote...');

        $result = $syncService->testRemoteConnection();

        if ($result['connected']) {
            $this->info('✅ Connecté avec succès !');
            $this->line("   - Hôte : {$result['host']}");
            $this->line("   - Base : {$result['database']}");
            $this->line("   - Latence : {$result['latency_ms']} ms");

            return Command::SUCCESS;
        }

        $this->error('❌ Échec de la connexion distante :');
        $this->line("   - Hôte : {$result['host']}");
        $this->line("   - Base : {$result['database']}");
        $this->line("   - Erreur : {$result['message']}");

        return Command::FAILURE;
    }
}
