<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->index();
            $table->text('description')->nullable();
            $table->foreignId('base_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('buy_price', 15, 2)->default(0); // harga beli per base unit
            $table->decimal('sell_price', 15, 2)->default(0); // harga jual per base unit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->integer('conversion_factor')->default(1); // misal 1 Pack = 5 Pcs (conversion_factor = 5)
            $table->decimal('sell_price', 15, 2)->nullable(); // harga jual per unit ini (opsional override)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_unit_conversions');
        Schema::dropIfExists('products');
    }
};
