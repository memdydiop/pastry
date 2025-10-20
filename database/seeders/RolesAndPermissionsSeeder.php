<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // N'oubliez pas d'importer le modèle User

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {// Réinitialiser les caches de rôles et permissions (important)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Créer des Permissions pour la gestion des utilisateurs
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            // Ajoutez d'autres permissions pour d'autres fonctionnalités si nécessaire
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Créer les Rôles
        $ghostRole = Role::firstOrCreate(['name' => 'Ghost']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assigner toutes les permissions au rôle 'Ghost'
        //$ghostRole->givePermissionTo(Permission::all());

        // Le rôle 'user' n'a aucune permission spécifique pour le moment
    }
}