<?php

namespace App\Livewire\Forms\Auth;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RegisterForm extends Form
{
    #[Validate('required|string|email|max:255|unique:'.User::class)]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    /**
     * Crée un nouvel utilisateur à partir des données du formulaire.
     */
    public function store(?Invitation $invitation = null): void
    {
        $this->validate();

        $user = User::create([
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        if ($invitation) {
            $role = Role::findById($invitation->role_id);
            if ($role) {
                $user->assignRole($role);
            }
        }

        event(new Registered($user));

        auth()->login($user);
    }
}