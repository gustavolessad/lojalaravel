<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')->whereNotNull('deleted_at')->delete();

            if (Schema::hasColumn('orders', 'deleted_at')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });
            }
        }

        if (Schema::hasTable('customers')) {
            DB::table('customers')->whereNotNull('deleted_at')->delete();

            if (Schema::hasColumn('customers', 'deleted_at')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
