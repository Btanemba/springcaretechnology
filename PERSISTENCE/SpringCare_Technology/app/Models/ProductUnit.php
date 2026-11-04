<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Jobs\NotifySubscribersOfProductUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'sku', 'serial_number', 'website', 'storage', 'color', 'battery_health', 'condition', 'defects', 'included_accessories', 'photos', 'selling_price', 'status'])]
class ProductUnit extends Model
{
    use CrudTrait;

    public const CONDITIONS = [
        'excellent' => 'Excellent',
        'good' => 'Good',
        'fair' => 'Fair',
        'for_parts' => 'For Parts',
    ];

    public const STATUSES = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'sold' => 'Sold',
    ];

    protected function casts(): array
    {
        return [
            'battery_health' => 'integer',
            'photos' => 'array',
            'selling_price' => 'decimal:2',
            'newsletter_notified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function photoUrls(): array
    {
        $photos = is_array($this->photos) ? $this->photos : json_decode($this->photos ?? '[]', true);

        return collect(is_array($photos) ? $photos : [])
            ->filter(fn ($photo) => is_string($photo) && $photo !== '')
            ->map(fn (string $photo) => Storage::disk('public')->url($photo))
            ->values()->all();
    }

    public function isPubliclyPostedWithPhotos(): bool
    {
        if ($this->status !== 'available' || $this->photoUrls() === []) {
            return false;
        }

        $product = $this->product()->with(['brand', 'category'])->first();

        return $product?->status === 'published'
            && $product->brand?->is_active
            && $product->category?->is_active;
    }

    public function queuePostedNotification(): void
    {
        if ($this->newsletter_notified_at || ! $this->isPubliclyPostedWithPhotos()) {
            return;
        }

        NotifySubscribersOfProductUnit::dispatch($this->getKey())->afterCommit();
    }

    protected static function booted(): void
    {
        static::saving(function (ProductUnit $unit) {
            if ($userId = backpack_auth()->id()) {
                if (! $unit->exists) {
                    $unit->created_by = $userId;
                }

                $unit->updated_by = $userId;
            }
        });

        static::created(fn (ProductUnit $unit) => $unit->queuePostedNotification());
        static::updated(function (ProductUnit $unit) {
            if ($unit->wasChanged(['status', 'photos'])) {
                $unit->queuePostedNotification();
            }
        });

        static::deleted(function (ProductUnit $unit) {
            $photos = is_array($unit->photos) ? $unit->photos : json_decode($unit->photos ?? '[]', true);

            if ($photos) {
                Storage::disk('public')->delete($photos);
            }
        });
    }
}