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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('分类名称');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父级分类ID,0=顶级分类');
            $table->string('icon')->nullable()->comment('分类图标URL');
            $table->integer('sort_order')->default(0)->comment('排序,数字越小越靠前');
            $table->tinyInteger('status')->default(1)->comment('状态:0=禁用,1=启用');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
