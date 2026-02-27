<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('value');
        });

        // Backfill slugs para registros existentes
        DB::table('attributes')->get()->each(function ($attr) {
            DB::table('attributes')
                ->where('id', $attr->id)
                ->update(['slug' => Str::slug($attr->name)]);
        });

        DB::table('attribute_values')->get()->each(function ($val) {
            DB::table('attribute_values')
                ->where('id', $val->id)
                ->update(['slug' => Str::slug($val->value)]);
        });

        // Agora tornar not-null e garantir unicidade de slug em attribute_values por atributo
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique(['attribute_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropUnique(['attribute_id', 'slug']);
            $table->dropColumn('slug');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
