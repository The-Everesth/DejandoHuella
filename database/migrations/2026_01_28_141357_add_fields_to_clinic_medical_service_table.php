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
    Schema::table('clinic_medical_service', function (Blueprint $table) {
        if (!Schema::hasColumn('clinic_medical_service', 'price')) {
            $table->decimal('price', 10, 2)->nullable()->after('medical_service_id');
        }
        if (!Schema::hasColumn('clinic_medical_service', 'currency')) {
            $table->string('currency', 5)->default('MXN')->after('price');
        }
        if (!Schema::hasColumn('clinic_medical_service', 'duration_minutes')) {
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('currency');
        }
        if (!Schema::hasColumn('clinic_medical_service', 'is_available')) {
            $table->boolean('is_available')->default(true)->index()->after('duration_minutes');
        }
    });
}

public function down(): void
{
    Schema::table('clinic_medical_service', function (Blueprint $table) {
        if (Schema::hasColumn('clinic_medical_service', 'is_available')) $table->dropColumn('is_available');
        if (Schema::hasColumn('clinic_medical_service', 'duration_minutes')) $table->dropColumn('duration_minutes');
        if (Schema::hasColumn('clinic_medical_service', 'currency')) $table->dropColumn('currency');
        if (Schema::hasColumn('clinic_medical_service', 'price')) $table->dropColumn('price');
    });
}

};
