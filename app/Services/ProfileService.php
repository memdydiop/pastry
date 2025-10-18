<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
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
                
                // Supprimer l'ancien avatar
                if ($oldAvatarPath) {
                    $this->deleteAvatar($oldAvatarPath);
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
                $this->deleteAvatar($avatarPath);
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

    /**
     * Supprime un fichier avatar.
     */
    protected function deleteAvatar(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            Log::error('Erreur suppression avatar', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Vérifie si un profil est complet.
     */
    public function isProfileComplete(User $user): bool
    {
        if (!$user->profile) {
            return false;
        }

        $requiredFields = [
            'full_name',
            'date_of_birth',
            'phone',
            'address',
            'city',
            'country',
            'bio',
        ];

        foreach ($requiredFields as $field) {
            if (empty($user->profile->$field)) {
                return false;
            }
        }

        return true;
    }
}