<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Forms\ProfileForm;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public ProfileForm $form;

    /**
     * Mount the component and populate the form
     * with the authenticated user's profile data.
     */
    public function mount(): void
    {
        $profile = auth()->user()->profile;

        if ($profile) {
            $this->form->fill([
                'full_name' => $profile->full_name,
                'date_of_birth' => $profile->date_of_birth?->format('Y-m-d'),
                'phone' => $profile->phone,
                'address' => $profile->address,
                'city' => $profile->city,
                'country' => $profile->country,
                'bio' => $profile->bio,
            ]);
        }
    }

    /**
     * Update the profile information with transaction support.
     */
    public function updateProfile(): void
    {
        // Validation des données
        $data = $this->form->validate();

        try {
            DB::beginTransaction();

            $profile = auth()->user()->profile;
            $oldAvatarPath = $profile->avatar_path;

            // Gestion de l'upload du nouvel avatar
            if ($this->form->avatar) {
                $newAvatarPath = $this->form->avatar->store('avatars', 'public');
                $data['avatar'] = $newAvatarPath;
            }

            // Mise à jour du profil
            $profile->update($data);

            DB::commit();

            // Note: L'observer se charge de supprimer l'ancien avatar après commit

            // Message de succès et event pour le toast
            session()->flash('success', 'Profil mis à jour avec succès !');
            $this->dispatch('profile-updated');

            // Rafraîchir le composant pour afficher le nouvel avatar
            $this->mount();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Supprimer le nouvel avatar si uploadé
            if (isset($newAvatarPath)) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            // Relancer l'exception pour afficher les erreurs de validation
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            // Supprimer le nouvel avatar en cas d'erreur
            if (isset($newAvatarPath)) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            Log::error('Erreur lors de la mise à jour du profil', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la mise à jour de votre profil.');
        }
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<x-layouts.content :heading="__('Paramètres')" :subheading="__('Gérez votre profil')" :pageHeading="__('Profil')"
    :pageSubheading="__('Mettez à jour les informations de votre profil et votre avatar.')">

    



    <form wire:submit="updateProfile" class="my-6 w-full space-y-6">

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-6">

            <!-- Section Avatar -->
            <div class="sm:col-span-2 flex items-center gap-x-2">
                <div class="size-24 relative">
                    @if ($form->avatar)
                        <!-- Aperçu du nouvel avatar -->
                        <img src="{{ $form->avatar->temporaryUrl() }}" alt="Aperçu de l'avatar"
                            class="h-24 w-24 flex-none mask mask-squircle bg-gray-800 object-cover">
                    @else
                        <!-- Avatar actuel -->
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar actuel"
                            class="h-24 w-24 flex-none mask mask-squircle bg-gray-800 object-cover">
                    @endif
                        <flux:button type="button" label="Changer l'avatar" class="bg-transparent! size-24! cursor-pointer border-none! absolute! top-0! left-0!"
                            onclick="document.getElementById('avatarInput').click()" />
                </div>

                <flux:text sm color="muted">JPG, GIF ou PNG. 2MB maximum.</flux:text>

                <input type="file" wire:model="form.avatar" id="avatarInput"
                    accept="image/jpeg,image/jpg,image/png,image/gif" class="hidden" />

                @error('form.avatar')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Champs du formulaire -->
            <div class="col-span-4">
                <flux:textarea wire:model="form.bio" label="Biographie" rows="4" />
            </div>


            <div class="sm:col-span-2">
                <flux:input wire:model="form.full_name" :label="__('Nom complet')" type="text" required autofocus />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.date_of_birth" type="date" label="Date de naissance"
                    :max="date('Y-m-d')" />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.phone" label="Téléphone" type="tel" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.address" label="Adresse" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.city" label="Ville" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.country" label="Pays" />
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit" wire:loading.attr="disabled" data-test="update-profile-button">
                <span wire:loading.remove>{{ __('Enregistrer') }}</span>
                <span wire:loading>Enregistrement...</span>
            </flux:button>
        </div>
    </form>

    <!-- Formulaire de suppression de compte -->
    <livewire:settings.delete-user-form />

</x-layouts.content>