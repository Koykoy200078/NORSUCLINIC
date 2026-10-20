@props(['editing' => false, 'existingMedicines' => []])

@php
$config = [
    'url' => parse_url(getRouteByRole('medicines.by.category'), PHP_URL_PATH) ?: getRouteByRole('medicines.by.category'),
    'editing' => $editing,
    'canManagePlanMedicines' => isRole('doctor'),
    'existingMedicines' => $existingMedicines,
];
@endphp
<script type="application/json" id="consultation_medicine_picker_config">{!! \Illuminate\Support\Js::encode($config) !!}</script>
