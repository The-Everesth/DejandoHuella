<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adoption_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Evita que publiquen la misma mascota 2 veces activas
            $table->unique(['pet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_posts');
    }
};
