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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('openid')->unique()->comment('微信openid');
            $table->string('phone')->unique()->nullable()->comment('手机号');
            $table->string('name')->nullable()->comment('姓名');
            $table->string('avatar')->nullable()->comment('头像URL');
            $table->tinyInteger('gender')->default(0)->comment('性别:0=未知,1=男,2=女');
            $table->integer('points_balance')->default(0)->comment('积分余额');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->tinyInteger('status')->default(1)->comment('状态:0=禁用,1=正常');
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
