<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_services', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_services', 'vet_id')) {
                $table->foreignId('vet_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_services', function (Blueprint $table) {
            if (Schema::hasColumn('medical_services', 'vet_id')) {
                $table->dropForeign(['vet_id']);
                $table->dropColumn('vet_id');
            }
        });
    }
};
