<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            // Nullable only while this migration backfills existing installations.
            // Application validation requires it for every variant created afterwards.
            $table->string('sku')->nullable()->after('product_id');
            $table->decimal('price', 12, 0)->default(0)->after('stock');
            $table->decimal('sale_price', 12, 0)->nullable()->after('price');
            $table->boolean('status')->default(true)->after('sale_price');
            $table->softDeletes();
        });

        DB::table('product_variants')
            ->orderBy('id')
            ->each(function (object $variant): void {
                $product = DB::table('products')->where('id', $variant->product_id)->first(['price', 'sale_price', 'status']);

                DB::table('product_variants')->where('id', $variant->id)->update([
                    'sku' => 'P'.$variant->product_id.'-V'.$variant->id,
                    'price' => $product?->price ?? 0,
                    'sale_price' => $product?->sale_price,
                    'status' => (bool) ($product?->status ?? true),
                ]);
            });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique('sku', 'product_variants_sku_unique');
            $table->index(['product_id', 'status', 'stock'], 'product_variants_sellable_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('product_variants_sellable_index');
            $table->dropUnique('product_variants_sku_unique');
            $table->dropSoftDeletes();
            $table->dropColumn(['sku', 'price', 'sale_price', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
