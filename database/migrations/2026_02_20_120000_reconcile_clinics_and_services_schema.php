<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->reconcileClinics();
        $this->reconcileMedicalServices();
        $this->reconcileClinicMedicalServicePivot();
    }

    public function down(): void
    {
    }

    private function reconcileClinics(): void
    {
        if (!Schema::hasTable('clinics')) {
            return;
        }

        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('clinics', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'email')) {
                $table->string('email')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'address')) {
                $table->string('address')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'opening_hours')) {
                $table->string('opening_hours')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'website')) {
                $table->string('website')->nullable();
            }

            if (!Schema::hasColumn('clinics', 'is_public')) {
                $table->boolean('is_public')->default(true);
            }
        });

        if (Schema::hasColumn('clinics', 'owner_vet_id') && Schema::hasColumn('clinics', 'user_id')) {
            DB::table('clinics')
                ->whereNull('user_id')
                ->update(['user_id' => DB::raw('owner_vet_id')]);
        }
    }

    private function reconcileMedicalServices(): void
    {
        if (!Schema::hasTable('medical_services')) {
            return;
        }

        Schema::table('medical_services', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_services', 'type')) {
                $table->string('type')->default('system');
            }

            if (!Schema::hasColumn('medical_services', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('medical_services', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('medical_services', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('medical_services', 'currency')) {
                $table->string('currency', 5)->default('MXN');
            }

            if (!Schema::hasColumn('medical_services', 'is_available')) {
                $table->boolean('is_available')->default(true);
            }
        });

        if (Schema::hasColumn('medical_services', 'base_price') && Schema::hasColumn('medical_services', 'price')) {
            DB::table('medical_services')
                ->whereNull('price')
                ->update(['price' => DB::raw('base_price')]);
        }
    }

    private function reconcileClinicMedicalServicePivot(): void
    {
        if (!Schema::hasTable('clinic_medical_service')) {
            Schema::create('clinic_medical_service', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
                $table->foreignId('medical_service_id')->constrained('medical_services')->cascadeOnDelete();
                $table->decimal('price', 10, 2)->nullable();
                $table->string('currency', 5)->default('MXN');
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->boolean('is_available')->default(true);
                $table->timestamps();
                $table->unique(['clinic_id', 'medical_service_id']);
            });
        } else {
            Schema::table('clinic_medical_service', function (Blueprint $table) {
                if (!Schema::hasColumn('clinic_medical_service', 'price')) {
                    $table->decimal('price', 10, 2)->nullable();
                }

                if (!Schema::hasColumn('clinic_medical_service', 'currency')) {
                    $table->string('currency', 5)->default('MXN');
                }

                if (!Schema::hasColumn('clinic_medical_service', 'duration_minutes')) {
                    $table->unsignedSmallInteger('duration_minutes')->nullable();
                }

                if (!Schema::hasColumn('clinic_medical_service', 'is_available')) {
                    $table->boolean('is_available')->default(true);
                }
            });
        }

        if (Schema::hasTable('clinic_services')) {
            DB::statement(
                "INSERT INTO clinic_medical_service (clinic_id, medical_service_id, price, currency, duration_minutes, is_available, created_at, updated_at)
                 SELECT clinic_id,
                        medical_service_id,
                        price,
                        'MXN',
                        NULL,
                        COALESCE(is_available, 1),
                        COALESCE(created_at, NOW()),
                        COALESCE(updated_at, NOW())
                 FROM clinic_services
                 ON DUPLICATE KEY UPDATE
                    price = COALESCE(VALUES(price), clinic_medical_service.price),
                    is_available = VALUES(is_available),
                    updated_at = VALUES(updated_at)"
            );
        }
    }
};
