<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('system')->index(); // system | custom
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();

            $table->decimal('price', 10, 2)->nullable(); // o ->default(0) si quieres forzarlo
            $table->string('currency', 5)->default('MXN');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_available')->default(true)->index();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_services');
    }
};
