<?php

namespace App\Support;

use App\Models\Property;

class PropertySurroundings
{
    public static function amenities(Property $property): array
    {
        $town = $property->town ?: $property->county ?: 'Local';
        $key = strtolower($town);

        $known = [
            'blackrock' => [
                ['type' => 'School', 'name' => 'Blackrock College', 'distance' => 1.2, 'icon' => '🎓'],
                ['type' => 'Supermarket', 'name' => 'Frascati Centre groceries', 'distance' => 0.7, 'icon' => '🛒'],
                ['type' => 'Hospital', 'name' => "St Vincent's University Hospital", 'distance' => 4.2, 'icon' => '✚'],
                ['type' => 'Park', 'name' => 'Blackrock Park', 'distance' => 0.8, 'icon' => '🌳'],
            ],
            'rathmines' => [
                ['type' => 'School', 'name' => 'St Marys College', 'distance' => 0.9, 'icon' => '🎓'],
                ['type' => 'Supermarket', 'name' => 'Swan Shopping Centre', 'distance' => 0.4, 'icon' => '🛒'],
                ['type' => 'Hospital', 'name' => "St James's Hospital", 'distance' => 3.3, 'icon' => '✚'],
                ['type' => 'Park', 'name' => 'Palmerston Park', 'distance' => 1.1, 'icon' => '🌳'],
            ],
            'clontarf' => [
                ['type' => 'School', 'name' => 'Holy Faith Secondary School', 'distance' => 1.0, 'icon' => '🎓'],
                ['type' => 'Supermarket', 'name' => 'Vernon Avenue shops', 'distance' => 0.5, 'icon' => '🛒'],
                ['type' => 'Hospital', 'name' => 'Beaumont Hospital', 'distance' => 4.0, 'icon' => '✚'],
                ['type' => 'Park', 'name' => "St Anne's Park", 'distance' => 1.4, 'icon' => '🌳'],
            ],
            'cork city' => [
                ['type' => 'School', 'name' => 'Cork Educate Together Secondary School', 'distance' => 1.3, 'icon' => '🎓'],
                ['type' => 'Supermarket', 'name' => 'Merchants Quay groceries', 'distance' => 0.8, 'icon' => '🛒'],
                ['type' => 'Hospital', 'name' => 'Cork University Hospital', 'distance' => 3.7, 'icon' => '✚'],
                ['type' => 'Park', 'name' => 'Fitzgerald Park', 'distance' => 1.6, 'icon' => '🌳'],
            ],
            'bray' => [
                ['type' => 'School', 'name' => 'St Cronans Boys National School', 'distance' => 0.9, 'icon' => '🎓'],
                ['type' => 'Supermarket', 'name' => 'Bray Main Street groceries', 'distance' => 0.6, 'icon' => '🛒'],
                ['type' => 'Hospital', 'name' => 'St Columcilles Hospital', 'distance' => 6.2, 'icon' => '✚'],
                ['type' => 'Park', 'name' => "People's Park Bray", 'distance' => 0.9, 'icon' => '🌳'],
            ],
        ];

        if (isset($known[$key])) {
            return $known[$key];
        }

        $seed = crc32($property->public_id ?: $property->slug);
        $distances = [
            0.4 + (($seed % 8) / 10),
            0.3 + ((($seed >> 3) % 7) / 10),
            1.2 + ((($seed >> 6) % 18) / 10),
            0.5 + ((($seed >> 9) % 12) / 10),
        ];

        return [
            ['type' => 'School', 'name' => $town.' National School', 'distance' => round($distances[0], 1), 'icon' => '🎓'],
            ['type' => 'Supermarket', 'name' => $town.' local supermarket', 'distance' => round($distances[1], 1), 'icon' => '🛒'],
            ['type' => 'Hospital', 'name' => $town.' primary care centre', 'distance' => round($distances[2], 1), 'icon' => '✚'],
            ['type' => 'Park', 'name' => $town.' park', 'distance' => round($distances[3], 1), 'icon' => '🌳'],
        ];
    }
}
