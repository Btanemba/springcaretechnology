<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->text('website')->nullable();
        });

        DB::table('products')->whereNotNull('website')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->whereNull('website')
                    ->update(['website' => $product->website]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('website');
        });
    }
};