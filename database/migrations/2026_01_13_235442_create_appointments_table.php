<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('medical_service_id')->constrained('medical_services')->onDelete('cascade');

            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('vet_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('scheduled_at');
            $table->string('status')->default('pendiente'); // pendiente|confirmada|atendida|cancelada
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('appointments');
    }
};
