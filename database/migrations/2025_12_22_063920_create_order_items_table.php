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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('product_id')->comment('商品ID');
            $table->unsignedBigInteger('sku_id')->nullable()->comment('SKU ID');

            // 商品快照
            $table->string('product_name')->comment('商品名称');
            $table->string('product_image')->nullable()->comment('商品图片');
            $table->string('sku_name')->nullable()->comment('SKU规格名称');
            $table->decimal('price', 10, 2)->comment('商品单价');
            $table->integer('quantity')->comment('购买数量');
            $table->decimal('subtotal', 10, 2)->comment('小计金额');

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
