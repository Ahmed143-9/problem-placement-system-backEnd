<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('problem_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained()->onDelete('cascade');
            $table->string('from_user');
            $table->string('to_user');
            $table->string('transferred_by');
            $table->timestamp('transfer_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('problem_transfers');
    }
};