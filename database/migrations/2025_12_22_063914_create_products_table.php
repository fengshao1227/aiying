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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->comment('分类ID');
            $table->string('name')->comment('商品名称');
            $table->string('cover_image')->nullable()->comment('商品主图');
            $table->decimal('original_price', 10, 2)->default(0)->comment('原价');
            $table->decimal('price', 10, 2)->comment('现价/售价');
            $table->integer('stock')->default(0)->comment('库存数量');
            $table->integer('sales')->default(0)->comment('销量');
            $table->string('unit')->default('件')->comment('单位');
            $table->text('summary')->nullable()->comment('商品简介');
            $table->text('description')->nullable()->comment('商品详情');
            $table->json('tech_params')->nullable()->comment('技术参数JSON');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态:0=下架,1=上架');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('restrict');
            $table->index('category_id');
            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
