@php
    $mapsApiKey = config('services.google.maps_api_key', env('VITE_GOOGLE_MAPS_API_KEY'));
@endphp

@if($mapsApiKey)
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places"></script>
@endif

<style>
    .pac-container {
        z-index: 999999 !important;
        font-family: inherit;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
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
        border-top: 1px solid #f3f4f6;
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

<script>
    (function () {
        const apiKey = @json($mapsApiKey);

        function ensureGoogleMapsLoaded(callback) {
            if (window.google && window.google.maps && window.google.maps.places) {
                callback();
                return;
            }
            if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places';
                script.async = true;
                script.defer = true;
                script.onload = () => {
                    callback();
                };
                document.head.appendChild(script);
            } else {
                const interval = setInterval(() => {
                    if (window.google && window.google.maps && window.google.maps.places) {
                        clearInterval(interval);
                        callback();
                    }
                }, 100);
            }
        }

        function setFieldValue(fieldKey, value, wire) {
            if (value === undefined || value === null) return;

            // Update DOM input directly
            const idMap = {
                'address': 'admin-spot-address-input',
                'city': 'admin-spot-city-input',
                'state': 'admin-spot-state-input',
                'country': 'admin-spot-country-input',
                'latitude': 'admin-spot-latitude-input',
                'longitude': 'admin-spot-longitude-input',
            };

            let input = document.getElementById(idMap[fieldKey]);
            if (!input) {
                input = document.querySelector(`[name="data.${fieldKey}"]`) ||
                        document.querySelector(`[wire\\:model*="${fieldKey}"]`) ||
                        document.querySelector(`[wire\\:model\\.live*="${fieldKey}"]`);
            }

            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Sync with Livewire wire
            if (wire) {
                try {
                    wire.set(`data.${fieldKey}`, value);
                } catch (e) {
                    console.warn(`Could not set wire state for data.${fieldKey}:`, e);
                }
            }
        }

        function setupAutocomplete(inputElement, wire) {
            if (!inputElement) return;

            ensureGoogleMapsLoaded(() => {
                if (inputElement._googleAutocompleteInitialized) return;
                inputElement._googleAutocompleteInitialized = true;

                const autocomplete = new window.google.maps.places.Autocomplete(inputElement, {
                    types: ['geocode'],
                });

                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace();
                    if (!place || !place.geometry) return;

                    const formattedAddress = place.formatted_address || place.name || '';
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();

                    let city = '';
                    let state = '';
                    let country = '';

                    if (place.address_components) {
                        for (const component of place.address_components) {
                            const types = component.types;
                            if (types.includes('locality')) {
                                city = component.long_name;
                            } else if (!city && (types.includes('sublocality') || types.includes('sublocality_level_1') || types.includes('postal_town') || types.includes('administrative_area_level_2'))) {
                                city = component.long_name;
                            }

                            if (types.includes('administrative_area_level_1')) {
                                state = component.short_name || component.long_name;
                            }

                            if (types.includes('country')) {
                                country = component.long_name;
                            }
                        }
                    }

                    // Autofill all fields in DOM & Livewire
                    setFieldValue('address', formattedAddress, wire);
                    setFieldValue('city', city, wire);
                    setFieldValue('state', state, wire);
                    setFieldValue('country', country || 'Canada', wire);
                    setFieldValue('latitude', lat, wire);
                    setFieldValue('longitude', lng, wire);
                });
            });
        }

        function registerAlpineComponent() {
            if (window.Alpine) {
                window.Alpine.data('filamentGoogleAutocomplete', (wire) => ({
                    init() {
                        this.$nextTick(() => {
                            const el = this.$el;
                            const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
                            setupAutocomplete(input, wire || this.$wire);
                        });
                    }
                }));
            }
        }

        if (window.Alpine) {
            registerAlpineComponent();
        } else {
            document.addEventListener('alpine:init', registerAlpineComponent);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('admin-spot-address-input');
            if (el) setupAutocomplete(el, window.Livewire?.find(el.closest('[wire\\:id]')?.getAttribute('wire:id')));
        });
    })();
</script>
