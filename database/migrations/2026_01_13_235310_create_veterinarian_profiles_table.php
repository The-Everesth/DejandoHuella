<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('veterinarian_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('professional_license')->nullable(); // cédula
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('veterinarian_profiles');
    }
};
