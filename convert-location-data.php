<?php
/**
 * Script to convert Philippines location data from external source
 * to the NORSUCLINIC application format.
 * 
 * External source: d:\Projects\philippines-province-city-barangay-database\json\
 * Target: d:\Projects\NORSUCLINIC\storage\countries\
 * 
 * Run this script once to generate the JSON files:
 * php convert-location-data.php
 */

$externalBasePath = 'd:/Projects/philippines-province-city-barangay-database/json/';
$targetBasePath = 'd:/Projects/NORSUCLINIC/storage/countries/';

echo "Loading external data files...\n";

// Load external data
$regionsRaw = json_decode(file_get_contents($externalBasePath . 'table_region.json'), true);
$provincesRaw = json_decode(file_get_contents($externalBasePath . 'table_province.json'), true);
$municipalitiesRaw = json_decode(file_get_contents($externalBasePath . 'table_municipality.json'), true);
$barangaysRaw = json_decode(file_get_contents($externalBasePath . 'table_barangay.json'), true);

echo "Loaded " . count($regionsRaw) . " regions\n";
echo "Loaded " . count($provincesRaw) . " provinces\n";
echo "Loaded " . count($municipalitiesRaw) . " municipalities\n";
echo "Loaded " . count($barangaysRaw) . " barangays\n";

// Filter out empty provinces
$provincesRaw = array_filter($provincesRaw, function($province) {
    return !empty($province['province_name']);
});
echo "Filtered to " . count($provincesRaw) . " valid provinces\n";

// Create mapping from old province_id to new id
$provinceMapping = [];
$statesOutput = [];
$stateId = 1;

foreach ($provincesRaw as $province) {
    if (empty($province['province_name'])) continue;
    
    $oldId = $province['province_id'];
    $provinceMapping[$oldId] = $stateId;
    
    $statesOutput[] = [
        'id' => (string)$stateId,
        'name' => $province['province_name'],
        'country_id' => '1' // Philippines
    ];
    
    $stateId++;
}

echo "Created " . count($statesOutput) . " states (provinces)\n";

// Create mapping from old municipality_id to new city_id
$municipalityMapping = [];
$citiesOutput = [];
$cityId = 1;

foreach ($municipalitiesRaw as $municipality) {
    $oldId = $municipality['municipality_id'];
    $oldProvinceId = $municipality['province_id'];
    
    // Skip if province doesn't exist in our mapping
    if (!isset($provinceMapping[$oldProvinceId])) {
        continue;
    }
    
    $municipalityMapping[$oldId] = $cityId;
    
    // Determine type based on name
    $name = $municipality['municipality_name'];
    $type = 'municipality';
    if (stripos($name, 'City of ') === 0 || stripos($name, ' City') !== false) {
        $type = 'city';
    }
    
    $citiesOutput[] = [
        'id' => (string)$cityId,
        'name' => $name,
        'type' => $type,
        'state_id' => (string)$provinceMapping[$oldProvinceId]
    ];
    
    $cityId++;
}

echo "Created " . count($citiesOutput) . " cities/municipalities\n";

// Create barangays
$barangaysOutput = [];
$barangayId = 1;

foreach ($barangaysRaw as $barangay) {
    $oldCityId = $barangay['municipality_id'];
    
    // Skip if city doesn't exist in our mapping
    if (!isset($municipalityMapping[$oldCityId])) {
        continue;
    }
    
    $barangaysOutput[] = [
        'id' => (string)$barangayId,
        'name' => $barangay['barangay_name'],
        'city_id' => (string)$municipalityMapping[$oldCityId]
    ];
    
    $barangayId++;
}

echo "Created " . count($barangaysOutput) . " barangays\n";

// Write output files
echo "\nWriting output files...\n";

// States (Provinces)
$statesJson = json_encode(['states' => $statesOutput], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($targetBasePath . 'states.json', $statesJson);
echo "Written states.json\n";

// Cities (Municipalities)
$citiesJson = json_encode(['cities' => $citiesOutput], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($targetBasePath . 'cities.json', $citiesJson);
echo "Written cities.json\n";

// Barangays
$barangaysJson = json_encode(['barangays' => $barangaysOutput], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($targetBasePath . 'barangays.json', $barangaysJson);
echo "Written barangays.json\n";

echo "\nConversion complete!\n";
echo "Summary:\n";
echo "- Provinces: " . count($statesOutput) . "\n";
echo "- Cities/Municipalities: " . count($citiesOutput) . "\n";
echo "- Barangays: " . count($barangaysOutput) . "\n";
