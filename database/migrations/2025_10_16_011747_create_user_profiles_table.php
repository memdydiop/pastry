<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->index();
            
            // Tous les champs sont required pour cohérence avec la validation
            $table->string('full_name')->index('full_name');
            $table->date('date_of_birth');
            $table->string('phone', 20)->unique();
            $table->string('address', 255);
            $table->string('city', 100);
            $table->string('country', 100);
            $table->text('bio');
            $table->string('avatar')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};