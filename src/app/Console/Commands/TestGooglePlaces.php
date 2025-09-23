<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GooglePlacesService;
use App\Models\Spot;

class TestGooglePlaces extends Command
{
    protected $signature = 'test:google-places {spot_id?}';
    protected $description = 'Test Google Places API with spot data';

    public function handle()
    {
        $spotId = $this->argument('spot_id');

        if ($spotId) {
            $spot = Spot::find($spotId);
        } else {
            $spot = Spot::first();
        }

        if (!$spot) {
            $this->error('Spot not found');
            return;
        }

        $this->info("Testing Google Places API for: {$spot->name}");
        $this->info("Location: {$spot->latitude}, {$spot->longitude}");

        $googlePlacesService = new GooglePlacesService();

        // Test Place ID search
        $this->info("\n1. Searching for Place ID...");
        $placeId = $googlePlacesService->findPlaceId($spot->name, $spot->latitude, $spot->longitude);

        if ($placeId) {
            $this->info("✓ Place ID found: {$placeId}");
        } else {
            $this->error("✗ Place ID not found");
            return;
        }

        // Test photos
        $this->info("\n2. Fetching photos...");
        $photos = $googlePlacesService->getPlacePhotos($placeId, 3);

        if (count($photos) > 0) {
            $this->info("✓ Found " . count($photos) . " photos:");
            foreach ($photos as $i => $photo) {
                $this->line("  " . ($i + 1) . ". " . $photo);
            }
        } else {
            $this->warn("⚠ No photos found");
        }

        // Test business hours
        $this->info("\n3. Fetching business hours...");
        $businessHours = $googlePlacesService->getBusinessHours($placeId);

        if (count($businessHours) > 0) {
            $this->info("✓ Business hours found:");
            $days = ['日', '月', '火', '水', '木', '金', '土'];
            foreach ($businessHours as $i => $hours) {
                $dayName = $days[$i] ?? "Day {$i}";
                $hoursText = empty($hours) ? '定休日' : implode(', ', $hours);
                $this->line("  {$dayName}: {$hoursText}");
            }
        } else {
            $this->warn("⚠ No business hours found");
        }

        // Test types
        $this->info("\n4. Fetching types...");
        $types = $googlePlacesService->getPlaceTypes($placeId);

        if (count($types) > 0) {
            $this->info("✓ Types found:");
            foreach ($types as $type) {
                $this->line("  - {$type}");
            }
        } else {
            $this->warn("⚠ No types found");
        }

        $this->info("\n✓ Google Places API test completed!");
    }
}
