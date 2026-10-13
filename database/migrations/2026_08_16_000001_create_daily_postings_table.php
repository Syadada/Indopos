<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_postings', function (Blueprint $table) {
            $table->id();
            $table->string('posting_code')->unique();
            $table->date('posting_date');
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->integer('total_transactions_count')->default(0);
            $table->integer('total_items_sold')->default(0); // total barang keluar
            $table->decimal('total_money_in', 15, 2)->default(0); // total omset / uang masuk
            $table->decimal('total_money_out', 15, 2)->default(0); // total hpp modal / uang keluar
            $table->decimal('net_profit', 15, 2)->default(0); // profit bersih
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['posting_date', 'branch_id']);
        });

        Schema::create('daily_posting_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('total_qty_sold')->default(0);
            $table->integer('total_base_qty_sold')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0); // uang masuk per barang
            $table->decimal('total_cost', 15, 2)->default(0); // uang keluar per barang
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_posting_items');
        Schema::dropIfExists('daily_postings');
    }
};
