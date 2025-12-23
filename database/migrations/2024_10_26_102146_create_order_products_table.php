<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->foreignId('orderId')
            ->constrained('orders')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('productId')
            ->constrained('products')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('storeId')
            ->constrained('stores')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('status')->default('Not Received');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
