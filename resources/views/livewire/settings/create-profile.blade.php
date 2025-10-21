<?php

use App\Livewire\Forms\ProfileForm;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.auth')]
    class extends Component {

    use WithFileUploads;

    public ProfileForm $form;

    /**
     * Sauvegarde le profil utilisateur avec gestion des transactions
     */
    public function saveProfile()
    {
        // Validation des données
        $this->form->validate();

        $data = $this->form->prepareForSave();

        try {
            DB::beginTransaction();

            // Gestion de l'upload de l'avatar
            if ($this->form->avatar) {
                $avatarPath = $this->form->avatar->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            // Création du profil de l'utilisateur
            auth()->user()->profile()->create($data);

            // Met à jour le statut de l'utilisateur
            auth()->user()->update(['profile_completed' => true]);

            DB::commit();

            // Message de succès
            session()->flash('success', 'Votre profil a été créé avec succès !');

            // Redirection vers le tableau de bord
            return $this->redirect(route('dashboard'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();

            // Supprimer l'avatar uploadé en cas d'erreur
            if (isset($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            Log::error('Erreur lors de la création du profil', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la création de votre profil. Veuillez réessayer.');

            return null;
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Créer votre profil')" :description="__('Complétez vos informations pour finaliser votre inscription')" />

    <!-- Messages de session -->
    @if (session('success'))
        <div class="rounded-md bg-green-50 p-4">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" wire:submit="saveProfile" class="bg-body rounded-md p-6 border flex flex-col gap-6">

        <!-- Nom complet -->
        <div>
            <flux:input wire:model.blur="form.full_name" :label="__('Nom et Prénoms')" name="full_name" type="text"
                required autofocus placeholder="Nom Complet" />
            @error('form.full_name')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div><!-- Avatar -->
        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Photo de profil') }}
            </label>
        
            {{-- Bloc d'aperçu de l'image --}}
            <div class="mb-2 flex flex-col items-start space-x-4">
                @if ($form->avatar)
                    <div class="flex items-center justify-start space-x-4">
                        <img src="{{ $form->avatar->temporaryUrl() }}" alt="{{ __('Aperçu de l\'avatar') }}"
                            class="w-20 h-20 mask mask-squircle object-cover">
                        <flux:button wire:click="$set('form.avatar', null)" type="button" variant="danger" size="sm">
                            {{ __('Retirer') }}
                        </flux:button>
                    </div>
                @else
                    <div class="relative flex items-center justify-start gap-x-2">
                        <div class="size-20 p-10 mask mask-squircle flex items-center justify-center">
                            <flux:icon name="camera" class="w-10 h-10  text-white" />
                        </div>
                        <flux:button type="button" 
                        class="bg-transparent! size-20! cursor-pointer border-none! absolute! inset!"
                            onclick="document.getElementById('avatar').click()" aria-label="Changer l'avatar" />
                        
                            <flux:text sm color="muted" >
                                {{ __("Clicker sur l'icon de la camera a gauche pour choisire une photo de profil") }}
                            </flux:text>
                        
                            <input type="file" wire:model="form.avatar" name="avatar" id="avatar" class="hidden"
                            accept="image/jpeg,image/jpg,image/png,image/webp" class="block w-full text-sm text-gray-500
                                           file:mr-2 file:p-2 file:px-4
                                           file:rounded file:border-2 file:border-info
                                           file:text-sm file:font-semibold
                                           file:bg-info/10 file:text-info
                                           hover:file:bg-info/30" />
                    </div>
                @endif
                <p class="mt-1 w-full text-center text-xs text-muted">
                    JPG, PNG, WEBP. 2MB maximum. Minimum 100x100 pixels.
                </p>
            </div>
        
        
        
            {{-- Indicateur de chargement Livewire --}}
            <div wire:loading wire:target="form.avatar" class="mt-2 text-sm text-blue-500">
                {{ __('Téléchargement de l\'avatar en cours...') }}
            </div>
        
            @error('form.avatar')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-x-6">
            <!-- Date de naissance -->
            <div class="flex-1">
                <flux:input wire:model.blur="form.date_of_birth" :label="__('Date de naissance')" name="date_of_birth"
                    type="date" :max="date('Y-m-d')" placeholder="Date de naissance" required />
                @error('form.date_of_birth')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <!-- Téléphone -->
            <div class="flex-1">
                <flux:input wire:model.blur="form.phone" :label="__('Téléphone')" name="phone" type="tel" required
                    placeholder="XX XX XX XX XX" />
                @error('form.phone')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Adresse -->
        <div>
            <flux:input wire:model.blur="form.address" :label="__('Adresse')" name="address" type="text" required
                placeholder="Adresse complète" />
            @error('form.address')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-x-6">
            <!-- Ville -->
            <div class="flex-1">
                <flux:input wire:model.blur="form.city" :label="__('Ville')" name="city" type="text" required
                    placeholder="Ville" />
                @error('form.city')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <!-- Pays -->
            <div class="flex-1">
                <flux:input wire:model.blur="form.country" :label="__('Pays')" name="country" type="text" required
                    placeholder="Pays" />
                @error('form.country')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Biographie -->
        <div>
            <flux:textarea wire:model.blur="form.bio" :label="__('Biographie')" name="bio" rows="4" required
                placeholder="Parlez-nous de vous..." />
            @error('form.bio')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Bouton de soumission -->
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="w-full">
            <span wire:loading.remove wire:target="saveProfile">Créer mon profil</span>
            <span wire:loading wire:target="saveProfile">Création en cours...</span>
        </flux:button>

    </form>

</div>