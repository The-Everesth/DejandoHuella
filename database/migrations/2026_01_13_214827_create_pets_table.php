<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->string('sex');
            $table->date('birth_date')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_sterilized')->default(false);
            $table->boolean('is_vaccinated')->default(false);
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
