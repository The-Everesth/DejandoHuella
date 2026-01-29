<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adoption_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoption_post_id')->constrained('adoption_posts')->onDelete('cascade');
            $table->foreignId('applicant_id')->constrained('users')->onDelete('cascade');

            $table->text('message')->nullable();
            $table->string('status')->default('pendiente'); // pendiente | aprobada | rechazada

            $table->timestamps();

            // Evita 2 solicitudes del mismo usuario al mismo post
            $table->unique(['adoption_post_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_requests');
    }
};
