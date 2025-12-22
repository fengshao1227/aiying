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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('name')->comment('收货人姓名');
            $table->string('phone')->comment('收货人电话');
            $table->string('province')->comment('省份');
            $table->string('city')->comment('城市');
            $table->string('district')->comment('区县');
            $table->string('detail')->comment('详细地址');
            $table->boolean('is_default')->default(false)->comment('是否默认地址');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
