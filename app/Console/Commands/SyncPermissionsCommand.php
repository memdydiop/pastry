<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionsSeeder; // Importer le seeder

class SyncPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize permissions from the PermissionsSeeder with the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronizing permissions...');

        // Instancier le seeder pour accéder à ses propriétés/méthodes si nécessaire
        $seeder = new PermissionsSeeder();

        // Récupérer la liste des permissions directement depuis le seeder
        // Pour cela, nous allons devoir légèrement modifier le seeder.
        $permissionsToSync = $seeder->getPermissions();

        // Créer les permissions qui n'existent pas encore
        foreach ($permissionsToSync as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
            $this->line('Ensured permission exists: ' . $permissionName);
        }

        $this->info('Permissions synchronized.');

        // S'assurer que le rôle Super Admin existe et a toutes les permissions
        $ghostRole = Role::firstOrCreate(['name' => 'Ghost']);
        //$ghostRole->givePermissionTo(Permission::all());

        $this->info('Ghost role synchronized with all permissions.');
        $this->comment('All done!');

        return 0;
    }
}