<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clinic_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('medical_service_id')->constrained('medical_services')->onDelete('cascade');

            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->unique(['clinic_id', 'medical_service_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('clinic_services');
    }
};

