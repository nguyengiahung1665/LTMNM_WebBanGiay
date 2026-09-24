<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('variant_sku')->nullable()->after('product_variant_id');
            $table->string('variant_size')->nullable()->after('variant_sku');
            $table->string('variant_color_name')->nullable()->after('variant_size');
            $table->string('variant_color_code')->nullable()->after('variant_color_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variant_sku', 'variant_size', 'variant_color_name', 'variant_color_code']);
        });
    }
};
