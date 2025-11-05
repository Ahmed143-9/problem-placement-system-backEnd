<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->enum('department', ['Tech', 'Business', 'Accounts']);
            $table->enum('priority', ['High', 'Medium', 'Low']);
            $table->text('statement');
            $table->enum('status', ['pending', 'in_progress', 'done', 'pending_approval'])->default('pending');
            $table->string('created_by');
            $table->string('assigned_to')->nullable();
            $table->string('submitted_for_approval_by')->nullable();
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('problems');
    }
};