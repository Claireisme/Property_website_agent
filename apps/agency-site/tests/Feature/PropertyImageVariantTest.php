<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyImageVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storage_urls_are_same_origin_relative_paths(): void
    {
        $this->assertSame(
            '/storage/properties/example.jpg',
            Storage::disk('public')->url('properties/example.jpg'),
        );
    }

    public function test_property_image_upload_generates_webp_variants(): void
    {
        Storage::fake('public');

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Test Property',
            'status' => 'available',
        ]);

        $path = UploadedFile::fake()
            ->image('front.jpg', 1200, 800)
            ->store('properties/originals', 'public');

        $image = PropertyImage::query()->create([
            'property_id' => $property->id,
            'original_url' => $path,
        ])->refresh();

        $this->assertNotNull($image->thumbnail_url);
        $this->assertNotNull($image->card_url);
        $this->assertNotNull($image->detail_url);
        $this->assertNotNull($image->large_url);

        Storage::disk('public')->assertExists($image->thumbnail_url);
        Storage::disk('public')->assertExists($image->card_url);
        Storage::disk('public')->assertExists($image->detail_url);
        Storage::disk('public')->assertExists($image->large_url);
    }
}
