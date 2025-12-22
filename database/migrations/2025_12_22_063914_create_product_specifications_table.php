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
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->comment('商品ID');
            $table->string('sku_code')->unique()->comment('SKU编码');
            $table->json('spec_values')->nullable()->comment('规格值JSON,如{"精度":"0.01","电压":"220V"}');
            $table->decimal('price', 10, 2)->nullable()->comment('SKU价格,null=使用商品默认价格');
            $table->integer('stock')->default(0)->comment('SKU库存');
            $table->string('image')->nullable()->comment('SKU图片');
            $table->tinyInteger('status')->default(1)->comment('状态:0=禁用,1=启用');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('product_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
