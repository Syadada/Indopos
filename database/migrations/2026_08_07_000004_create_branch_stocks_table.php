<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('store_stock')->default(0); // stok toko dalam base_unit
            $table->integer('warehouse_stock')->default(0); // stok gudang dalam base_unit
            $table->integer('min_store_stock_alert')->default(10); // batas alert toko
            $table->integer('min_warehouse_stock_alert')->default(20); // batas alert gudang
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};
