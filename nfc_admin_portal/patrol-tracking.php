<?php
/**
 * Patrol Tracking Page
 */
require_once 'auth.php';
requireLogin();

require_once 'PatrolLog.php';
require_once 'Guard.php';
require_once 'Site.php';

$page_title = 'Patrol Tracking';

// Initialize database and models
$db = new Database();
$patrol = new PatrolLog($db);
$guard = new Guard($db);
$site = new Site($db);

// Get filter parameters
$filter_guard = $_GET['guard_id'] ?? '';
$filter_site = $_GET['site_id'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Get all patrols (with optional filters)
$all_patrols = $patrol->getAll();
$all_guards = $guard->getAll();
$all_sites = $site->getAll();

// Apply filters
if ($filter_guard || $filter_site || $filter_date) {
    $all_patrols = array_filter($all_patrols, function($p) use ($filter_guard, $filter_site, $filter_date) {
        if ($filter_guard && $p['guard_id'] != $filter_guard) return false;
        if ($filter_site && $p['site_id'] != $filter_site) return false;
        if ($filter_date && date('Y-m-d', strtotime($p['scanned_at'])) != $filter_date) return false;
        return true;
    });
}

include 'header.php';
?>

<div class="container">
    <!-- Filters -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-header">
            <h4 class="card-title">Filter Patrols</h4>
        </div>
        <form method="GET" class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md);">
            <div class="form-group">
                <label for="guard_id">Guard</label>
                <select id="guard_id" name="guard_id">
                    <option value="">-- All Guards --</option>
                    <?php foreach ($all_guards as $g): ?>
                        <option value="<?php echo $g['id']; ?>" <?php echo ($filter_guard == $g['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="site_id">Site</label>
                <select id="site_id" name="site_id">
                    <option value="">-- All Sites --</option>
                    <?php foreach ($all_sites as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($filter_site == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>

            <div class="form-group" style="display: flex; align-items: flex-end; gap: var(--spacing-md);">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="<?php echo APP_URL; ?>/patrol-tracking.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Patrol Logs Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Patrol Logs</h3>
            <span style="color: var(--color-text-secondary);"><?php echo count($all_patrols); ?> records</span>
        </div>
        
        <?php if (count($all_patrols) > 0): ?>
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Guard Name & Surname</th>
                            <th>Site</th>
                            <th>Checkpoint</th>
                            <th>Description</th>
                            <th>Scanned At</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_patrols as $log): ?>
                            <tr>
                                <td style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($log['guard_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($log['site_name'] ?? 'Unknown'); ?></td>
                                <td><span class="badge" style="background: rgba(230, 126, 34, 0.2); color: var(--color-accent);"><?php echo htmlspecialchars($log['tag_label'] ?? 'Unknown'); ?></span></td>
                                <td style="color: rgba(255,255,255,0.7); font-size: 0.85rem;"><?php echo htmlspecialchars($log['tag_description'] ?? 'Main gate checkpoint'); ?></td>
                                <td>
                                    <small>
                                        <?php echo date('M d, Y H:i:s', strtotime($log['scanned_at'])); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($log['notes'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" style="margin: var(--spacing-lg);">
                <i class="fas fa-info-circle"></i>
                <span>No patrol logs found matching the selected filters.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>
