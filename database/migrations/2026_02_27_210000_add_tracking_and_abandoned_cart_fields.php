<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_code')->nullable()->after('shipping_days');
            $table->string('tracking_url')->nullable()->after('tracking_code');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('abandoned_cart_sent_at')->nullable()->after('coupon_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_code', 'tracking_url']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('abandoned_cart_sent_at');
        });
    }
};
