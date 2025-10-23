<?php

namespace App\Observers;

use App\Models\RoleAudit;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    /**
     * Gère l'événement "created" (création) du rôle.
     */
    public function created(Role $role): void
    {
        RoleAudit::log('created', $role, null, ['permissions' => $role->permissions->pluck('name')->toArray()]);
    }

    /**
     * Gère l'événement "updated" (mise à jour) du rôle.
     */
    public function updated(Role $role): void
    {
        $oldData = $role->getOriginal();
        $newData = $role->getAttributes();
        
        // Nous allons nous concentrer sur le changement de nom pour l'instant
        // La gestion des permissions se fera via un événement "pivot"
        if (isset($oldData['name']) && $oldData['name'] !== $newData['name']) {
            RoleAudit::log('updated', $role, ['name' => $oldData['name']], ['name' => $newData['name']]);
        }
    }

    /**
     * Gère l'événement "deleted" (suppression) du rôle.
     */
    public function deleted(Role $role): void
    {
        RoleAudit::log('deleted', $role, ['permissions' => $role->permissions->pluck('name')->toArray()]);
    }

    /**
     * Gère les changements de permissions (événement "pivot" sur la relation).
     */
    public function permissionsAttached(Role $role, array $permissionIds): void
    {
        RoleAudit::log('permissions_changed', $role);
    }
}