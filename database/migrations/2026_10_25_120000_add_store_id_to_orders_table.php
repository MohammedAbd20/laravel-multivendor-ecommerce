<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('user_id')
                ->constrained('stores')->nullOnDelete();
        });

        // Backfill existing orders from the store of their first product.
        DB::table('orders')->whereNull('store_id')->orderBy('id')->each(function ($order) {
            $storeId = DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('order_items.order_id', $order->id)
                ->value('products.store_id');

            if ($storeId) {
                DB::table('orders')->where('id', $order->id)->update(['store_id' => $storeId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
