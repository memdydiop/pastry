<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Retourne la liste de toutes les permissions de l'application.
     * C'est la "Source de Vérité". Pour ajouter une permission,
     * il suffit de l'ajouter à ce tableau.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return [
            // Gestion des Utilisateurs
            'view users',
            'create users',
            'edit users',
            'delete users',
            'export users',

            // Gestion des Rôles & Permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            'view administration',

            'view permissions',
            'assign permissions',

            'view all profiles',
            'edit all profiles',
            'delete profiles',

            'view settings',
            'edit settings',

            'view reports',
            'export reports',

            // --- AJOUTEZ VOS NOUVELLES PERMISSIONS CI-DESSOUS ---
            
            // Exemple : Gestion des Rapports
            // 'view reports',
            // 'export reports',
        ];
    }

    /**
     * Exécute les seeds de la base de données.
     */
    public function run(): void
    {
        // Réinitialise le cache des rôles et permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 1. Création des Permissions ---
        $permissions = $this->getPermissions();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- 2. Création des Rôles ---
        $ghostRole = Role::firstOrCreate(['name' => 'Ghost']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // --- 3. Assignation des Permissions aux Rôles ---
        // Le "Ghost" obtient toutes les permissions
        //$ghostRole->givePermissionTo(Permission::all());

        // Le "Admin" obtient toutes les permissions
        $adminRole->syncPermissions([
            'view users', 'create users', 'edit users', 'export users',
            'view roles', 'edit roles',
            'view all profiles', 'edit all profiles',
            'view administration',
            'view settings',
            'view reports', 'export reports',
        ]);

        // Le "User" obtient un ensemble de permissions de base
        $userRole->syncPermissions([
            'view users', 'edit users',
            'view all profiles',
            'view reports',
            // Ajoutez ici d'autres permissions par défaut pour un utilisateur standard
        ]);
    }
}