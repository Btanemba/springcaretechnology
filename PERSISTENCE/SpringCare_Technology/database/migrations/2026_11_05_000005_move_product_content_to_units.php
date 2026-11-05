<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addDescription = ! Schema::hasColumn('product_units', 'description');
        $addSpecifications = ! Schema::hasColumn('product_units', 'specifications');

        if ($addDescription || $addSpecifications) {
            Schema::table('product_units', function (Blueprint $table) use ($addDescription, $addSpecifications) {
                if ($addDescription) {
                    $table->text('description')->nullable();
                }
                if ($addSpecifications) {
                    $table->text('specifications')->nullable();
                }
            });
        }

        $hasProductDescription = Schema::hasColumn('products', 'description');
        $hasProductSpecifications = Schema::hasColumn('products', 'specifications');

        if ($hasProductDescription || $hasProductSpecifications) {
            $columns = ['id'];
            if ($hasProductDescription) {
                $columns[] = 'description';
            }
            if ($hasProductSpecifications) {
                $columns[] = 'specifications';
            }

            DB::table('products')->select($columns)->orderBy('id')->chunkById(100, function ($products) use ($hasProductDescription, $hasProductSpecifications): void {
                foreach ($products as $product) {
                    $values = [];
                    if ($hasProductDescription && $product->description !== null) {
                        $values['description'] = $product->description;
                    }
                    if ($hasProductSpecifications && $product->specifications !== null) {
                        $values['specifications'] = $product->specifications;
                    }

                    if ($values !== []) {
                        DB::table('product_units')->where('product_id', $product->id)->update($values);
                    }
                }
            });
        }

        $dropColumns = array_values(array_filter(
            ['description', 'specifications'],
            fn (string $column): bool => Schema::hasColumn('products', $column),
        ));

        if ($dropColumns !== []) {
            Schema::table('products', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    public function down(): void
    {
        $addDescription = ! Schema::hasColumn('products', 'description');
        $addSpecifications = ! Schema::hasColumn('products', 'specifications');

        if ($addDescription || $addSpecifications) {
            Schema::table('products', function (Blueprint $table) use ($addDescription, $addSpecifications) {
                if ($addDescription) {
                    $table->text('description')->nullable();
                }
                if ($addSpecifications) {
                    $table->text('specifications')->nullable();
                }
            });
        }

        DB::table('products')->select('id')->orderBy('id')->chunkById(100, function ($products): void {
            foreach ($products as $product) {
                $unit = DB::table('product_units')->where('product_id', $product->id)->orderBy('id')
                    ->first(['description', 'specifications']);

                if ($unit) {
                    DB::table('products')->where('id', $product->id)->update([
                        'description' => $unit->description,
                        'specifications' => $unit->specifications,
                    ]);
                }
            }
        });

        $dropColumns = array_values(array_filter(
            ['description', 'specifications'],
            fn (string $column): bool => Schema::hasColumn('product_units', $column),
        ));

        if ($dropColumns !== []) {
            Schema::table('product_units', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }
};