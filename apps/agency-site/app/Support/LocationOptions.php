<?php

namespace App\Support;

use Illuminate\Support\Str;

class LocationOptions
{
    public static function regions(): array
    {
        return collect(self::searchLocations())
            ->pluck('display', 'key')
            ->all();
    }

    public static function regionFormOptions(): array
    {
        return collect(self::searchLocations())
            ->pluck('label', 'key')
            ->all();
    }

    public static function searchLocations(): array
    {
        $dublinLocalitiesByPostcode = [
            '1' => ['Dorset Street', 'IFSC', 'North City Centre', 'Parnell Square', 'Smithfield'],
            '2' => ['Ballsbridge', 'Baggot Street', 'Grand Canal Dock', 'Merrion Square', 'South City Centre'],
            '3' => ['Clonliffe', 'Clontarf', 'Dollymount', 'East Wall', 'Fairview', 'Marino'],
            '4' => ['Ballsbridge', 'Donnybrook', 'Irishtown', 'Merrion', 'Ringsend', 'Sandymount'],
            '5' => ['Artane', 'Donnycarney', 'Harmonstown', 'Killester', 'Raheny'],
            '6' => ['Dartry', "Harold's Cross", 'Milltown', 'Ranelagh', 'Rathgar', 'Rathmines', 'Terenure'],
            '6W' => ['Harolds Cross', 'Kimmage', 'Terenure'],
            '7' => ['Arbour Hill', 'Cabra', 'Drumcondra', 'Phibsborough', 'Stoneybatter'],
            '8' => ['Dolphins Barn', 'Inchicore', 'Kilmainham', 'Portobello', 'The Coombe', 'The Liberties'],
            '9' => ['Beaumont', 'Drumcondra', 'Glasnevin', 'Santry', 'Whitehall'],
            '10' => ['Ballyfermot', 'Cherry Orchard'],
            '11' => ['Ballymun', 'Finglas', 'Glasnevin North'],
            '12' => ['Bluebell', 'Crumlin', 'Drimnagh', 'Walkinstown'],
            '13' => ['Baldoyle', 'Bayside', 'Donaghmede', 'Howth', 'Sutton'],
            '14' => ['Churchtown', 'Clonskeagh', 'Dundrum', 'Goatstown', 'Rathfarnham', 'Windy Arbour'],
            '15' => ['Blanchardstown', 'Castleknock', 'Clonsilla', 'Mulhuddart', 'Tyrrelstown'],
            '16' => ['Ballinteer', 'Ballyboden', 'Firhouse', 'Knocklyon', 'Rathfarnham'],
            '17' => ['Belcamp', 'Coolock', 'Priorswood'],
            '18' => ['Cabinteely', 'Carrickmines', 'Foxrock', 'Leopardstown', 'Sandyford', 'Stepaside'],
            '20' => ['Chapelizod', 'Palmerstown'],
            '22' => ['Clondalkin', 'Liffey Valley', 'Neilstown'],
            '24' => ['Firhouse', 'Jobstown', 'Kilnamanagh', 'Rathcoole', 'Tallaght', 'Templeogue'],
        ];

        $entries = [
            self::location('dublin', 'Dublin - All', ['Dublin']),
            self::location('dublin_county', '-- Dublin County', ['Dublin'], [], 1),
            self::location('dublin_city', '-- Dublin City', ['Dublin'], ['Dublin City', 'Ranelagh', 'Rathmines', 'Clontarf'], 1, ['North City Centre', 'South City Centre', 'Smithfield', 'Temple Bar']),
            self::location('dublin_north', '-- Dublin North', ['Dublin'], ['Malahide', 'Swords', 'Portmarnock', 'Balbriggan'], 1, ['Balbriggan', 'Malahide', 'Portmarnock', 'Skerries', 'Swords']),
            self::location('dublin_south', '-- Dublin South', ['Dublin'], ['Blackrock', 'Dun Laoghaire', 'Dalkey', 'Stillorgan', 'Sandyford'], 1, ['Blackrock', 'Dalkey', 'Dun Laoghaire', 'Sandyford', 'Stillorgan']),
            self::location('dublin_west', '-- Dublin West', ['Dublin'], ['Lucan', 'Tallaght', 'Blanchardstown', 'Clondalkin'], 1, ['Blanchardstown', 'Clondalkin', 'Lucan', 'Tallaght']),
        ];

        foreach (['1', '2', '3', '4', '5', '6', '6W', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '20', '22', '24'] as $postcode) {
            $entries[] = self::location(
                'dublin_'.$postcode,
                '-- Dublin '.$postcode,
                ['Dublin'],
                ['Dublin '.$postcode],
                1,
                $dublinLocalitiesByPostcode[$postcode] ?? [],
            );
        }

        $entries = [
            ...$entries,
            self::location('carlow', 'Carlow', ['Carlow']),
            self::location('cavan', 'Cavan', ['Cavan']),
            self::location('clare', 'Clare', ['Clare']),
            self::location('cork', 'Cork', ['Cork']),
            self::location('cork_city', '-- Cork City', ['Cork'], ['Cork City'], 1),
            self::location('east_cork', '-- East Cork', ['Cork'], ['Midleton', 'Youghal', 'Cobh', 'Carrigtwohill'], 1),
            self::location('west_cork', '-- West Cork', ['Cork'], ['Bantry', 'Clonakilty', 'Skibbereen', 'Bandon'], 1),
        ];

        foreach ([
            'Ballincollig',
            'Ballydehob',
            'Ballyphehane',
            'Bandon',
            'Bantry',
            'Bishopstown',
            'Blackrock',
            'Blarney',
            'Boherbue',
            'Bweeng',
            'Carrigaline',
            'Carrigtwohill',
            'Charleville',
            'Churchtown',
            'Clonakilty',
            'Cobh',
            'Crosshaven',
            'Donnybrook',
            'Drimoleague',
            'Dunmanway',
            'Fermoy',
            'Glanmire',
            'Glasheen',
            'Glengarriff',
            'Glounthaune',
            'Kanturk',
            'Kildorrery',
            'Kilworth',
            'Kinsale',
            'Leap',
            'Macroom',
            'Mallow',
            'Midleton',
            'Millstreet',
            'Mitchelstown',
            'Monkstown',
            'Passage West',
            'Raheen',
            'Rathcoole',
            'Rosscarbery',
            'Schull',
            'Skibbereen',
            'Togher',
            'Turners Cross',
            'Watergrasshill',
            'Youghal',
        ] as $town) {
            $entries[] = self::location(Str::slug('cork '.$town, '_'), '-- '.$town, ['Cork'], [$town], 1);
        }

        $entries = [
            ...$entries,
            self::location('donegal', 'Donegal', ['Donegal']),
        ];

        return [
            ...$entries,
            self::location('galway', 'Galway', ['Galway']),
            self::location('galway_city', '-- Galway City', ['Galway'], ['Galway City'], 1),
            self::location('kerry', 'Kerry', ['Kerry']),
            self::location('killarney', '-- Killarney', ['Kerry'], ['Killarney'], 1),
            self::location('kildare', 'Kildare', ['Kildare']),
            self::location('naas', '-- Naas', ['Kildare'], ['Naas'], 1),
            self::location('kilkenny', 'Kilkenny', ['Kilkenny']),
            self::location('laois', 'Laois', ['Laois']),
            self::location('leitrim', 'Leitrim', ['Leitrim']),
            self::location('limerick', 'Limerick', ['Limerick']),
            self::location('limerick_city', '-- Limerick City', ['Limerick'], ['Limerick City'], 1),
            self::location('longford', 'Longford', ['Longford']),
            self::location('louth', 'Louth', ['Louth']),
            self::location('mayo', 'Mayo', ['Mayo']),
            self::location('meath', 'Meath', ['Meath']),
            self::location('monaghan', 'Monaghan', ['Monaghan']),
            self::location('offaly', 'Offaly', ['Offaly']),
            self::location('roscommon', 'Roscommon', ['Roscommon']),
            self::location('sligo', 'Sligo', ['Sligo']),
            self::location('tipperary', 'Tipperary', ['Tipperary']),
            self::location('waterford', 'Waterford', ['Waterford']),
            self::location('waterford_city', '-- Waterford City', ['Waterford'], ['Waterford City'], 1),
            self::location('westmeath', 'Westmeath', ['Westmeath']),
            self::location('wexford', 'Wexford', ['Wexford']),
            self::location('wexford_town', '-- Wexford Town', ['Wexford'], ['Wexford Town'], 1),
            self::location('wicklow', 'Wicklow', ['Wicklow']),
            self::location('bray', '-- Bray', ['Wicklow'], ['Bray'], 1),
        ];
    }

    public static function counties(): array
    {
        return [
            'Carlow' => 'Carlow',
            'Cavan' => 'Cavan',
            'Clare' => 'Clare',
            'Cork' => 'Cork',
            'Donegal' => 'Donegal',
            'Dublin' => 'Dublin',
            'Galway' => 'Galway',
            'Kerry' => 'Kerry',
            'Kildare' => 'Kildare',
            'Kilkenny' => 'Kilkenny',
            'Laois' => 'Laois',
            'Leitrim' => 'Leitrim',
            'Limerick' => 'Limerick',
            'Longford' => 'Longford',
            'Louth' => 'Louth',
            'Mayo' => 'Mayo',
            'Meath' => 'Meath',
            'Monaghan' => 'Monaghan',
            'Offaly' => 'Offaly',
            'Roscommon' => 'Roscommon',
            'Sligo' => 'Sligo',
            'Tipperary' => 'Tipperary',
            'Waterford' => 'Waterford',
            'Westmeath' => 'Westmeath',
            'Wexford' => 'Wexford',
            'Wicklow' => 'Wicklow',
        ];
    }

    public static function townsForRegion(string $region): array
    {
        return self::locationByKey($region)['towns'] ?? [];
    }

    public static function localitiesForRegion(?string $region): array
    {
        $location = $region ? self::locationByKey($region) : null;
        $localities = $location['localities'] ?? [];

        if ($localities === []) {
            $towns = $location['towns'] ?? [];
            $localities = count($towns) > 1 ? $towns : [];
        }

        return collect($localities)
            ->mapWithKeys(fn (string $locality): array => [$locality => $locality])
            ->all();
    }

    public static function countiesForRegion(string $region): array
    {
        return self::locationByKey($region)['counties'] ?? [];
    }

    public static function regionKeyFor(?string $county, ?string $town): ?string
    {
        $county = trim((string) $county);
        $town = trim((string) $town);

        if ($town !== '') {
            foreach (self::searchLocations() as $location) {
                if (
                    (in_array($town, $location['towns'], true) || in_array($town, $location['localities'], true))
                    && ($county === '' || in_array($county, $location['counties'], true))
                ) {
                    return $location['key'];
                }
            }
        }

        if ($county !== '') {
            foreach (self::searchLocations() as $location) {
                if ($location['towns'] === [] && $location['counties'] === [$county]) {
                    return $location['key'];
                }
            }

            foreach (self::searchLocations() as $location) {
                if (in_array($county, $location['counties'], true)) {
                    return $location['key'];
                }
            }
        }

        return null;
    }

    public static function propertyFieldsForRegion(?string $region, ?string $locality = null): array
    {
        $location = $region ? self::locationByKey($region) : null;
        $locality = trim((string) $locality);

        return [
            'county' => $location['counties'][0] ?? null,
            'town' => $locality !== ''
                ? $locality
                : (count($location['towns'] ?? []) === 1 ? $location['towns'][0] : null),
        ];
    }

    private static function location(string $key, string $label, array $counties, array $towns = [], int $depth = 0, array $localities = []): array
    {
        $display = ltrim($label, '- ');

        return [
            'key' => $key,
            'label' => $label,
            'display' => $display,
            'depth' => $depth,
            'counties' => $counties,
            'towns' => $towns,
            'localities' => $localities,
            'search' => strtolower($display.' '.$label.' '.implode(' ', $counties).' '.implode(' ', $towns).' '.implode(' ', $localities)),
        ];
    }

    private static function locationByKey(string $key): ?array
    {
        return collect(self::searchLocations())->firstWhere('key', $key);
    }
}
