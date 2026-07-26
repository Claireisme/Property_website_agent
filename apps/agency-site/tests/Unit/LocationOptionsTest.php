<?php

namespace Tests\Unit;

use App\Support\LocationOptions;
use PHPUnit\Framework\TestCase;

class LocationOptionsTest extends TestCase
{
    public function test_region_key_can_be_inferred_from_property_location_fields(): void
    {
        $this->assertSame('dublin_6', LocationOptions::regionKeyFor('Dublin', 'Dublin 6'));
        $this->assertSame('dublin_6', LocationOptions::regionKeyFor('Dublin', 'Terenure'));
        $this->assertSame('cork', LocationOptions::regionKeyFor('Cork', null));
    }

    public function test_region_can_be_mapped_back_to_property_location_fields(): void
    {
        $this->assertSame([
            'county' => 'Dublin',
            'town' => 'Dublin 6',
        ], LocationOptions::propertyFieldsForRegion('dublin_6'));

        $this->assertSame([
            'county' => 'Cork',
            'town' => null,
        ], LocationOptions::propertyFieldsForRegion('cork'));
    }

    public function test_region_locality_options_follow_myhome_style_flow(): void
    {
        $this->assertSame('-- Dublin 6', LocationOptions::regionFormOptions()['dublin_6']);

        $this->assertContains('Dartry', LocationOptions::localitiesForRegion('dublin_6'));
        $this->assertContains('Terenure', LocationOptions::localitiesForRegion('dublin_6'));
        $this->assertContains('Rathcoole', LocationOptions::localitiesForRegion('dublin_24'));

        $this->assertSame([
            'county' => 'Dublin',
            'town' => 'Terenure',
        ], LocationOptions::propertyFieldsForRegion('dublin_6', 'Terenure'));
    }
}
