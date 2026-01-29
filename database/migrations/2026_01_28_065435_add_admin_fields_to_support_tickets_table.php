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
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'priority')) {
                $table->string('priority')->default('medium')->index();
            }

            if (!Schema::hasColumn('support_tickets', 'admin_reply')) {
                $table->text('admin_reply')->nullable();
            }

            if (!Schema::hasColumn('support_tickets', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->index();
            }

            if (!Schema::hasColumn('support_tickets', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->index();
            }

            // status: SOLO si no existe (en tu caso ya existe)
            if (!Schema::hasColumn('support_tickets', 'status')) {
                $table->string('status')->default('open')->index();
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'priority')) $table->dropColumn('priority');
            if (Schema::hasColumn('support_tickets', 'admin_reply')) $table->dropColumn('admin_reply');
            if (Schema::hasColumn('support_tickets', 'replied_at')) $table->dropColumn('replied_at');
            if (Schema::hasColumn('support_tickets', 'closed_at')) $table->dropColumn('closed_at');
            // status lo dejamos, porque ya existía antes (mejor no tocarlo)
        });
    }
};
