<?php

use App\Models\User;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(RoleService::class);
    
    // Créer des permissions de test
    Permission::create(['name' => 'view users']);
    Permission::create(['name' => 'edit users']);
    Permission::create(['name' => 'delete users']);
});

test('peut créer un rôle avec permissions', function () {
    $role = $this->service->createRole('Manager', ['view users', 'edit users']);
    
    expect($role)->toBeInstanceOf(Role::class)
        ->and($role->name)->toBe('Manager')
        ->and($role->permissions)->toHaveCount(2)
        ->and($role->hasPermissionTo('view users'))->toBeTrue()
        ->and($role->hasPermissionTo('edit users'))->toBeTrue();
});

test('ne peut pas créer un rôle avec un nom existant', function () {
    $this->service->createRole('Manager', []);
    
    $this->service->createRole('Manager', []);
})->throws(\Illuminate\Validation\ValidationException::class);

test('peut mettre à jour un rôle', function () {
    $role = $this->service->createRole('Manager', ['view users']);
    
    $updated = $this->service->updateRole($role, 'Senior Manager', ['view users', 'edit users']);
    
    expect($updated->name)->toBe('Senior Manager')
        ->and($updated->permissions)->toHaveCount(2);
});

test('ne peut pas renommer un rôle protégé', function () {
    $ghost = Role::create(['name' => 'Ghost']);
    
    $this->service->updateRole($ghost, 'NewName', []);
})->throws(\Illuminate\Validation\ValidationException::class);

test('peut supprimer un rôle sans utilisateurs', function () {
    $role = $this->service->createRole('Temporary', []);
    
    $result = $this->service->deleteRole($role);
    
    expect($result)->toBeTrue()
        ->and(Role::where('name', 'Temporary')->exists())->toBeFalse();
});

test('ne peut pas supprimer un rôle protégé', function () {
    $ghost = Role::create(['name' => 'Ghost']);
    
    $this->service->deleteRole($ghost);
})->throws(\Illuminate\Validation\ValidationException::class);

test('ne peut pas supprimer un rôle avec utilisateurs', function () {
    $role = $this->service->createRole('Manager', []);
    $user = User::factory()->create();
    $user->assignRole($role);
    
    $this->service->deleteRole($role);
})->throws(\Illuminate\Validation\ValidationException::class);

test('peut assigner des permissions à un rôle', function () {
    $role = $this->service->createRole('Manager', []);
    
    $updated = $this->service->assignPermissions($role, ['view users', 'edit users']);
    
    expect($updated->permissions)->toHaveCount(2);
});

test('peut révoquer des permissions d\'un rôle', function () {
    $role = $this->service->createRole('Manager', ['view users', 'edit users']);
    
    $updated = $this->service->revokePermissions($role, ['edit users']);
    
    expect($updated->permissions)->toHaveCount(1)
        ->and($updated->hasPermissionTo('view users'))->toBeTrue()
        ->and($updated->hasPermissionTo('edit users'))->toBeFalse();
});

test('groupedPermissions retourne les permissions par catégorie', function () {
    Permission::create(['name' => 'view roles']);
    Permission::create(['name' => 'edit roles']);
    
    $grouped = $this->service->getGroupedPermissions();
    
    expect($grouped)->toBeArray()
        ->and($grouped)->toHaveKey('Users')
        ->and($grouped)->toHaveKey('Roles');
});

test('isProtected identifie correctement les rôles protégés', function () {
    $ghost = Role::create(['name' => 'Ghost']);
    $admin = Role::create(['name' => 'admin']);
    $custom = Role::create(['name' => 'Custom']);
    
    expect($this->service->isProtected($ghost))->toBeTrue()
        ->and($this->service->isProtected($admin))->toBeTrue()
        ->and($this->service->isProtected($custom))->toBeFalse();
});