<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('storage', 100)->nullable();
            $table->string('color', 100)->nullable();
            $table->unsignedTinyInteger('battery_health')->nullable();
            $table->string('condition');
            $table->text('defects')->nullable();
            $table->text('included_accessories')->nullable();
            $table->json('photos')->nullable();
            $table->decimal('selling_price', 12, 2);
            $table->string('status')->default('available')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};