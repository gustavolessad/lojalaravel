<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('color_hex');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type', 20)->default('select')->after('slug');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('color_hex', 20)->nullable()->after('display_value');
        });
    }
};
