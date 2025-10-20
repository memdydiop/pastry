<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RoleService
{
    /**
     * Rôles protégés qui ne peuvent pas être supprimés.
     */
    protected array $protectedRoles = ['Ghost', 'admin'];

    /**
     * Récupère tous les rôles avec leurs permissions.
     */
    public function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('roles:all', 3600, function () {
            return Role::with('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Récupère toutes les permissions groupées par catégorie.
     */
    public function getGroupedPermissions(): array
    {
        return Cache::remember('permissions:grouped', 3600, function () {
            $permissions = Permission::all();
            
            $grouped = [];
            foreach ($permissions as $permission) {
                $category = $this->extractCategory($permission->name);
                $grouped[$category][] = $permission;
            }
            
            return $grouped;
        });
    }

    /**
     * Crée un nouveau rôle avec permissions.
     */
    public function createRole(string $name, array $permissions = []): Role
    {
        DB::beginTransaction();
        
        try {
            // Vérifier si le rôle existe déjà
            if (Role::where('name', $name)->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'Ce rôle existe déjà.'
                ]);
            }

            // Créer le rôle
            $role = Role::create(['name' => $name]);
            
            // Assigner les permissions
            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }

            DB::commit();
            
            $this->clearCache();
            
            Log::info('Rôle créé', [
                'role' => $name,
                'permissions_count' => count($permissions),
                'created_by' => auth()->id(),
            ]);

            return $role;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur création rôle', [
                'role' => $name,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Met à jour un rôle existant.
     */
    public function updateRole(Role $role, string $name, array $permissions): Role
    {
        // Protéger les rôles système
        if (in_array($role->name, $this->protectedRoles) && $name !== $role->name) {
            throw ValidationException::withMessages([
                'name' => 'Ce rôle est protégé et ne peut pas être renommé.'
            ]);
        }

        DB::beginTransaction();
        
        try {
            // Vérifier unicité du nouveau nom
            if ($name !== $role->name && Role::where('name', $name)->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'Ce nom de rôle est déjà utilisé.'
                ]);
            }

            // Mettre à jour le nom
            if ($name !== $role->name) {
                $role->update(['name' => $name]);
            }
            
            // Synchroniser les permissions
            $role->syncPermissions($permissions);

            DB::commit();
            
            $this->clearCache();
            
            Log::info('Rôle mis à jour', [
                'role_id' => $role->id,
                'new_name' => $name,
                'permissions_count' => count($permissions),
                'updated_by' => auth()->id(),
            ]);

            return $role->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprime un rôle.
     */
    public function deleteRole(Role $role): bool
    {
        // Protection des rôles système
        if (in_array($role->name, $this->protectedRoles)) {
            throw ValidationException::withMessages([
                'role' => 'Ce rôle est protégé et ne peut pas être supprimé.'
            ]);
        }

        // Vérifier si le rôle a des utilisateurs
        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages([
                'role' => "Ce rôle est assigné à {$role->users()->count()} utilisateur(s) et ne peut pas être supprimé."
            ]);
        }

        try {
            $roleName = $role->name;
            $role->delete();
            
            $this->clearCache();
            
            Log::info('Rôle supprimé', [
                'role' => $roleName,
                'deleted_by' => auth()->id(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur suppression rôle', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Assigne des permissions à un rôle.
     */
    public function assignPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        $this->clearCache();
        
        return $role->fresh();
    }

    /**
     * Retire des permissions d'un rôle.
     */
    public function revokePermissions(Role $role, array $permissions): Role
    {
        $role->revokePermissionTo($permissions);
        $this->clearCache();
        
        return $role->fresh();
    }

    /**
     * Vérifie si un rôle est protégé.
     */
    public function isProtected(Role $role): bool
    {
        return in_array($role->name, $this->protectedRoles);
    }

    /**
     * Extrait la catégorie d'une permission.
     */
    protected function extractCategory(string $permissionName): string
    {
        $parts = explode(' ', $permissionName);
        return ucfirst(end($parts));
    }

    /**
     * Vide le cache des rôles et permissions.
     */
    protected function clearCache(): void
    {
        Cache::forget('roles:all');
        Cache::forget('permissions:grouped');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}