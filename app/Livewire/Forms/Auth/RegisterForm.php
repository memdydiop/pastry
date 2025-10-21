<?php

namespace App\Livewire\Forms\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Livewire\Form;

class RegisterForm extends Form
{
    

    /**
     * The user's email address.
     *
     * @var string
     */
    public $email = '';

    /**
     * The user's password.
     *
     * @var string
     */
    public $password = '';

    /**
     * The user's password confirmation.
     *
     * @var string
     */
    public $password_confirmation = '';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Create a new user instance and authenticate them.
     */
    public function store(): User
    {
        $this->validate();

        $user = User::create([
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        event(new Registered($user));
        
        return $user;
    }
}