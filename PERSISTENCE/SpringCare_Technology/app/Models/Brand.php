<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'slug', 'logo', 'is_active'])]
class Brand extends Model
{
    use CrudTrait;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Brand $brand) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
        });
    }
}