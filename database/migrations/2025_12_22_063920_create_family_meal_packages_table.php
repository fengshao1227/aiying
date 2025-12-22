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
        Schema::create('family_meal_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('套餐名称');
            $table->string('cover_image')->nullable()->comment('封面图');
            $table->decimal('price', 10, 2)->comment('套餐价格');
            $table->integer('duration_days')->default(1)->comment('服务天数');
            $table->text('description')->nullable()->comment('套餐描述');
            $table->json('services')->nullable()->comment('服务项目JSON');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态:0=下架,1=上架');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_meal_packages');
    }
};
