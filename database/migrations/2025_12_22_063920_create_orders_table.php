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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique()->comment('订单号');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->enum('order_type', ['goods', 'family_meal'])->comment('订单类型:goods=商品订单,family_meal=家庭套餐');

            // 收货地址快照
            $table->string('receiver_name')->comment('收货人姓名');
            $table->string('receiver_phone')->comment('收货人电话');
            $table->string('receiver_province')->comment('省份');
            $table->string('receiver_city')->comment('城市');
            $table->string('receiver_district')->comment('区县');
            $table->string('receiver_detail')->comment('详细地址');

            // 金额信息
            $table->decimal('goods_amount', 10, 2)->default(0)->comment('商品总金额');
            $table->decimal('shipping_fee', 10, 2)->default(0)->comment('运费');
            $table->integer('points_used')->default(0)->comment('使用积分数量');
            $table->decimal('points_discount', 10, 2)->default(0)->comment('积分抵扣金额');
            $table->decimal('total_amount', 10, 2)->comment('订单总金额(实付)');

            // 订单状态
            $table->tinyInteger('order_status')->default(0)->comment('订单状态:0=待支付,1=待发货,2=待收货,3=已完成,4=已取消,5=已退款');
            $table->tinyInteger('payment_status')->default(0)->comment('支付状态:0=未支付,1=已支付,2=已退款');

            // 备注与时间
            $table->text('remark')->nullable()->comment('订单备注');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->timestamp('shipped_at')->nullable()->comment('发货时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamp('cancelled_at')->nullable()->comment('取消时间');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->index('order_no');
            $table->index('user_id');
            $table->index(['order_type', 'order_status']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
