<?php
/**
 * Guards Management Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Guard.php';
require_once 'Site.php';

$page_title = 'Guards';

// Initialize database and models
$db = new Database();
$guard = new Guard($db);
$site = new Site($db);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token. Please try again.');
        } elseif (isset($_POST['action'])) {
            if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'assigned_site_ids' => isset($_POST['assigned_sites']) ? $_POST['assigned_sites'] : [],
                    'status' => $_POST['status'] ?? 'active',
                    'notes' => $_POST['notes'] ?? ''
                ];

                if ($_POST['action'] === 'create') {
                    if ($guard->create($data)) {
                        $message = 'Guard created successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error creating guard.');
                    }
                } else {
                    $guard_id = $_POST['guard_id'] ?? 0;
                    if ($guard->update($guard_id, $data)) {
                        $message = 'Guard updated successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error updating guard records.');
                    }
                }
            } elseif ($_POST['action'] === 'deactivate') {
                $guard_id = $_POST['guard_id'] ?? 0;
                if ($guard->deactivate($guard_id)) {
                    $message = 'Guard deactivated successfully!';
                    $message_type = 'success';
                }
            } elseif ($_POST['action'] === 'delete') {
                $guard_id = $_POST['guard_id'] ?? 0;
                if ($guard->delete($guard_id)) {
                    $message = 'Guard deleted successfully!';
                    $message_type = 'success';
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}
 
// Get all guards and sites
$all_guards = $guard->getAll();
$all_sites = $site->getAll();

// Create a site name lookup map for the table
$site_map = [];
foreach ($all_sites as $s) {
    $site_map[$s['id']] = $s['name'];
}

include 'header.php';
?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .summary-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 20px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .summary-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .summary-icon.blue { background: rgba(96, 165, 250, 0.2); color: #60a5fa; }
    .summary-icon.green { background: rgba(52, 211, 153, 0.2); color: #34d399; }
    .summary-label { font-size: 0.85rem; color: rgba(255,255,255,0.6); }
    .summary-value { font-size: 1.5rem; font-weight: 700; color: #fff; }
    .personnel-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--color-accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .site-pill {
        display: inline-block;
        padding: 2px 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        font-size: 0.75rem;
        margin-right: 4px;
        margin-bottom: 4px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .op-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
        text-decoration: none;
        cursor: pointer;
    }
    .op-btn:hover {
        background: var(--color-accent);
        border-color: var(--color-accent);
        transform: translateY(-2px);
        color: white;
    }
    .op-btn.danger:hover { background: #ef4444; border-color: #ef4444; }
    .op-btn.info:hover { background: #0ea5e9; border-color: #0ea5e9; }
    .op-btn.warning:hover { background: #f59e0b; border-color: #f59e0b; }
</style>

<div class="container">
    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: var(--spacing-lg);">
            <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    <?php endif; ?>

    <!-- Header with Add Button -->
    <div class="page-header">
        <h2 style="margin: 0;">Guard Force Management</h2>
        <div style="display: flex; gap: 12px;">
            <button class="btn btn-primary" onclick="openAddGuardModal()">
                <i class="fas fa-plus-circle"></i> Register New Guard
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-summary">
        <div class="summary-card">
            <div class="summary-icon blue"><i class="fas fa-user-shield"></i></div>
            <div>
                <div class="summary-label">Total Registered</div>
                <div class="summary-value"><?php echo count($all_guards); ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="summary-label">Active Personnel</div>
                <div class="summary-value"><?php echo count(array_filter($all_guards, function($g) { return $g['status'] === 'active'; })); ?></div>
            </div>
        </div>
    </div>

    <!-- Guards Table -->
    <div class="card">
        <?php if (count($all_guards) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Personnel</th>
                            <th><i class="fas fa-phone"></i> Contact</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-map-marker-alt"></i> Coverage</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th style="text-align: right;"><i class="fas fa-cog"></i> Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_guards as $g): ?>
                            <tr>
                                <td>
                                    <div class="personnel-cell">
                                        <div class="avatar-sm"><?php echo strtoupper(substr($g['name'] ?? 'G', 0, 1)); ?></div>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($g['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($g['phone'] ?? 'N/A'); ?></td>
                                <td style="font-size: 0.85rem; opacity: 0.8;"><?php echo htmlspecialchars($g['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <div style="max-width: 250px; display: flex; flex-wrap: wrap;">
                                        <?php 
                                        $site_ids = $g['assigned_site_ids'] ?? [];
                                        if (is_string($site_ids)) { $site_ids = json_decode($site_ids, true) ?? []; }
                                        if (empty($site_ids)) echo '<span class="text-muted" style="font-size: 0.75rem;">No sites assigned</span>';
                                        foreach ($site_ids as $sid) {
                                            if (isset($site_map[$sid])) {
                                                echo '<span class="site-pill">' . htmlspecialchars($site_map[$sid]) . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($g['status'] === 'active') ? 'success' : 'inactive'; ?>">
                                        <?php echo ucfirst($g['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <a href="guard-details.php?id=<?php echo $g['id']; ?>" class="op-btn info" title="View Profile">
                                            <i class="fas fa-id-badge"></i>
                                        </a>
                                        <button class="op-btn" onclick='editGuard(<?php echo json_encode($g); ?>)' title="Modify Records">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($g['status'] === 'active'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Suspend this guard profile?');">
                                                <input type="hidden" name="action" value="deactivate">
                                                <input type="hidden" name="guard_id" value="<?php echo $g['id']; ?>">
                                                <button type="submit" class="op-btn warning" title="Suspend">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>No guards registered yet. <a href="#" onclick="openAddGuardModal(); return false;">Add one now</a>.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Add/Edit Guard Modal -->
<div id="guardModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title" id="modalTitle">Add New Guard</h3>
            <button type="button" onclick="closeGuardModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <form method="POST" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="guard_id" id="guardId" value="">

            <div class="form-group">
                <label for="name">Guard Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="assigned_sites">Assigned Sites</label>
                <select id="assigned_sites" name="assigned_sites[]" multiple style="height: 120px;">
                    <?php foreach ($all_sites as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="on_leave">On Leave</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"></textarea>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Guard
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeGuardModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddGuardModal() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('guardId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Guard';
    document.getElementById('name').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('notes').value = '';
    document.getElementById('assigned_sites').selectedIndex = -1;
    document.getElementById('guardModal').style.display = 'flex';
}

function closeGuardModal() {
    document.getElementById('guardModal').style.display = 'none';
}

function editGuard(guard) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('guardId').value = guard.id;
    document.getElementById('modalTitle').textContent = 'Edit Guard';
    document.getElementById('name').value = guard.name;
    document.getElementById('phone').value = guard.phone || '';
    document.getElementById('email').value = guard.email || '';
    document.getElementById('status').value = guard.status;
    document.getElementById('notes').value = guard.notes || '';
    
    // Handle multiple select for sites
    const select = document.getElementById('assigned_sites');
    const assigned = guard.assigned_site_ids || [];
    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = assigned.includes(select.options[i].value) || assigned.includes(parseInt(select.options[i].value));
    }
    
    document.getElementById('guardModal').style.display = 'flex';
}

// Close modal when clicking outside
document.getElementById('guardModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGuardModal();
    }
});
</script>

<?php include 'footer.php'; ?>
