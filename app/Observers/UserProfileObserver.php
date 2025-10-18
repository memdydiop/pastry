<?php

namespace App\Observers;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;

class UserProfileObserver
{
    /**
     * ✅ CORRECTION : Utilise la nouvelle méthode deleteAvatarFile()
     */
    public function updated(UserProfile $userProfile): void
    {
        // Vider le cache immédiatement
        $userProfile->clearAvatarCache();

        // Si l'avatar a été modifié et qu'un ancien existait
        if ($userProfile->wasChanged('avatar')) {
            $oldAvatarPath = $userProfile->getOriginal('avatar');
            
            if ($oldAvatarPath) {
                try {
                    $userProfile->deleteAvatarFile($oldAvatarPath);
                    Log::info('Avatar remplacé avec succès', [
                        'user_id' => $userProfile->user_id,
                        'old_path' => $oldAvatarPath,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur suppression ancien avatar', [
                        'user_id' => $userProfile->user_id,
                        'path' => $oldAvatarPath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    public function deleting(UserProfile $userProfile): void
    {
        $userProfile->clearAvatarCache();
    }

    public function deleted(UserProfile $userProfile): void
    {
        $avatarPath = $userProfile->getAvatarPathAttribute();

        if ($avatarPath) {
            try {
                $userProfile->deleteAvatarFile($avatarPath);
                Log::info('Avatar supprimé lors de la suppression du profil', [
                    'user_id' => $userProfile->user_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur suppression avatar', [
                    'user_id' => $userProfile->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}