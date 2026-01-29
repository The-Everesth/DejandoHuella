<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // quien envía

            $table->string('subject');
            $table->string('priority')->default('media'); // baja | media | alta
            $table->text('message');

            // Control del flujo
            $table->string('status')->default('pendiente'); // pendiente | visto | respondido | cerrado
            $table->timestamp('seen_at')->nullable();

            // Respuesta admin
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_reply')->nullable();
            $table->timestamp('answered_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
