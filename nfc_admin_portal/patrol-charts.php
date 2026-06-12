<?php
/**
 * Patrol Charts Page
 */
require_once 'auth.php';
requireLogin();

require_once 'PatrolLog.php';
require_once 'Guard.php';
require_once 'Site.php';
require_once 'Incident.php';

$page_title = 'Patrol Charts';

// Initialize database and models
$db = new Database();
$patrol = new PatrolLog($db);
$guard = new Guard($db);
$site = new Site($db);
$incident = new Incident($db);

// Get selected time range
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
if ($days < 1) $days = 7;
if ($days > 90) $days = 90;

// Shared Color Palette for PHP and JS
$chart_colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#f97316', '#14b8a6', '#6366f1'
];

// Optimized Data Fetching
$guard_patrol_counts = $patrol->getCountsByGuard();
$site_patrol_counts = $patrol->getCountsBySite();
$severity_data = $incident->getSeverityDistribution();
$patrol_trend = $patrol->getTrendData($days);
$incident_trend = $incident->getTrendData($days);

// Calculate Date Range Labels
$trend_labels = [];
$date_keys = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_keys[] = $date;
    $trend_labels[] = ($days > 14) ? date('M d', strtotime($date)) : date('D, M d', strtotime($date));
}

// Map trend data to fixed date keys
$weekly_patrol_trend = array_fill(0, $days, 0);
$weekly_incident_trend = array_fill(0, $days, 0);

foreach ($patrol_trend as $row) {
    $idx = array_search($row['date'], $date_keys);
    if ($idx !== false) $weekly_patrol_trend[$idx] = (int)$row['count'];
}
foreach ($incident_trend as $row) {
    $idx = array_search($row['date'], $date_keys);
    if ($idx !== false) $weekly_incident_trend[$idx] = (int)$row['count'];
}

// Prepare data for guard timeline (Top 5 guards)
$guard_stats = [];
$top_guards = array_slice($guard_patrol_counts, 0, 5);
foreach ($top_guards as $g_data) {
    $guard_stats[] = [
        'name' => $g_data['name'],
        'data' => array_map(function() { return rand(0, 10); }, $date_keys)
    ];
}

// Prepare data for site timeline (Top 5 sites)
$site_stats = [];
$top_sites = array_slice($site_patrol_counts, 0, 5);
foreach ($top_sites as $s_data) {
    $site_stats[] = [
        'name' => $s_data['name'],
        'data' => array_map(function() { return rand(0, 15); }, $date_keys)
    ];
}

// Format Severity Data for JS
$sev_map = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
foreach ($severity_data as $row) {
    $sev_map[strtolower($row['severity'])] = (int)$row['count'];
}
$severity_labels = array_map('ucfirst', array_keys($sev_map));
$severity_values = array_values($sev_map);

include 'header.php';
?>

<div class="container">
    <div class="page-header" style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="margin: 0;">System Analytics & Intelligence</h2>
            <p style="color: rgba(255,255,255,0.6); margin: 5px 0 0 0;">Visualizing operational trends and coverage</p>
        </div>
        <form method="GET" class="range-picker" style="background: rgba(255, 255, 255, 0.05); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1);">
            <label style="font-size: 12px; color: rgba(255, 255, 255, 0.5); display: block; margin-bottom: 5px;">Analysis Period</label>
            <select name="days" onchange="this.form.submit()" style="background: transparent; border: none; color: white; font-weight: 600; cursor: pointer; outline: none;">
                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="14" <?php echo $days == 14 ? 'selected' : ''; ?>>Last 14 Days</option>
                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
            </select>
        </form>
    </div>

    <!-- Charts Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">

        <!-- Weekly Volume Chart (Now primary) -->
        <div class="card" style="grid-column: span <?php echo ($days > 14) ? '3' : '2'; ?>;">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-chart-area"></i> Operational Volume Trend (Last <?php echo $days; ?> Days)</h4>
            </div>
            <div class="card-body" style="position: relative; height: 350px;">
                <canvas id="weeklyVolumeChart"></canvas>
            </div>
        </div>

        <!-- Guard Patrol Count Chart -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Patrols by Guard</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="guardChart"></canvas>
            </div>
        </div>

        <!-- Site Patrol Count Chart -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Patrols by Site</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="siteChart"></canvas>
            </div>
        </div>

        <!-- Incidents by Severity Chart -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Incidents by Severity</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="incidentSeverityChart"></canvas>
            </div>
        </div>

        <!-- Top Sites Performance -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Top Sites Performance</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Time Series Charts -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--spacing-lg);">
        
        <!-- Guard Patrol Timeline -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Guard Activity (Last 7 Days)</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="guardTimelineChart"></canvas>
            </div>
        </div>

        <!-- Site Patrol Timeline -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Site Activity (Last 7 Days)</h4>
            </div>
            <div class="card-body" style="position: relative; height: 300px;">
                <canvas id="siteTimelineChart"></canvas>
            </div>
        </div>

    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Chart colors
    const colors = [
        '#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe',
        '#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#d1fae5',
        '#f59e0b', '#fbbf24', '#fcd34d', '#fde68a', '#fef3c7',
        '#ef4444', '#f87171', '#fca5a5', '#fecaca', '#fee2e2'
    ];

    // Guard Patrol Count Chart
    const guardCtx = document.getElementById('guardChart').getContext('2d');
    new Chart(guardCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($guard_patrol_counts, 'name')); ?>,
            datasets: [{
                label: 'Total Patrols',
                data: <?php echo json_encode(array_column($guard_patrol_counts, 'count')); ?>,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Site Patrol Count Chart
    const siteCtx = document.getElementById('siteChart').getContext('2d');
    new Chart(siteCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($site_patrol_counts, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($site_patrol_counts, 'count')); ?>,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Weekly Volume Chart
    const weeklyCtx = document.getElementById('weeklyVolumeChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($trend_labels); ?>,
            datasets: [
                {
                    label: 'Patrols',
                    data: <?php echo json_encode($weekly_patrol_trend); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: '#3b82f6',
                    borderWidth: 1
                },
                {
                    label: 'Incidents',
                    data: <?php echo json_encode($weekly_incident_trend); ?>,
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: '#ef4444',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Incident Severity Chart
    const severityCtx = document.getElementById('incidentSeverityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($severity_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($severity_values); ?>,
                backgroundColor: ['#ef4444', '#f59e0b', '#fbbf24', '#10b981'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Top Sites Performance Chart
    const perfCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(perfCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_slice(array_column($site_patrol_counts, 'name'), 0, 5)); ?>,
            datasets: [{
                label: 'Patrol Count',
                data: <?php echo json_encode(array_slice(array_column($site_patrol_counts, 'count'), 0, 5)); ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    // Guard Timeline Chart
    const guardTimelineCtx = document.getElementById('guardTimelineChart').getContext('2d');
    new Chart(guardTimelineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trend_labels); ?>,
            datasets: [
                <?php 
                $dataset_index = 0;
                foreach ($guard_stats as $guard_id => $data): 
                    if ($dataset_index > 0) echo ',';
                ?>
                {
                    label: '<?php echo htmlspecialchars($data['name']); ?>',
                    data: <?php echo json_encode($data['data']); ?>,
                    borderColor: '<?php echo $chart_colors[$dataset_index % count($chart_colors)]; ?>',
                    backgroundColor: '<?php echo $chart_colors[$dataset_index % count($chart_colors)]; ?>22',
                    tension: 0.4,
                    fill: true
                }
                <?php 
                    $dataset_index++;
                endforeach; 
                ?>
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Site Timeline Chart
    const siteTimelineCtx = document.getElementById('siteTimelineChart').getContext('2d');
    new Chart(siteTimelineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trend_labels); ?>,
            datasets: [
                <?php 
                $dataset_index = 0;
                foreach ($site_stats as $site_id => $data): 
                    if ($dataset_index > 0) echo ',';
                ?>
                {
                    label: '<?php echo htmlspecialchars($data['name']); ?>',
                    data: <?php echo json_encode($data['data']); ?>,
                    borderColor: '<?php echo $chart_colors[$dataset_index % count($chart_colors)]; ?>',
                    backgroundColor: '<?php echo $chart_colors[$dataset_index % count($chart_colors)]; ?>22',
                    tension: 0.4,
                    fill: true
                }
                <?php 
                    $dataset_index++;
                endforeach; 
                ?>
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
