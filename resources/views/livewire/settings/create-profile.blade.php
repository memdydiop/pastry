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
     * Sauvegarde le profil de l'utilisateur.
     * La logique a été affinée pour une meilleure gestion des erreurs et de la validation.
     */
    public function saveProfile()
    {
        // 1. On valide les données en premier. Si cela échoue,
        //    Livewire arrêtera l'exécution ici et affichera les erreurs.
        $validatedData = $this->form->validate();

        // On prépare les données après validation pour plus de sécurité
        $data = $this->form->prepareForSave();
        $avatarPath = null;

        try {
            DB::beginTransaction();

            // 2. Le code ici ne s'exécute que si la validation a réussi.
            if ($this->form->avatar) {
                $avatarPath = $this->form->avatar->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            auth()->user()->profile()->create($data);
            auth()->user()->update(['profile_completed' => true]);

            DB::commit();

            session()->flash('success', 'Votre profil a été créé avec succès !');
            return $this->redirect(route('dashboard'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();

            // En cas d'erreur, on supprime l'avatar qui a pu être uploadé.
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }

            Log::error('Erreur lors de la création du profil', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Une erreur inattendue est survenue. Veuillez réessayer.');
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Créer votre profil')" :description="__('Complétez vos informations pour finaliser votre inscription')" />

    {{-- Affichage des messages de succès ou d'erreur --}}
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

    <form wire:submit="saveProfile" class="flex flex-col gap-6">

        {{-- Section Avatar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Photo de profil (Optionnel)') }}</label>
            <div class="flex items-center gap-4">
                @if ($form->avatar)
                    <img src="{{ $form->avatar->temporaryUrl() }}" alt="{{ __('Aperçu') }}"
                        class="size-20 mask mask-squircle object-cover  shadow-sm">
                @else
                    <div class="size-20 mask mask-squircle bg-gray-100 flex items-center justify-center">
                        <flux:icon.user class="size-10 text-gray-400" />
                    </div>
                @endif
                <div class="flex-grow">
                    <input type="file" wire:model.blur="form.avatar" id="avatar" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, WEBP. 2MB maximum.</p>
                    <div wire:loading wire:target="form.avatar" class="mt-2 text-sm text-blue-500">
                        {{ __('Téléchargement en cours...') }}
                    </div>
                    
                </div>
            </div>
        </div>

        {{-- Nom complet --}}
        <div>
            <flux:input wire:model.blur="form.full_name" :label="__('Nom et Prénoms')" type="text" required />
            
        </div>

        {{-- Ligne : Date de naissance & Téléphone --}}
        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <flux:input wire:model.blur="form.date_of_birth" :label="__('Date de naissance')" type="date"
                    :max="date('Y-m-d')" />
            </div>
            <div>
                <flux:input wire:model.blur="form.phone" :label="__('Téléphone')" type="tel"
                    placeholder="+225 xx xx xx xx xx" required />
            </div>
        </div>

        {{-- Adresse --}}
        <div>
            <flux:input wire:model.blur="form.address" :label="__('Adresse')" type="text" required />
        </div>

        {{-- Ligne : Ville & Pays --}}
        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <flux:input wire:model.blur="form.city" :label="__('Ville')" type="text" required />
            </div>
            <div>
                <flux:input wire:model.blur="form.country" :label="__('Pays')" type="text" />
            </div>
        </div>

        {{-- Biographie --}}
        <div>
            <flux:textarea wire:model.blur="form.bio" :label="__('Biographie (Optionnel)')" rows="4"
                placeholder="Parlez-nous de vous..." />
        </div>

        {{-- Bouton de soumission --}}
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="w-full">
            <span wire:loading.remove wire:target="saveProfile">Créer mon profil</span>
            <span wire:loading wire:target="saveProfile">Création en cours...</span>
        </flux:button>

    </form>
</div>