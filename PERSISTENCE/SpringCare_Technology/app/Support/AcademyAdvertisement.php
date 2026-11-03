<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AcademyAdvertisement
{
    public const TIMEZONE = 'Africa/Lagos';

    private const PATH = 'storefront/academy-advertisement.json';

    public function settings(): array
    {
        $disk = Storage::disk('local');
        $saved = $disk->exists(self::PATH) ? $disk->json(self::PATH) : [];

        return array_replace([
            'enabled' => true,
            'starts_at' => null,
            'ends_at' => null,
        ], is_array($saved) ? $saved : []);
    }

    public function isVisible(): bool
    {
        $settings = $this->settings();
        $now = now();

        return (bool) $settings['enabled']
            && (! $settings['starts_at'] || $now->greaterThanOrEqualTo(Carbon::parse($settings['starts_at'], self::TIMEZONE)))
            && (! $settings['ends_at'] || $now->lessThan(Carbon::parse($settings['ends_at'], self::TIMEZONE)));
    }

    public function save(array $settings): void
    {
        $saved = Storage::disk('local')->put(self::PATH, json_encode([
            'enabled' => (bool) $settings['enabled'],
            'starts_at' => $settings['starts_at'] ?? null,
            'ends_at' => $settings['ends_at'] ?? null,
        ], JSON_THROW_ON_ERROR));

        if (! $saved) {
            throw new RuntimeException('The advertisement settings could not be saved.');
        }
    }
}