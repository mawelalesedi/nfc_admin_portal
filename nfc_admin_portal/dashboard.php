<?php
/**
 * Dashboard Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Guard.php';
require_once 'Site.php';
require_once 'PatrolLog.php';
require_once 'Incident.php';

// Set page title
$page_title = 'Dashboard';

include 'header.php';
?>
<style>
    /* Demo-inspired Styles */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px; }
    .stat-tile { 
        background: rgba(255, 255, 255, 0.05); 
        backdrop-filter: blur(10px); 
        border: 1px solid rgba(255,255,255,0.1); 
        padding: 20px; 
        border-radius: 18px; 
        position: relative;
        overflow: hidden;
    }
    .stat-tile::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
    }
    .stat-tile.blue::before { background: #60a5fa; }
    .stat-tile.green::before { background: #34d399; }
    .stat-tile.red::before, .stat-tile.yellow::before { background: #facc15; }
    .stat-tile.orange::before { background: #e67e22; }
    
    .stat-ico { font-size: 24px; display: block; margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: rgba(255,255,255,0.6); font-weight: 500; }
    .stat-value { font-size: 28px; font-weight: 700; color: #fff; margin: 4px 0; }
    .stat-sub { font-size: 12px; color: rgba(255,255,255,0.5); }
    .trend-up { color: #34d399; font-weight: 600; }
    .trend-down { color: #f87171; font-weight: 600; }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-green { background: rgba(52, 211, 153, 0.2); color: #34d399; }
    .badge-blue { background: rgba(96, 165, 250, 0.2); color: #60a5fa; }
    .badge-orange { background: rgba(247, 148, 29, 0.2); color: #e67e22; }
    .badge-red { background: rgba(248, 113, 113, 0.2); color: #f87171; }

    .activity-list { list-style: none; padding: 0; margin: 0; }
    .activity-item { display: flex; gap: 12px; margin-bottom: 16px; align-items: flex-start; }
    .activity-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .activity-dot.green { background: #34d399; box-shadow: 0 0 8px rgba(52, 211, 153, 0.4); }
    .activity-dot.red { background: #f87171; box-shadow: 0 0 8px rgba(248, 113, 113, 0.4); }
    .activity-dot.blue { background: #60a5fa; box-shadow: 0 0 8px rgba(96, 165, 250, 0.4); }
    .activity-dot.orange { background: #e67e22; box-shadow: 0 0 8px rgba(230, 126, 34, 0.4); }
    .activity-text { font-size: 13px; color: rgba(255,255,255,0.9); line-height: 1.4; }
    .activity-time { font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; font-size: 12px; text-transform: uppercase; color: rgba(255,255,255,0.4); padding: 12px 8px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    td { padding: 10px 8px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #fff; word-break: break-word; }
    code { background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; font-family: monospace; }

    @media (max-width: 1200px) {
        .bottom-grid { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 768px) {
        .charts-grid { grid-template-columns: 1fr; }
    }

    .brand-accent-card {
        background: linear-gradient(135deg, #08326d 0%, #05214a 100%);
        color: white;
        border: none;
    }
    .btn-white {
        background: #e67e22;
        color: #ffffff;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        display: inline-block;
        margin-top: var(--spacing-md);
        transition: opacity 0.2s;
    }
    .btn-white:hover { opacity: 0.9; background: #d35400; }
    
    h2, p { color: #ffffff; }
</style>
<?php
// Initialize database and models
$db = new Database();
$guard = new Guard($db);
$site = new Site($db);
$patrol = new PatrolLog($db);
$incident = new Incident($db);
// Get dashboard statistics
$total_guards = $guard->getTotalCount();
$active_guards = $guard->countByStatus('active');
$total_sites = $site->getTotalCount();
$today_patrols = $patrol->getTodayCount();
$today_incidents = $incident->getTodayCount();
$open_incidents = $incident->getCountByStatus('open');
$resolved_incidents = $incident->getCountByStatus('resolved');

// Fetch specific open incidents for the table
// Tweak: Filter during the initial loop to save memory
$open_incidents_list = array_filter($incident->getAll(), function($i) { return $i['status'] == 'open'; });

// Get recent patrol logs
$recent_patrols = $patrol->getToday();

// Prepare trend data for chart
$trend_raw = $patrol->getTrendData(7);
$chart_labels = [];
$chart_values = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D, M d', strtotime($date));
    $count = 0;
    foreach ($trend_raw as $row) {
        if ($row['date'] == $date) { $count = (int)$row['count']; break; }
    }
    $chart_values[] = $count;
}
?>

<div class="container">
    <!-- STAT TILES -->
    <div class="stats-grid">
        <div class="stat-tile blue">
            <span class="stat-ico">🛡️</span>
            <div class="stat-label">Patrols Today</div>
            <div class="stat-value" id="stat-today-patrols"><?= $today_patrols ?></div>
            <div class="stat-sub">iZiGP scan events logged</div>
        </div>
        <div class="stat-tile green">
            <span class="stat-ico">👮</span>
            <div class="stat-label">Active Guards</div>
            <div class="stat-value" id="stat-active-guards"><?= $active_guards ?></div>
            <div class="stat-sub">of <?= $total_guards ?> registered</div>
        </div>
        <div class="stat-tile yellow">
            <span class="stat-ico">⚠️</span>
            <div class="stat-label">Open Incidents</div>
            <div class="stat-value" id="stat-open-incidents"><?= $open_incidents ?></div>
            <div class="stat-sub">across all active sites</div>
        </div>
        <div class="stat-tile orange">
            <span class="stat-ico">🏢</span>
            <div class="stat-label">Sites Covered</div>
            <div class="stat-value" id="stat-total-sites"><?= $total_sites ?></div>
            <div class="stat-sub">active patrol zones</div>
        </div>
    </div>

    <!-- PATROL TREND CHART -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title"><span class="ico">📈</span> Patrol Volume Trend (Last 7 Days)</div>
        <div style="height: 250px; padding: 10px; position: relative;">
            <canvas id="patrolTrendChart"></canvas>
        </div>
    </div>

    <!-- RECENT PATROLS TABLE -->
    <div class="card">
        <div class="card-title"><span class="ico">🛡️</span> Recent Patrol Logs
            <a href="patrol-tracking.php" style="margin-left:auto;font-size:13px;color:#60a5fa;text-decoration:none;font-weight:600;">View all →</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Guard</th><th>Site</th><th>Checkpoint</th><th>Scanned At</th><th>Status</th>
                </tr>
            </thead>
            <tbody id="recent-patrols-body">
            <?php foreach(array_slice($recent_patrols, 0, 6) as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['guard_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($log['site_name'] ?? 'Unknown') ?></td>
                    <td><code><?= htmlspecialchars($log['tag_label'] ?? 'Unknown') ?></code></td>
                    <td><?= date('H:i', strtotime($log['scanned_at'])) ?></td>
                    <td><span class="badge badge-green">Completed</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- BOTTOM ROW: INCIDENTS + ACTIVITY -->
    <div class="bottom-grid" style="display:grid;grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap:18px;margin-bottom:24px;">

        <!-- INCIDENTS -->
        <div class="card">
            <div class="card-title"><span class="ico">⚠️</span> Open Incidents
                <a href="incidents.php" style="margin-left:auto;font-size:13px;color:#60a5fa;text-decoration:none;font-weight:600;">View all →</a>
            </div>
            <table>
                <thead><tr><th>Site</th><th>Type</th><th>Time</th><th>Severity</th></tr></thead>
                <tbody id="open-incidents-body">
                <?php foreach(array_slice($open_incidents_list, 0, 5) as $inc): ?>
                    <tr>
                        <td><?= htmlspecialchars($inc['site_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($inc['title']) ?></td>
                        <td><?= date('H:i', strtotime($inc['reported_at'])) ?></td>
                        <td>
                            <?php
                                $severity_map = [
                                    'critical' => 'badge-red',
                                    'high' => 'badge-red',
                                    'medium' => 'badge-orange',
                                    'low' => 'badge-green'
                                ];
                                $badge = $severity_map[$inc['severity']] ?? 'badge-blue';
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($inc['severity']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ACTIVITY FEED -->
        <div class="card">
            <div class="card-title"><span class="ico">🕐</span> Live Activity Feed</div>
            <ul class="activity-list" id="activity-feed-list">
                <?php foreach(array_slice($recent_patrols, 0, 5) as $log): ?>
                <li class="activity-item">
                    <div class="activity-dot green"></div>
                    <div>
                        <div class="activity-text"><strong><?= htmlspecialchars($log['guard_name']) ?></strong> scanned checkpoint <?= htmlspecialchars($log['tag_label']) ?> at <?= htmlspecialchars($log['site_name']) ?></div>
                        <div class="activity-time"><?= date('H:i A', strtotime($log['scanned_at'])) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if(empty($recent_patrols)): ?>
                    <li class="activity-item"><div class="activity-text">Waiting for patrol scans...</div></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    /**
     * AJAX Dashboard Polling
     */
    async function fetchDashboardUpdates() {
        try {
            const response = await fetch('api-dashboard.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            // Update Stats
            document.getElementById('stat-today-patrols').textContent = data.stats.today_patrols;
            document.getElementById('stat-active-guards').textContent = data.stats.active_guards;
            document.getElementById('stat-open-incidents').textContent = data.stats.open_incidents;
            document.getElementById('stat-total-sites').textContent = data.stats.total_sites;
            
            // Update Table
            const tbody = document.getElementById('recent-patrols-body');
            if (data.recent_patrols.length > 0) {
                tbody.innerHTML = data.recent_patrols.map(log => `
                    <tr>
                        <td>${log.guard_name}</td>
                        <td>${log.site_name}</td>
                        <td><code>${log.tag_label}</code></td>
                        <td>${log.time}</td>
                        <td><span class="badge badge-green">Completed</span></td>
                    </tr>
                `).join('');
            }
            
            // Update Incidents Table
            const incidentsBody = document.getElementById('open-incidents-body');
            if (data.open_incidents.length > 0) {
                incidentsBody.innerHTML = data.open_incidents.map(inc => {
                    const severityMap = {
                        'critical': 'badge-red',
                        'high': 'badge-red',
                        'medium': 'badge-orange',
                        'low': 'badge-green'
                    };
                    const badgeClass = severityMap[inc.severity] || 'badge-blue';
                    const severityLabel = inc.severity.charAt(0).toUpperCase() + inc.severity.slice(1);
                    
                    return `<tr><td>${inc.site_name}</td><td>${inc.title}</td><td>${inc.time}</td><td><span class="badge ${badgeClass}">${severityLabel}</span></td></tr>`;
                }).join('');
            }
            
            // Update Feed
            const feedList = document.getElementById('activity-feed-list');
            if (data.activity_feed.length > 0) {
                feedList.innerHTML = data.activity_feed.map(item => `
                    <li class="activity-item">
                        <div class="activity-dot green"></div>
                        <div>
                            <div class="activity-text">${item.text}</div>
                            <div class="activity-time">${item.time}</div>
                        </div>
                    </li>
                `).join('');
            }
        } catch (error) {
            console.error('AJAX Refresh Error:', error);
        }
    }

    // Refresh every 30 seconds
    setInterval(fetchDashboardUpdates, 30000);

    // Initialize Patrol Trend Chart
    const ctx = document.getElementById('patrolTrendChart').getContext('2d');
    const patrolTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Total Patrols',
                data: <?php echo json_encode($chart_values); ?>,
                borderColor: '#60a5fa',
                backgroundColor: 'rgba(96, 165, 250, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#60a5fa',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: 'rgba(255, 255, 255, 0.6)', stepSize: 1 }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'rgba(255, 255, 255, 0.6)' }
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
