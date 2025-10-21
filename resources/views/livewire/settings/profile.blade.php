<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Forms\ProfileForm;
use App\Services\ProfileService;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public ProfileForm $form;
    public string $currentAvatarUrl = '';

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

    public function updateProfile(ProfileService $profileService): void
    {
        $this->form->validate();

        try {
            $profileService->updateProfile(
                auth()->user()->profile,
                $this->form->prepareForSave(),
                $this->form->avatar
            );

            $this->mount();
            session()->flash('success', 'Profil mis à jour avec succès !');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour.');
        }
    }

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

    <x-slot name="actions" class="flex gap-x-2">
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <flux:navlist.item
                class="h-8! bg-success hover:bg-success-hover! text-success-foreground! hover:text-success-foreground"
                :href="route('settings.two-factor')" wire:navigate>
                {{ __('Two-Factor Auth') }}
            </flux:navlist.item>
        @endif
        <livewire:settings.delete-user-form />
    </x-slot>

    <form wire:submit="updateProfile" class="my-6 w-full space-y-6">

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-6">

            <!-- Section Avatar CORRIGÉE -->
            <div class="sm:col-span-2 flex flex-col justify-between gap-y-2">
                <label class="block text-sm font-medium text-gray-700">
                    Photo de profil
                </label>

                <div class="flex items-center gap-4">
                    <!-- Avatar actuel ou temporaire -->
                    <div class="relative size-24">
                        @if ($form->avatar)
                            <img src="{{ $form->avatar->temporaryUrl() }}" alt="Aperçu"
                                class="size-24 mask mask-squircle object-cover ">
                        @else
                            <img src="{{ $currentAvatarUrl }}" alt="Avatar actuel"
                                class="size-24  mask mask-squircle object-cover">
                        @endif

                        <!-- Bouton overlay transparent pour cliquer -->
                        <flux:button type="button" onclick="document.getElementById('avatarInput').click()"
                            class="bg-opacity-0 bg-transparent! size-24! cursor-pointer border-none! absolute! inset-0"
                            aria-label="Changer l'avatar" />
                        
                    </div>

                    <div class="flex flex-col gap-2">
                        <flux:button type="button" size="sm" variant="secondary"
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
                </div>

                <flux:text sm color="muted">JPG, PNG ou WEBP. 2MB maximum.</flux:text>

                <input type="file" wire:model="form.avatar" id="avatarInput"
                    accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" />

            </div>

            <!-- Biographie -->
            <div class="col-span-4">
                <div class="space-y-2">
                    <flux:textarea wire:model.live="form.bio" label="Biographie" rows="4"
                        placeholder="Parlez-nous de vous..." maxlength="500" />

                    <div x-data="{ count: $wire.entangle('form.bio').live }" x-init="count = count || ''"
                        class="flex items-center justify-between">

                        <div class="text-xs">
                            <span :class="{
                                    'text-danger font-semibold': count.length > 450,
                                    'text-warning': count.length > 400 && count.length <= 450,
                                    'text-info': count.length <= 400
                                }" x-text="`${count.length} / 500 caractères`">
                            </span>
                        </div>

                        <div class="w-32 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300" :class="{
                                    'bg-danger': count.length > 450,
                                    'bg-warning': count.length > 400 && count.length <= 450,
                                    'bg-info': count.length <= 400
                                }" :style="`width: ${(count.length / 500) * 100}%`">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Autres champs -->
            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.full_name" label="Nom complet" type="text" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.date_of_birth" type="date" label="Date de naissance"
                    :max="date('Y-m-d')" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.phone" label="Téléphone" type="tel" required
                    placeholder="+225 XX XX XX XX XX" />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.address" label="Adresse" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.city" label="Ville" required />
            </div>

            <div class="sm:col-span-2">
                <flux:input wire:model.blur="form.country" label="Pays" required />
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateProfile">{{ __('Enregistrer') }}</span>
                <span wire:loading wire:target="updateProfile">Enregistrement...</span>
            </flux:button>
        </div>
    </form>

</x-layouts.content>