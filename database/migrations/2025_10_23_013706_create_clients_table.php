<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('particulier'); // particulier ou entreprise
            
            // Informations personnelles
            $table->string('nom');
            $table->string('raison_sociale')->nullable();
            $table->string('email')->unique();
            $table->string('telephone');
            $table->string('telephone_secondaire')->nullable();
                        
            // Fidélité
            $table->integer('points_fidelite')->default(0);
            $table->decimal('score_client', 8, 2)->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('type');
            $table->index('email');
            $table->index('telephone');
            $table->index('score_client');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};