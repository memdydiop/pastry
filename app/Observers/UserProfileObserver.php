<?php

namespace App\Observers;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Storage;

class UserProfileObserver
{/**
     * Gérer l'événement "updating" (mise à jour) du modèle UserProfile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    public function updating(UserProfile $userProfile): void
    {
        // Si le champ 'avatar' a été modifié et qu'un ancien avatar existe
        if ($userProfile->isDirty('avatar') && $userProfile->getOriginal('avatar')) {
            // Supprimer l'ancien avatar du disque de stockage public
            Storage::disk('public')->delete($userProfile->getOriginal('avatar'));
        }
    }

    /**
     * Gérer l'événement "deleting" (suppression) du modèle UserProfile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    public function deleting(UserProfile $userProfile): void
    {
        // S'il y a un avatar, le supprimer
        if ($userProfile->avatar) {
            Storage::disk('public')->delete($userProfile->avatar);
        }
    }
}
