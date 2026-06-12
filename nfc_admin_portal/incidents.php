<?php
/**
 * Incidents Management Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Incident.php';
require_once 'Guard.php';
require_once 'Site.php';

$page_title = 'Incidents';

// Initialize database and models
$db = new Database();
$incident = new Incident($db);
$guard = new Guard($db);
$site = new Site($db);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token. Please refresh and try again.');
        } elseif (isset($_POST['action'])) {
            if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'severity' => $_POST['severity'] ?? 'medium',
                    'status' => $_POST['status'] ?? 'open',
                    'site_id' => $_POST['site_id'] ?? null,
                    'guard_id' => $_POST['guard_id'] ?? null,
                    'location' => $_POST['location'] ?? '',
                    'latitude' => $_POST['latitude'] ?? null,
                    'longitude' => $_POST['longitude'] ?? null
                ];

                if ($_POST['action'] === 'create') {
                    if ($incident->create($data)) {
                        $message = 'Incident created successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error creating incident report.');
                    }
                } else {
                    $incident_id = $_POST['incident_id'] ?? 0;
                    if ($incident->update($incident_id, $data)) {
                        $message = 'Incident updated successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error updating incident details.');
                    }
                }
            } elseif ($_POST['action'] === 'update_status') {
                $incident_id = $_POST['incident_id'] ?? 0;
                $status = $_POST['status'] ?? 'open';
                if ($incident->updateStatus($incident_id, $status)) {
                    $message = 'Incident status updated!';
                    $message_type = 'success';
                }
            } elseif ($_POST['action'] === 'delete') {
                $incident_id = $_POST['incident_id'] ?? 0;
                if ($incident->delete($incident_id)) {
                    $message = 'Incident deleted successfully!';
                    $message_type = 'success';
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}
 
// Get all incidents and related data
$all_incidents = $incident->getAll();
$all_guards = $guard->getAll();
$all_sites = $site->getAll();

include 'header.php';
?>

<div class="container">
    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: var(--spacing-lg);">
            <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    <?php endif; ?>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
        <h2>Incident Management</h2>
        <button class="btn btn-primary" onclick="openAddIncidentModal()">
            <i class="fas fa-plus"></i> Report Incident
        </button>
    </div>

    <!-- Incidents Table -->
    <div class="card">
        <?php if (count($all_incidents) > 0): ?>
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Site</th>
                            <th>Guard</th>
                            <th>Reported</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_incidents as $inc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inc['title']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $inc['severity']; ?>">
                                        <?php echo ucfirst($inc['severity']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="incident_id" value="<?php echo $inc['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                                            <option value="open" <?php echo ($inc['status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                                            <option value="resolved" <?php echo ($inc['status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($inc['site_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($inc['guard_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($inc['reported_at'])); ?></small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick='editIncident(<?php echo json_encode($inc); ?>)'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this incident?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="incident_id" value="<?php echo $inc['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>No incidents reported yet. <a href="#" onclick="openAddIncidentModal(); return false;">Report one now</a>.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Add/Edit Incident Modal -->
<div id="incidentModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title" id="modalTitle">Report Incident</h3>
            <button type="button" onclick="closeIncidentModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <form method="POST" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="incident_id" id="incidentId" value="">

            <div class="form-group">
                <label for="title">Incident Title *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                <div class="form-group">
                    <label for="severity">Severity *</label>
                    <select id="severity" name="severity" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="open" selected>Open</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="site_id">Site</label>
                <select id="site_id" name="site_id">
                    <option value="">-- Select Site --</option>
                    <?php foreach ($all_sites as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="guard_id">Assigned Guard</label>
                <select id="guard_id" name="guard_id">
                    <option value="">-- Select Guard --</option>
                    <?php foreach ($all_guards as $g): ?>
                        <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location">
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Incident
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeIncidentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddIncidentModal() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('incidentId').value = '';
    document.getElementById('modalTitle').textContent = 'Report Incident';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('severity').value = 'medium';
    document.getElementById('status').value = 'open';
    document.getElementById('site_id').value = '';
    document.getElementById('guard_id').value = '';
    document.getElementById('location').value = '';
    document.getElementById('incidentModal').style.display = 'flex';
}

function closeIncidentModal() {
    document.getElementById('incidentModal').style.display = 'none';
}

function editIncident(incident) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('incidentId').value = incident.id;
    document.getElementById('modalTitle').textContent = 'Edit Incident';
    document.getElementById('title').value = incident.title;
    document.getElementById('description').value = incident.description || '';
    document.getElementById('severity').value = incident.severity;
    document.getElementById('status').value = incident.status;
    document.getElementById('site_id').value = incident.site_id || '';
    document.getElementById('guard_id').value = incident.guard_id || '';
    document.getElementById('location').value = incident.location || '';
    
    document.getElementById('incidentModal').style.display = 'flex';
}

// Close modal when clicking outside
document.getElementById('incidentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeIncidentModal();
    }
});
</script>

<?php include 'footer.php'; ?>
