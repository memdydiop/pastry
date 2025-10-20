<?php

namespace App\Console\Commands;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;

class AssignRoleCommand extends Command
{
    protected $signature = 'user:assign-role 
                            {email : Email de l\'utilisateur}
                            {role : Nom du rôle à assigner}
                            {--remove : Retirer le rôle au lieu de l\'ajouter}';

    protected $description = 'Assigne ou retire un rôle à un utilisateur';

/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Exécute la commande.
     *
     * @return int
     */
/*******  3cf679dd-0c9d-4f1c-b4b0-3dd8e254d7a8  *******/    public function handle(): int
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');
        $remove = $this->option('remove');

        // Vérifier l'utilisateur
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Utilisateur avec l'email '{$email}' introuvable.");
            return self::FAILURE;
        }

        // Vérifier le rôle
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("❌ Le rôle '{$roleName}' n'existe pas.");
            $this->info("\n📋 Rôles disponibles:");
            Role::all()->each(fn($r) => $this->line("  • {$r->name}"));
            return self::FAILURE;
        }

        // Exécuter l'action
        if ($remove) {
            if (!$user->hasRole($roleName)) {
                $this->warn("⚠️  L'utilisateur ne possède pas le rôle '{$roleName}'.");
                return self::FAILURE;
            }
            
            $user->removeRole($roleName);
            $this->info("✅ Rôle '{$roleName}' retiré à {$user->email}");
        } else {
            if ($user->hasRole($roleName)) {
                $this->warn("⚠️  L'utilisateur possède déjà le rôle '{$roleName}'.");
                return self::FAILURE;
            }
            
            $user->assignRole($roleName);
            $this->info("✅ Rôle '{$roleName}' assigné à {$user->email}");
        }

        // Afficher les rôles actuels
        $this->newLine();
        $this->info("📋 Rôles actuels de l'utilisateur:");
        $user->roles->each(fn($r) => $this->line("  • {$r->name}"));

        return self::SUCCESS;
    }
}