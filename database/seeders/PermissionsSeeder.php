<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Liste complète des permissions du système.
     */
    protected array $permissions = [
        // Gestion des utilisateurs
        'users' => [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'export users',
        ],
        
        // Gestion des rôles
        'roles' => [
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
        ],
        
        // Gestion des permissions
        'permissions' => [
            'view permissions',
            'assign permissions',
        ],
        
        // Gestion des profils
        'profiles' => [
            'view all profiles',
            'edit all profiles',
            'delete profiles',
        ],
        
        // Paramètres système
        'settings' => [
            'view settings',
            'edit settings',
        ],
        
        // Rapports
        'reports' => [
            'view reports',
            'export reports',
        ],
    ];

    public function run(): void
    {
        // Réinitialiser le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer toutes les permissions
        foreach ($this->permissions as $category => $perms) {
            foreach ($perms as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission],
                    ['guard_name' => 'web']
                );
            }
        }

        // Créer ou mettre à jour les rôles
        $this->createRoles();

        $this->command->info('✅ Permissions créées avec succès !');
    }

    /**
     * Crée les rôles et assigne les permissions.
     */
    protected function createRoles(): void
    {
        // 1. Rôle Ghost (Super Admin)
        $ghostRole = Role::firstOrCreate(['name' => 'Ghost']);
        $ghostRole->syncPermissions(Permission::all());

        // 2. Rôle Admin
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'view users', 'create users', 'edit users', 'export users',
            'view roles', 'edit roles',
            'view all profiles', 'edit all profiles',
            'view settings',
            'view reports', 'export reports',
        ]);

        // 3. Rôle Moderator
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $moderatorRole->syncPermissions([
            'view users', 'edit users',
            'view all profiles',
            'view reports',
        ]);

        // 4. Rôle User (par défaut)
        $userRole = Role::firstOrCreate(['name' => 'user']);
        // Les utilisateurs normaux n'ont que les permissions de base

        $this->command->info('✅ Rôles configurés avec succès !');
    }
}