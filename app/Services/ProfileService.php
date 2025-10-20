<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile; // Assurez-vous que cette ligne est présente
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Crée un nouveau profil utilisateur.
     */
    public function createProfile(User $user, array $data, ?UploadedFile $avatar = null): UserProfile
    {
        return DB::transaction(function () use ($user, $data, $avatar) {
            // Upload de l'avatar
            if ($avatar) {
                $data['avatar'] = $this->uploadAvatar($avatar);
            }

            // Création du profil
            $profile = $user->profile()->create($data);

            // Marquer le profil comme complété
            $user->update(['profile_completed' => true]);

            Log::info('Profil créé avec succès', [
                'user_id' => $user->id,
                'has_avatar' => isset($data['avatar']),
            ]);

            return $profile;
        });
    }

    /**
     * Met à jour un profil existant.
     */
    public function updateProfile(UserProfile $profile, array $data, ?UploadedFile $avatar = null): UserProfile
    {
        return DB::transaction(function () use ($profile, $data, $avatar) {
            $oldAvatarPath = $profile->getRawOriginal('avatar');

            // Upload du nouvel avatar
            if ($avatar) {
                $data['avatar'] = $this->uploadAvatar($avatar);
                
                // Supprimer l'ancien avatar en utilisant la méthode du modèle
                if ($oldAvatarPath) {
                    $profile->deleteAvatarFile($oldAvatarPath);
                }
            }

            // Mise à jour
            $profile->update($data);
            $profile->clearAvatarCache();

            Log::info('Profil mis à jour', [
                'user_id' => $profile->user_id,
                'avatar_changed' => isset($data['avatar']),
            ]);

            return $profile->fresh();
        });
    }

    /**
     * Supprime l'avatar d'un profil.
     */
    public function removeAvatar(UserProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            $avatarPath = $profile->getRawOriginal('avatar');

            if ($avatarPath) {
                $profile->update(['avatar' => null]);
                // Suppression du fichier via la méthode du modèle
                $profile->deleteAvatarFile($avatarPath);
                $profile->clearAvatarCache();

                Log::info('Avatar supprimé', ['user_id' => $profile->user_id]);
            }
        });
    }

    /**
     * Upload un avatar et retourne son chemin.
     */
    protected function uploadAvatar(UploadedFile $file): string
    {
        $filename = sprintf(
            '%s_%s.%s',
            auth()->id(),
            time(),
            $file->extension()
        );

        return $file->storeAs('avatars', $filename, 'public');
    }

    // REMARQUE : La méthode deleteAvatar() a été supprimée, 
    // car la logique est maintenant gérée par UserProfile::deleteAvatarFile().

    /**
     * Vérifie si un profil est complet.
     */
    public function isProfileComplete(User $user): bool
    {
        if (!$user->profile) {
            return false;
        }

        // Utilisation de la constante centralisée
        foreach (UserProfile::REQUIRED_FIELDS as $field) {
            if (empty($user->profile->$field)) {
                return false;
            }
        }

        return true;
    }
}