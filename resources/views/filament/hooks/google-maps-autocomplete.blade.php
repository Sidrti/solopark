@php
    $mapsApiKey = config('services.google.maps_api_key', env('VITE_GOOGLE_MAPS_API_KEY'));
@endphp
@if($mapsApiKey)
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places" async defer></script>
@endif
<style>
    .pac-container {
        z-index: 99999 !important;
        font-family: inherit;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(209, 213, 219, 0.8);
        margin-top: 4px;
        background-color: #ffffff;
    }
    .dark .pac-container {
        background-color: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    .pac-item {
        padding: 8px 12px;
        font-size: 13px;
        cursor: pointer;
        line-height: 20px;
    }
    .dark .pac-item {
        border-top-color: #374151;
        color: #d1d5db;
    }
    .pac-item:hover, .pac-item-selected {
        background-color: #f3f4f6;
    }
    .dark .pac-item:hover, .dark .pac-item-selected {
        background-color: #374151;
    }
    .pac-item-query {
        font-size: 13px;
        color: #111827;
    }
    .dark .pac-item-query {
        color: #f9fafb;
    }
</style>
