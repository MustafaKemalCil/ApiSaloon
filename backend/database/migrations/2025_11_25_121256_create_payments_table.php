<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Appointment bağlantısı (zorunlu)
            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');

            // Customer bağlantısı (kolay erişim için)
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            // Ödenecek tutar
            $table->decimal('amount', 10, 2);

            // Ödeme yöntemi (nakit, kart, dolar vb.)
            $table->string('method')->nullable();

            // Açıklama / Not
            $table->text('note')->nullable();

            // Ödeme Tarihi
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
