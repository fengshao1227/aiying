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
        Schema::create('points_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->enum('type', ['earn', 'spend', 'refund'])->comment('类型:earn=获得,spend=消费,refund=退还');
            $table->integer('points')->comment('积分数量(正数=增加,负数=减少)');
            $table->integer('balance_after')->comment('变动后余额');
            $table->string('source')->comment('来源:order=订单,refund=退款,admin=后台调整等');
            $table->unsignedBigInteger('source_id')->nullable()->comment('来源ID(如订单ID)');
            $table->string('description')->nullable()->comment('描述');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('type');
            $table->index(['source', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_history');
    }
};
