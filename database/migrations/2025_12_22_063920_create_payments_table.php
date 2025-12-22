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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('payment_no')->unique()->comment('支付流水号');
            $table->string('transaction_id')->nullable()->unique()->comment('第三方交易号(微信支付)');
            $table->enum('payment_method', ['wechat'])->default('wechat')->comment('支付方式');
            $table->decimal('amount', 10, 2)->comment('支付金额');
            $table->tinyInteger('status')->default(0)->comment('支付状态:0=待支付,1=已支付,2=已退款,3=支付失败');
            $table->timestamp('paid_at')->nullable()->comment('支付完成时间');
            $table->json('payment_data')->nullable()->comment('支付原始数据JSON');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->index('payment_no');
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
