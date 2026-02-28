<?php
if (!isset($geographies_telecalling)) {
    $geographies_telecalling = [];
    $geographies_field = [];
    $geographies_both = [];
    $geographies_for_map = [];
    try {
        if (!function_exists('getDb')) {
            require_once __DIR__ . '/../config/database.php';
        }
        $pdo = getDb();
        $all = $pdo->query('SELECT name, state_code, coverage_type FROM geographies WHERE is_active = 1 ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $state_coords = [
            'IN-PB' => [31.1471, 75.3412],
            'IN-HP' => [31.1048, 77.1734],
            'IN-RJ' => [27.0238, 74.2179],
            'IN-HR' => [29.0588, 76.0856],
            'IN-UP' => [26.8467, 80.9462],
            'IN-DL' => [28.7041, 77.1025],
            'IN-UK' => [30.0668, 79.0193],
            'IN-JK' => [33.7782, 76.5762],
            'IN-LA' => [34.1526, 77.5771],
            'IN-GJ' => [22.2587, 71.1924],
            'IN-MH' => [19.0760, 72.8777],
            'IN-MP' => [22.9734, 78.6569],
            'IN-CT' => [21.2787, 81.8661],
            'IN-WB' => [22.9868, 87.8550],
            'IN-OR' => [20.9517, 85.0985],
            'IN-BR' => [25.0961, 85.3131],
            'IN-JH' => [23.6102, 85.2799],
            'IN-SK' => [27.3390, 88.6065],
            'IN-AS' => [26.2006, 92.9376],
            'IN-NL' => [26.1584, 94.5624],
            'IN-MN' => [24.6637, 93.9063],
            'IN-MZ' => [23.1645, 92.9376],
            'IN-TR' => [23.9408, 91.9882],
            'IN-GA' => [15.2993, 74.1240],
            'IN-KA' => [15.3173, 75.7139],
            'IN-TN' => [11.1271, 78.6569],
            'IN-KL' => [10.8505, 76.2711],
            'IN-AP' => [15.9129, 79.7400],
            'IN-TG' => [18.1124, 79.0193],
            'IN-PY' => [11.9416, 79.8083],
        ];
        $name_to_coords = [
            'punjab' => [31.1471, 75.3412],
            'himachal pradesh' => [31.1048, 77.1734],
            'rajasthan' => [27.0238, 74.2179],
            'haryana' => [29.0588, 76.0856],
            'uttar pradesh' => [26.8467, 80.9462],
            'delhi' => [28.7041, 77.1025],
            'uttarakhand' => [30.0668, 79.0193],
            'jammu and kashmir' => [33.7782, 76.5762],
            'ladakh' => [34.1526, 77.5771],
            'gujarat' => [22.2587, 71.1924],
            'maharashtra' => [19.0760, 72.8777],
            'madhya pradesh' => [22.9734, 78.6569],
            'chhattisgarh' => [21.2787, 81.8661],
            'west bengal' => [22.9868, 87.8550],
            'odisha' => [20.9517, 85.0985],
            'bihar' => [25.0961, 85.3131],
        ];
        foreach ($all as $g) {
            if ($g['coverage_type'] === 'telecalling')
                $geographies_telecalling[] = $g['name'];
            elseif ($g['coverage_type'] === 'field')
                $geographies_field[] = $g['name'];
            else
                $geographies_both[] = $g['name'];
            $lat = null;
            $lng = null;
            $code = !empty($g['state_code']) ? strtoupper(trim($g['state_code'])) : '';
            if ($code && isset($state_coords[$code])) {
                list($lat, $lng) = $state_coords[$code];
            } elseif (isset($name_to_coords[mb_strtolower(trim($g['name']))])) {
                list($lat, $lng) = $name_to_coords[mb_strtolower(trim($g['name']))];
            }
            if ($lat !== null && $lng !== null) {
                $geographies_for_map[] = ['name' => $g['name'], 'coverage_type' => $g['coverage_type'], 'lat' => $lat, 'lng' => $lng];
            }
        }
    } catch (Throwable $e) { /* use empty lists */
    }
}
if (!isset($geographies_for_map))
    $geographies_for_map = [];
$india_map_geographies_json = json_encode($geographies_for_map);
$india_boundary_path = dirname(__DIR__) . '/data/india-boundary.geojson';
$india_boundary_path_js = [];
if (is_file($india_boundary_path)) {
    $geo = json_decode(file_get_contents($india_boundary_path), true);
    if (!empty($geo['features'][0]['geometry']['coordinates'][0])) {
        foreach ($geo['features'][0]['geometry']['coordinates'][0] as $c) {
            $india_boundary_path_js[] = ['lat' => (float) $c[1], 'lng' => (float) $c[0]];
        }
    }
}
$india_boundary_path_json = json_encode($india_boundary_path_js);
if (!function_exists('getDb') || !defined('GOOGLE_MAPS_API_KEY')) {
    $site_config = dirname(__DIR__) . '/config/site.php';
    if (is_file($site_config))
        require_once $site_config;
}
$google_maps_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
?>
<!-- Our Presence / Geographies + India Map Start -->
<div class="container-fluid contact overflow-hidden py-5">
    <div class="container py-5">
        <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="sub-style">
                <h5 class="sub-title text-primary px-3">Our Presence</h5>
            </div>
            <h1 class="display-5 mb-4">Geographies We Cater To</h1>
            <p class="mb-0">We serve multiple states across North India through <strong>Telecalling</strong> and
                <strong>Field</strong> operations. More regions can be added as we expand.
            </p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary"><i class="bi bi-telephone-outbound me-2"></i>Telecalling
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <?php
                            $tel = array_merge($geographies_telecalling, $geographies_both);
                            if (empty($tel)):
                                echo '<li class="text-muted">— Add in Admin → Geographies</li>';
                            else:
                                foreach ($tel as $n):
                                    echo '<li>' . htmlspecialchars($n) . '</li>';
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="card h-100 border-secondary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-secondary"><i class="bi bi-geo-alt me-2"></i>Field</h5>
                        <ul class="list-unstyled mb-0">
                            <?php
                            $fld = array_merge($geographies_field, $geographies_both);
                            if (empty($fld)):
                                echo '<li class="text-muted">— Add in Admin → Geographies</li>';
                            else:
                                foreach ($fld as $n):
                                    echo '<li>' . htmlspecialchars($n) . '</li>';
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                <h5 class="mb-3">Our Presence on the Map</h5>
                <p class="text-muted">We operate across Punjab, Himachal Pradesh, Rajasthan, Haryana and neighbouring
                    regions — with scope to add more geographies in future.</p>
                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <span class="badge border-0 text-white" style="background:#e05218;">Telecalling</span>
                    <span class="badge border-0 text-white" style="background:#6c757d;">Field</span>
                    <span class="badge border-0 text-white" style="background:#198754;">Both</span>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="position-relative rounded overflow-hidden border shadow-sm india-map-wrap">
                    <div id="india-map" class="india-map-leaflet-states" aria-label="Interactive India states map"></div>
                    <div id="india-map-error" class="india-map-error d-none text-muted text-center p-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
window.INDIA_MAP_GEOGRAPHIES = <?= $india_map_geographies_json ?>;
(function() {
    var GEOJSON_URL = 'data/india-master.geojson';
    var MAP_CENTER = [22.5937, 78.9629];
    var MAP_ZOOM = 5;
    var STYLES = {
        default: { fillColor: '#1E90FF', fillOpacity: 0.7, color: '#ffffff', weight: 1 },
        hover:   { fillColor: '#63B3ED', fillOpacity: 0.85, color: '#ffffff', weight: 1 },
        selected: { fillColor: '#FF5733', fillOpacity: 0.9, color: '#ffffff', weight: 1 }
    };
    var PRESENCE_COLORS = { telecalling: '#e05218', field: '#6c757d', both: '#198754' };
    var PRESENCE_LABELS = { telecalling: 'Telecalling', field: 'Field', both: 'Both' };

    function getStateName(props) {
        if (!props) return 'State';
        return props.ST_NM || props.st_nm || props.name || props.NAME || props.State || 'State';
    }

    function initMap() {
        var container = document.getElementById('india-map');
        var errorEl = document.getElementById('india-map-error');
        if (!container) return;

        var map = L.map('india-map', {
            center: MAP_CENTER,
            zoom: MAP_ZOOM,
            zoomControl: true
        });
        L.control.zoom({ position: 'topleft' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var selectedLayer = null;

        function onEachFeature(feature, layer) {
            var name = getStateName(feature.properties);
            layer.bindTooltip(name, {
                permanent: false,
                direction: 'top',
                className: 'india-map-tooltip'
            });
            layer.on({
                mouseover: function() {
                    this.setStyle(STYLES.hover);
                    this.bringToFront();
                },
                mouseout: function() {
                    if (this !== selectedLayer) {
                        this.setStyle(STYLES.default);
                    }
                },
                click: function() {
                    if (selectedLayer) selectedLayer.setStyle(STYLES.default);
                    selectedLayer = this;
                    this.setStyle(STYLES.selected);
                    this.bringToFront();
                }
            });
            layer.setStyle(STYLES.default);
        }

        fetch(GEOJSON_URL)
            .then(function(res) {
                if (!res.ok) throw new Error('GeoJSON load failed: ' + res.status);
                return res.json();
            })
            .then(function(geojson) {
                if (!geojson || !geojson.features || !geojson.features.length) {
                    throw new Error('Invalid GeoJSON: no features');
                }
                var layer = L.geoJSON(geojson, {
                    style: STYLES.default,
                    onEachFeature: onEachFeature
                }).addTo(map);
                var bounds = layer.getBounds();
                if (bounds.isValid()) map.fitBounds(bounds, { padding: [20, 20], maxZoom: 6 });

                var geos = window.INDIA_MAP_GEOGRAPHIES || [];
                geos.forEach(function(g) {
                    var type = (g.coverage_type || 'both').toLowerCase();
                    var color = PRESENCE_COLORS[type] || PRESENCE_COLORS.both;
                    var label = PRESENCE_LABELS[type] || type;
                    var icon = L.divIcon({
                        className: 'india-map-presence-marker',
                        html: '<span style="background-color:' + color + ';width:14px;height:14px;border-radius:50%;display:inline-block;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35);"></span>',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });
                    var popupContent = '<b>' + (g.name || '') + '</b><br><span style="display:inline-block;margin-top:4px;padding:2px 8px;border-radius:4px;background:' + color + ';color:#fff;font-size:0.75rem;">' + label + '</span>';
                    L.marker([g.lat, g.lng], { icon: icon }).addTo(map).bindPopup(popupContent);
                });
            })
            .catch(function(err) {
                if (errorEl) {
                    errorEl.textContent = 'Map data could not be loaded. ' + (err.message || '');
                    errorEl.classList.remove('d-none');
                }
                if (typeof console !== 'undefined' && console.warn) {
                    console.warn('India map GeoJSON error:', err);
                }
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
    } else {
        initMap();
    }
})();
</script>
<!-- Our Presence End -->