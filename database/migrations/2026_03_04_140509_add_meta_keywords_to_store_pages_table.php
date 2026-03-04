<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_pages', function (Blueprint $table) {
            $table->string('meta_keywords', 255)->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('store_pages', function (Blueprint $table) {
            $table->dropColumn('meta_keywords');
        });
    }
};
