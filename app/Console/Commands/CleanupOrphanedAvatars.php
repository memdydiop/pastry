<?php

namespace App\Console\Commands;

use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedAvatars extends Command
{
    /**
     * Signature de la commande.
     */
    protected $signature = 'profile:cleanup-avatars
                            {--dry-run : Simuler sans supprimer}
                            {--force : Forcer sans confirmation}';

    /**
     * Description de la commande.
     */
    protected $description = 'Nettoie les avatars orphelins du stockage';

    /**
     * Exécute la commande.
     */
    public function handle(): int
    {
        $this->info('🔍 Recherche des avatars orphelins...');

        // Récupérer tous les avatars en base
        $usedAvatars = UserProfile::whereNotNull('avatar')
            ->pluck('avatar')
            ->map(fn($path) => basename($path))
            ->toArray();

        $this->info('✓ ' . count($usedAvatars) . ' avatars actifs en base de données');

        // Récupérer tous les fichiers du dossier avatars
        $allFiles = Storage::disk('public')->files('avatars');
        $this->info('✓ ' . count($allFiles) . ' fichiers trouvés dans le stockage');

        // Identifier les orphelins
        $orphanedFiles = collect($allFiles)->filter(function ($file) use ($usedAvatars) {
            return !in_array(basename($file), $usedAvatars);
        });

        if ($orphanedFiles->isEmpty()) {
            $this->info('✅ Aucun avatar orphelin trouvé !');
            return self::SUCCESS;
        }

        $this->warn('⚠️  ' . $orphanedFiles->count() . ' avatar(s) orphelin(s) trouvé(s)');

        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->table(
                ['Fichier', 'Taille'],
                $orphanedFiles->map(fn($file) => [
                    $file,
                    $this->formatBytes(Storage::disk('public')->size($file))
                ])
            );
            
            $this->info('🔍 Mode simulation - Aucun fichier supprimé');
            return self::SUCCESS;
        }

        // Demander confirmation
        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous supprimer ces fichiers orphelins ?')) {
                $this->info('❌ Opération annulée');
                return self::FAILURE;
            }
        }

        // Supprimer les fichiers orphelins
        $deleted = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($orphanedFiles->count());
        $progressBar->start();

        foreach ($orphanedFiles as $file) {
            try {
                if (Storage::disk('public')->delete($file)) {
                    $deleted++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $this->error("Erreur lors de la suppression de {$file}: " . $e->getMessage());
                $errors++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Résultats
        if ($deleted > 0) {
            $this->info("✅ {$deleted} fichier(s) supprimé(s) avec succès");
        }

        if ($errors > 0) {
            $this->error("❌ {$errors} erreur(s) rencontrée(s)");
        }

        // Calcul de l'espace libéré
        $this->info('💾 Nettoyage terminé !');

        return self::SUCCESS;
    }

    /**
     * Formate les octets en format lisible.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}