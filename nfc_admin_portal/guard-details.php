<?php
/**
 * Guard Details / Profile Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Database.php';
require_once 'Guard.php';
require_once 'PatrolLog.php';
require_once 'Incident.php';
require_once 'Site.php';

$db = new Database();
$guardModel = new Guard($db);
$patrolModel = new PatrolLog($db);
$incidentModel = new Incident($db);
$siteModel = new Site($db);

$guard_id = $_GET['id'] ?? 0;
$guard = $guardModel->getById($guard_id);

if (!$guard) {
    header('Location: guards.php');
    exit;
}

$page_title = 'Guard Profile: ' . htmlspecialchars($guard['name']);
include 'header.php';

// Fetch related activity
$recent_patrols = $patrolModel->getByGuard($guard_id, 10);
$all_incidents = $incidentModel->getAll();
$guard_incidents = array_filter($all_incidents, function($i) use ($guard_id) {
    return $i['guard_id'] == $guard_id;
});
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
            <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; background: var(--color-accent);">
                <?php echo strtoupper(substr($guard['name'], 0, 1)); ?>
            </div>
            <div>
                <h2 style="margin: 0;"><?php echo htmlspecialchars($guard['name']); ?></h2>
                <p style="margin: 0; color: var(--color-text-secondary);">Guard Profile</p>
            </div>
        </div>
        <a href="guards.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>

    <div style="display: grid; grid-template-columns: 350px 1fr; gap: var(--spacing-lg); align-items: start;">
        
        <!-- Left Column: Personal Info -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">General Information</h4>
            </div>
            <div class="card-body">
                <div style="margin-bottom: var(--spacing-md);">
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-accent); font-weight: 700;">Status</label>
                    <div style="margin-top: 4px;">
                        <span class="badge badge-<?php echo ($guard['status'] === 'active') ? 'success' : 'inactive'; ?>">
                            <?php echo ucfirst($guard['status']); ?>
                        </span>
                    </div>
                </div>
                <div style="margin-bottom: var(--spacing-md);">
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-accent); font-weight: 700;">Contact Details</label>
                    <p style="margin: 4px 0 0 0;"><i class="fas fa-phone fa-fw"></i> <?php echo htmlspecialchars($guard['phone'] ?? 'N/A'); ?></p>
                    <p style="margin: 4px 0 0 0;"><i class="fas fa-envelope fa-fw"></i> <?php echo htmlspecialchars($guard['email'] ?? 'N/A'); ?></p>
                </div>
                <div style="margin-bottom: var(--spacing-md);">
                    <label style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-accent); font-weight: 700;">Notes</label>
                    <p style="margin: 4px 0 0 0; font-style: italic; opacity: 0.8;"><?php echo nl2br(htmlspecialchars($guard['notes'] ?? 'No notes provided.')); ?></p>
                </div>
            </div>
        </div>

        <!-- Right Column: Tabs/Activity -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
            
            <!-- Recent Patrols Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-history nav-icon-logs"></i> Recent Patrol Activity</h4>
                </div>
                <div class="card-body">
                    <?php if (count($recent_patrols) > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Checkpoint</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_patrols as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['site_name']); ?></td>
                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($p['tag_label']); ?></span></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($p['scanned_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No recent patrol activity found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Incident History Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-exclamation-triangle nav-icon-incidents"></i> Reported Incidents</h4>
                </div>
                <div class="card-body">
                    <?php if (count($guard_incidents) > 0): ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($guard_incidents as $inc): ?>
                                <li style="padding: var(--spacing-md); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="display: block;"><?php echo htmlspecialchars($inc['title']); ?></strong>
                                        <small style="opacity: 0.7;"><?php echo date('M d, Y', strtotime($inc['reported_at'])); ?> • <?php echo htmlspecialchars($inc['site_name'] ?? 'N/A'); ?></small>
                                    </div>
                                    <span class="badge badge-<?php echo $inc['severity']; ?>"><?php echo ucfirst($inc['severity']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No incidents reported by this guard.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>