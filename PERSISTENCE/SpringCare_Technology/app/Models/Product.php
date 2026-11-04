<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'model_number', 'description', 'specifications', 'status'])]
class Product extends Model
{
    use CrudTrait;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($userId = backpack_auth()->id()) {
                if (! $product->exists) {
                    $product->created_by = $userId;
                }

                $product->updated_by = $userId;
            }
        });

        static::updated(function (Product $product) {
            if ($product->wasChanged(['status', 'category_id', 'brand_id'])) {
                $product->units()->whereNull('newsletter_notified_at')->get()
                    ->each(fn (ProductUnit $unit) => $unit->queuePostedNotification());
            }
        });

        static::deleting(function (Product $product) {
            if ($product->units()->exists()) {
                throw ValidationException::withMessages([
                    'product' => 'This product has units and cannot be deleted. Archive it instead.',
                ]);
            }
        });
    }
}