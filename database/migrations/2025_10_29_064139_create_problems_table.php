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
    Schema::create('problems', function (Blueprint $table) {
        $table->id();
        $table->string('department');
        $table->string('priority');
        $table->text('statement');
        $table->string('image')->nullable();
        $table->string('status')->default('Pending');
        $table->string('assigned_to')->nullable();
        $table->text('comment')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
