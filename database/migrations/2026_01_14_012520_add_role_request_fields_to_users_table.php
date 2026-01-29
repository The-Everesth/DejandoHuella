<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('requested_role')->nullable()->after('remember_token');
            $table->string('role_request_status')->default('none')->after('requested_role'); 
            // none | pending | approved | rejected

            $table->timestamp('role_requested_at')->nullable()->after('role_request_status');
            $table->timestamp('role_reviewed_at')->nullable()->after('role_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['requested_role','role_request_status','role_requested_at','role_reviewed_at']);
        });
    }
};

