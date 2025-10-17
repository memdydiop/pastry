<?php

namespace App\Observers;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserProfileObserver
{
    /**
     * Gérer l'événement "updated" du modèle UserProfile.
     * 
     * Cette méthode est appelée APRÈS que la mise à jour soit confirmée en base.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    public function updated(UserProfile $userProfile): void
    {
        // Invalider le cache de l'URL de l'avatar pour refléter les changements immédiatement.
        $this->clearAvatarCache($userProfile);

        // Si l'avatar a été modifié ET qu'un ancien avatar existait
        if ($userProfile->wasChanged('avatar') && $userProfile->getOriginal('avatar')) {
            $oldAvatarPath = $userProfile->getOriginal('avatar');
            
            // Supprimer l'ancien avatar du disque de stockage public
            try {
                if (Storage::disk('public')->exists($oldAvatarPath)) {
                    Storage::disk('public')->delete($oldAvatarPath);
                    Log::info("Ancien avatar supprimé: {$oldAvatarPath}");
                }
            } catch (\Exception $e) {
                Log::error("Erreur lors de la suppression de l'ancien avatar: {$e->getMessage()}");
            }
        }
    }

    /**
     * Gérer l'événement "deleted" du modèle UserProfile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    public function deleted(UserProfile $userProfile): void
    {
        // Invalider le cache de l'URL de l'avatar.
        $this->clearAvatarCache($userProfile);

        // S'il y a un avatar, le supprimer du stockage.
        if ($userProfile->avatar) {
            try {
                if (Storage::disk('public')->exists($userProfile->avatar)) {
                    Storage::disk('public')->delete($userProfile->avatar);
                    Log::info("Avatar supprimé lors de la suppression du profil: {$userProfile->avatar_path}");
                }
            } catch (\Exception $e) {
                Log::error("Erreur lors de la suppression de l'avatar: {$e->getMessage()}");
            }
        }
    }

    /**
     * Gérer l'événement "deleting" du modèle UserProfile.
     * 
     * Permet de faire des vérifications avant la suppression si nécessaire.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    public function deleting(UserProfile $userProfile): void
    {
        // Hook disponible pour des validations avant suppression
        // Par exemple: vérifier si le profil peut être supprimé
    }

    /**
     * Supprime la clé de cache de l'URL de l'avatar pour un profil donné.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return void
     */
    protected function clearAvatarCache(UserProfile $userProfile): void
    {
        $cacheKey = 'user:' . $userProfile->user_id . ':avatar';
        Cache::forget($cacheKey);
        Log::debug("Cache avatar vidé pour l'utilisateur {$userProfile->user_id}");
    }
}