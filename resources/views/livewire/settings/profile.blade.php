<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Forms\ProfileForm;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public ProfileForm $form;
    public string $currentAvatarUrl = '';

    /**
     * Initialise le composant avec les données du profil.
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

            $this->currentAvatarUrl = $profile->avatar;
        }
    }

    /**
     * Met à jour le profil avec gestion transactionnelle.
     */
    public function updateProfile(): void
    {
        // Validation
        $validatedData = $this->form->validate();
        $preparedData = $this->form->prepareForSave();

        try {
            DB::beginTransaction();

            $profile = auth()->user()->profile;
            $oldAvatarPath = $profile->getRawOriginal('avatar');
            $newAvatarPath = null;

            // Upload du nouvel avatar si fourni
            if ($this->form->avatar) {
                $newAvatarPath = $this->form->avatar->store('avatars', 'public');
                $preparedData['avatar'] = $newAvatarPath;
            }

            // Mise à jour du profil
            $profile->update($preparedData);

            // Suppression manuelle de l'ancien avatar si remplacé
            if ($newAvatarPath && $oldAvatarPath && $oldAvatarPath !== $newAvatarPath) {
                $profile->deleteAvatarFile($oldAvatarPath);
            }

            DB::commit();

            // Vider le cache et recharger
            $profile->clearAvatarCache();
            $this->mount();

            // Notifications
            session()->flash('success', 'Profil mis à jour avec succès !');
            $this->dispatch('profile-updated');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Nettoyer le nouvel avatar en cas d'échec
            if (isset($newAvatarPath)) {
                \Storage::disk('public')->delete($newAvatarPath);
            }

            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($newAvatarPath)) {
                \Storage::disk('public')->delete($newAvatarPath);
            }

            Log::error('Erreur mise à jour profil', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Supprime l'avatar actuel.
     */
    public function removeAvatar(): void
    {
        try {
            DB::beginTransaction();

            $profile = auth()->user()->profile;
            $oldAvatarPath = $profile->getRawOriginal('avatar');

            if ($oldAvatarPath) {
                $profile->update(['avatar' => null]);
                $profile->deleteAvatarFile($oldAvatarPath);
                $profile->clearAvatarCache();
            }

            DB::commit();

            $this->mount();
            session()->flash('success', 'Avatar supprimé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur suppression avatar', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Erreur lors de la suppression de l\'avatar.');
        }
    }

    /**
     * Envoie une notification de vérification d'email.
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
            <div class="sm:col-span-2 flex flex-col items-start gap-4">
                <div class="size-24 relative">
                    @if ($form->avatar)
                        <img src="{{ $form->avatar->temporaryUrl() }}" alt="Aperçu"
                            class="h-24 w-24 flex-none mask mask-squircle bg-gray-800 object-cover">
                    @else
                        <img src="{{ $currentAvatarUrl }}" alt="Avatar actuel"
                            class="h-24 w-24 flex-none mask mask-squircle bg-gray-800 object-cover">
                    @endif

                    <flux:button type="button"
                        class="bg-transparent! size-24! cursor-pointer border-none! absolute! top-0! left-0!"
                        onclick="document.getElementById('avatarInput').click()" aria-label="Changer l'avatar" />
                </div>

                <div class="flex gap-2">
                    <flux:button type="button" size="sm" variant="ghost"
                        onclick="document.getElementById('avatarInput').click()">
                        Changer
                    </flux:button>

                    @if(auth()->user()->profile?->getRawOriginal('avatar'))
                        <flux:button type="button" size="sm" variant="danger" wire:click="removeAvatar"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer votre avatar ?">
                            Supprimer
                        </flux:button>
                    @endif
                </div>

                <flux:text sm color="muted">JPG, PNG ou WEBP. 2MB maximum.</flux:text>

                <input type="file" wire:model="form.avatar" id="avatarInput"
                    accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" />

                @error('form.avatar')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Champs du formulaire -->
            <div class="col-span-4">
                <flux:textarea wire:model="form.bio" label="Biographie" rows="4" placeholder="Parlez-nous de vous..." />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.full_name" label="Nom complet" type="text" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.date_of_birth" type="date" label="Date de naissance" :max="date('Y-m-d')"
                    required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.phone" label="Téléphone" type="tel" required
                    placeholder="+225 XX XX XX XX XX" />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.address" label="Adresse" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.city" label="Ville" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.country" label="Pays" required />
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