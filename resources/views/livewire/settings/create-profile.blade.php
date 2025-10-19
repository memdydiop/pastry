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
        $data = $this->form->validate();

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Supprimer l'avatar uploadé si la validation échoue
            if (isset($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            // Relancer l'exception pour afficher les erreurs
            throw $e;

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

    <form method="POST" wire:submit="saveProfile" class="flex flex-col gap-6">

        <!-- Nom complet -->
        <flux:input wire:model="form.full_name" :label="__('Nom et Prénoms')" name="full_name" type="text" required
            autofocus placeholder="Nom Complet" />

        <!-- Date de naissance -->
        <flux:input wire:model="form.date_of_birth" :label="__('Date de naissance')" name="date_of_birth"
            type="date" :max="date('Y-m-d')" placeholder="Date de naissance" />

        <!-- Téléphone -->
        <flux:input wire:model="form.phone" :label="__('Téléphone')" name="phone" type="tel" required
            placeholder="+225 XX XX XX XX XX" />

        <!-- Adresse -->
        <flux:input wire:model="form.address" :label="__('Adresse')" name="address" type="text" required
            placeholder="Adresse complète" />

        <!-- Ville -->
        <flux:input wire:model="form.city" :label="__('Ville')" name="city" type="text" required placeholder="Ville" />

        <!-- Pays -->
        <flux:input wire:model="form.country" :label="__('Pays')" name="country" type="text"
            placeholder="Pays" />

        <!-- Biographie -->
        <flux:textarea wire:model="form.bio" :label="__('Biographie')" name="bio" rows="4"
            placeholder="Parlez-nous de vous..." />

        <!-- Avatar -->
        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Photo de profil') }}
            </label>
        
            {{-- Bloc d'aperçu de l'image --}}
            @if ($form->avatar)
                <div class="mb-4 flex items-center space-x-4">
                    {{-- Affiche l'image en utilisant l'URL temporaire de Livewire --}}
                    <img src="{{ $form->avatar->temporaryUrl() }}" alt="{{ __('Aperçu de l\'avatar') }}"
                        class="w-20 h-20 rounded-full object-cover border border-gray-200 shadow-sm">
                    {{-- Bouton pour retirer la sélection du fichier --}}
                    <flux:button wire:click="$set('form.avatar', null)" type="button" variant="danger" size="sm">
                        {{ __('Retirer') }}
                    </flux:button>
                </div>
            @endif
        
            <input type="file" wire:model="form.avatar" name="avatar" id="avatar"
                accept="image/jpeg,image/jpg,image/png,image/webp" {{-- Mise à jour des formats acceptés --}} class="block w-full text-sm text-gray-500 
                               file:mr-4 file:py-2 file:px-4 
                               file:rounded-full file:border-0 
                               file:text-sm file:font-semibold 
                               file:bg-blue-50 file:text-blue-700 
                               hover:file:bg-blue-100" />
        
            <p class="mt-1 text-xs text-gray-500">
                JPG, PNG, WEBP. 2MB maximum.
            </p>
        
            {{-- Indicateur de chargement Livewire --}}
            <div wire:loading wire:target="form.avatar" class="mt-2 text-sm text-blue-500">
                {{ __('Téléchargement de l\'avatar en cours...') }}
            </div>
        
            @error('form.avatar')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Bouton de soumission -->
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="w-full">
            <span wire:loading.remove>Créer mon profil</span>
            <span wire:loading>Création en cours...</span>
        </flux:button>

    </form>

</div>