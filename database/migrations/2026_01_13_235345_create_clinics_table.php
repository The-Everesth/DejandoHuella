<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_vet_id')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->string('address_line');
            $table->string('neighborhood')->nullable();
            $table->string('city')->default('Durango');
            $table->string('state')->default('Durango');

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('clinics');
    }
};

