<?php
/**
 * Interactive Map Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Database.php';
require_once 'Site.php';
require_once 'NfcTag.php';
require_once 'Incident.php';

$page_title = 'Map';

// Initialize database and models
$db = new Database();
$site = new Site($db);
$nfc_tag = new NfcTag($db);
$incident = new Incident($db);

// Get all sites and NFC tags
$all_sites = $site->getAll();
$all_tags = $nfc_tag->getAll();
$all_incidents = $incident->getAll();
include 'header.php';
?>

<style>
    /* Override default layout constraints to make the map fill the screen */
    html, body {
        height: 100vh;
        overflow: hidden !important;
    }
    .main-content {
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; }
    }
    .content {
        padding: 0 !important;
        display: flex !important;
        flex-direction: column;
        flex: 1 !important;
        position: relative;
    }
    #map {
        width: 100%;
        height: 100% !important;
        flex: 1;
        background: #05214a;
    }
</style>

<div id="map"></div>

<!-- IMPORTANT: Replace YOUR_GOOGLE_MAPS_API_KEY with your actual key from Google Cloud Console -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>"></script>
<script>
    // Default to Midland Estate coordinates (-26.2041, 28.0473) if no sites are found
    const defaultLat = <?php echo !empty($all_sites) ? ($all_sites[0]['latitude'] ?: -26.2041) : -26.2041; ?>;
    const defaultLng = <?php echo !empty($all_sites) ? ($all_sites[0]['longitude'] ?: 28.0473) : 28.0473; ?>;

    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: parseFloat(defaultLat), lng: parseFloat(defaultLng) },
        mapTypeControl: true,
        fullscreenControl: true,
        streetViewControl: true
    });

    // Tracker for the currently open info window
    let activeInfoWindow = null;

    // Site markers (blue)
    const siteMarkers = [];
    <?php foreach ($all_sites as $s): ?>
        <?php if (!empty($s['latitude']) && !empty($s['longitude'])): ?>
        const siteMarker_<?php echo $s['id']; ?> = new google.maps.Marker({
            position: { lat: parseFloat(<?php echo $s['latitude']; ?>), lng: parseFloat(<?php echo $s['longitude']; ?>) },
            map: map,
            title: '<?php echo htmlspecialchars($s['name']); ?>',
            icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        });
        siteMarkers.push(siteMarker_<?php echo $s['id']; ?>);

        const siteInfoWindow_<?php echo $s['id']; ?> = new google.maps.InfoWindow({
            content: `
                <div style="padding: 10px; max-width: 250px;">
                    <h4 style="margin: 0 0 8px 0; color: #ffffff;"><?php echo htmlspecialchars($s['name']); ?></h4>
                    <p style="margin: 0 0 8px 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Address:</strong> <?php echo htmlspecialchars($s['address'] ?? 'N/A'); ?>
                    </p>
                </div>
            `
        });

        siteMarker_<?php echo $s['id']; ?>.addListener('click', () => {
            if (activeInfoWindow) activeInfoWindow.close();
            siteInfoWindow_<?php echo $s['id']; ?>.open(map, siteMarker_<?php echo $s['id']; ?>);
            activeInfoWindow = siteInfoWindow_<?php echo $s['id']; ?>;
        });
        <?php endif; ?>
    <?php endforeach; ?>

    // NFC Tag markers (red)
    const tagMarkers = [];
    <?php foreach ($all_tags as $tag): ?>
        <?php if (!empty($tag['latitude']) && !empty($tag['longitude'])): ?>
        const tagMarker_<?php echo $tag['id']; ?> = new google.maps.Marker({
            position: { lat: parseFloat(<?php echo $tag['latitude']; ?>), lng: parseFloat(<?php echo $tag['longitude']; ?>) },
            map: map,
            title: '<?php echo htmlspecialchars($tag['label']); ?>',
            icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        });
        tagMarkers.push(tagMarker_<?php echo $tag['id']; ?>);

        const tagInfoWindow_<?php echo $tag['id']; ?> = new google.maps.InfoWindow({
            content: `
                <div style="padding: 10px; max-width: 250px;">
                    <h4 style="margin: 0 0 8px 0; color: var(--color-danger);"><?php echo htmlspecialchars($tag['label']); ?></h4>
                    <p style="margin: 0 0 8px 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Site:</strong> <?php echo htmlspecialchars($tag['site_name'] ?? 'Unknown'); ?>
                    </p>
                    <p style="margin: 0 0 8px 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Tag UID:</strong> <?php echo htmlspecialchars(substr($tag['tag_uid'], 0, 12) . '...'); ?>
                    </p>
                </div>
            `
        });

        tagMarker_<?php echo $tag['id']; ?>.addListener('click', () => {
            if (activeInfoWindow) activeInfoWindow.close();
            tagInfoWindow_<?php echo $tag['id']; ?>.open(map, tagMarker_<?php echo $tag['id']; ?>);
            activeInfoWindow = tagInfoWindow_<?php echo $tag['id']; ?>;
        });
        <?php endif; ?>
    <?php endforeach; ?>

    // Incident markers (yellow)
    const incidentMarkers = [];
    <?php foreach ($all_incidents as $inc): ?>
        <?php if (!empty($inc['latitude']) && !empty($inc['longitude'])): ?>
        const incidentMarker_<?php echo $inc['id']; ?> = new google.maps.Marker({
            position: { lat: parseFloat(<?php echo $inc['latitude']; ?>), lng: parseFloat(<?php echo $inc['longitude']; ?>) },
            map: map,
            title: '<?php echo htmlspecialchars($inc['title']); ?>',
            icon: 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png'
        });
        incidentMarkers.push(incidentMarker_<?php echo $inc['id']; ?>);

        const incidentInfoWindow_<?php echo $inc['id']; ?> = new google.maps.InfoWindow({
            content: `
                <div style="padding: 10px; max-width: 250px;">
                    <h4 style="margin: 0 0 8px 0; color: #854d0e;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($inc['title']); ?></h4>
                    <p style="margin: 0 0 8px 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Severity:</strong> <span class="badge badge-<?php echo $inc['severity']; ?>"><?php echo ucfirst($inc['severity']); ?></span>
                    </p>
                    <p style="margin: 0 0 8px 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Site:</strong> <?php echo htmlspecialchars($inc['site_name'] ?? 'N/A'); ?>
                    </p>
                    <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-secondary);">
                        <strong>Reported:</strong> <?php echo date('M d, Y', strtotime($inc['reported_at'])); ?>
                    </p>
                </div>
            `
        });

        incidentMarker_<?php echo $inc['id']; ?>.addListener('click', () => {
            if (activeInfoWindow) activeInfoWindow.close();
            incidentInfoWindow_<?php echo $inc['id']; ?>.open(map, incidentMarker_<?php echo $inc['id']; ?>);
            activeInfoWindow = incidentInfoWindow_<?php echo $inc['id']; ?>;
        });
        <?php endif; ?>
    <?php endforeach; ?>

    // Fit bounds to show all markers
    if (siteMarkers.length > 0 || tagMarkers.length > 0 || incidentMarkers.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        siteMarkers.forEach(marker => bounds.extend(marker.getPosition()));
        tagMarkers.forEach(marker => bounds.extend(marker.getPosition()));
        incidentMarkers.forEach(marker => bounds.extend(marker.getPosition()));
        map.fitBounds(bounds);
        if (map.getZoom() > 15) {
            map.setZoom(15);
        }
    }

    // Legend
    const legend = document.createElement('div');
    legend.style.cssText = `
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 15px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 25px;
        margin: 10px;
    `;
    legend.innerHTML = `
        <div style="margin-bottom: 10px; font-weight: bold; color: var(--color-accent);">Map Legend</div>
        <div><img src="https://maps.google.com/mapfiles/ms/icons/blue-dot.png" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;"> Patrol Sites</div>
        <div><img src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;"> NFC Checkpoints</div>
        <div><img src="https://maps.google.com/mapfiles/ms/icons/yellow-dot.png" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;"> Security Incidents</div>
    `;
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
</script>

<?php include 'footer.php'; ?>
