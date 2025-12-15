<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('user_id');

            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();
            $table->string('status')->default('pending'); 
            // GÜNCEL ve DOĞRU ALANLAR
            $table->string('service');                 // title yerine
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
