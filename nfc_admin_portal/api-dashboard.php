<?php
/**
 * AJAX API for Dashboard Statistics
 */
require_once 'auth.php';
if (!isAuthenticated()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'Guard.php';
require_once 'Site.php';
require_once 'PatrolLog.php';
require_once 'Incident.php';

$db = new Database();
$guard = new Guard($db);
$site = new Site($db);
$patrol = new PatrolLog($db);
$incident = new Incident($db);

// Statistics
$data = [
    'stats' => [
        'today_patrols'  => $patrol->getTodayCount(),
        'active_guards'  => $guard->countByStatus('active'),
        'open_incidents' => $incident->getCountByStatus('open'),
        'total_sites'    => $site->getTotalCount()
    ],
    'recent_patrols' => [],
    'activity_feed'  => []
];

$recent_raw = $patrol->getToday();

foreach(array_slice($recent_raw, 0, 6) as $log) {
    $data['recent_patrols'][] = [
        'guard_name'   => htmlspecialchars($log['guard_name'] ?? 'Unknown'),
        'site_name'    => htmlspecialchars($log['site_name'] ?? 'Unknown'),
        'tag_label'    => htmlspecialchars($log['tag_label'] ?? 'Unknown'),
        'time'         => date('H:i', strtotime($log['scanned_at']))
    ];
}

foreach(array_slice($recent_raw, 0, 5) as $log) {
    $data['activity_feed'][] = [
        'text' => "<strong>" . htmlspecialchars($log['guard_name']) . "</strong> scanned checkpoint " . htmlspecialchars($log['tag_label']) . " at " . htmlspecialchars($log['site_name']),
        'time' => date('H:i A', strtotime($log['scanned_at']))
    ];
}

header('Content-Type: application/json');
echo json_encode($data);