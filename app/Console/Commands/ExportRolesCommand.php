<?php

namespace App\Console\Commands;

use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportRolesCommand extends Command
{
    protected $signature = 'roles:export 
                            {--format=json : Format d\'export (json|csv)}
                            {--output= : Chemin du fichier de sortie}';

    protected $description = 'Exporte les rôles et leurs permissions';

    public function handle(): int
    {
        $format = $this->option('format');
        $output = $this->option('output') ?? "exports/roles-" . now()->format('Y-m-d-His') . ".{$format}";

        $this->info("📤 Export des rôles au format {$format}...");

        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'name' => $role->name,
                'users_count' => $role->users()->count(),
                'permissions' => $role->permissions->pluck('name')->toArray(),
                'created_at' => $role->created_at->toDateTimeString(),
            ];
        });

        $content = match($format) {
            'json' => $this->exportJson($roles),
            'csv' => $this->exportCsv($roles),
            default => throw new \InvalidArgumentException("Format non supporté: {$format}"),
        };

        Storage::put($output, $content);

        $this->info("✅ Export terminé: storage/app/{$output}");
        $this->info("📊 {$roles->count()} rôle(s) exporté(s)");

        return self::SUCCESS;
    }

    protected function exportJson($roles): string
    {
        return json_encode([
            'exported_at' => now()->toIso8601String(),
            'total' => $roles->count(),
            'roles' => $roles->toArray(),
        ], JSON_PRETTY_PRINT);
    }

    protected function exportCsv($roles): string
    {
        $csv = "Name,Users Count,Permissions Count,Permissions,Created At\n";
        
        foreach ($roles as $role) {
            $csv .= sprintf(
                '"%s",%d,%d,"%s","%s"' . "\n",
                $role['name'],
                $role['users_count'],
                count($role['permissions']),
                implode(', ', $role['permissions']),
                $role['created_at']
            );
        }

        return $csv;
    }
}