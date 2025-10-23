<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('livraison'); // livraison ou facturation
            $table->string('adresse');
            $table->string('complement_adresse')->nullable();
            $table->string('code_postal');
            $table->string('ville');
            $table->string('pays')->default('Côte d\'Ivoire');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adresses');
    }
};