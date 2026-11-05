<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 24)->unique();
            $table->string('first_name', 160);
            $table->string('last_name', 160);
            $table->string('email', 254)->index();
            $table->string('phone', 40);
            $table->string('state', 120);
            $table->text('delivery_address');
            $table->string('delivery_city', 120)->nullable();
            $table->unsignedTinyInteger('delivery_month');
            $table->unsignedSmallInteger('delivery_year');
            $table->decimal('subtotal', 12, 2);
            $table->string('status', 40)->default('awaiting_payment')->index();
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};