<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('target_username')->nullable();
            $table->enum('type', ['new_problem', 'assignment', 'status_change', 'transfer', 'completion']);
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('problem_id')->nullable();
            $table->boolean('for_admin_or_leader')->default(false);
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('notification_timestamp')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};