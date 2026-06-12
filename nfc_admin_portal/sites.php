<?php
/**
 * Sites Management Page
 */
require_once 'auth.php';
requireLogin();

require_once 'Database.php';
require_once 'Site.php';

$page_title = 'Sites';

// Initialize database and models
$db = new Database();
$site = new Site($db);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token.');
        } elseif (isset($_POST['action'])) {
            if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'latitude' => $_POST['latitude'] ?? 0,
                    'longitude' => $_POST['longitude'] ?? 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                if ($_POST['action'] === 'create') {
                    if ($site->create($data)) {
                        $message = 'Site created successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error creating site.');
                    }
                } else {
                    $site_id = $_POST['site_id'] ?? 0;
                    if ($site->update($site_id, $data)) {
                        $message = 'Site updated successfully!';
                        $message_type = 'success';
                    } else {
                        throw new Exception('Error updating site.');
                    }
                }
            } elseif ($_POST['action'] === 'delete') {
                $site_id = $_POST['site_id'] ?? 0;
                if ($site->delete($site_id)) {
                    $message = 'Site deleted successfully!';
                    $message_type = 'success';
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// Get all sites
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
        <h2>Sites Management</h2>
        <button class="btn btn-primary" onclick="openAddSiteModal()">
            <i class="fas fa-plus"></i> Add New Site
        </button>
    </div>

    <!-- Sites Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-lg);">
        <?php foreach ($all_sites as $s): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo htmlspecialchars($s['name']); ?></h4>
                    <span class="badge badge-<?php echo $s['is_active'] ? 'success' : 'inactive'; ?>">
                        <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($s['address'] ?? 'N/A'); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($s['description'] ?? 'N/A'); ?></p>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-secondary" onclick='editSite(<?php echo json_encode($s); ?>)'>
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this site?');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="site_id" value="<?php echo $s['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($all_sites) === 0): ?>
        <div class="alert alert-info" style="margin-top: var(--spacing-lg);">
            <i class="fas fa-info-circle"></i>
            <span>No sites registered yet. <a href="#" onclick="openAddSiteModal(); return false;">Add one now</a>.</span>
        </div>
    <?php endif; ?>

</div>

<!-- Add/Edit Site Modal -->
<div id="siteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title" id="modalTitle">Add New Site</h3>
            <button type="button" onclick="closeSiteModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <form method="POST" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="site_id" id="siteId" value="">

            <div class="form-group">
                <label for="name">Site Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active
                </label>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Site
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeSiteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddSiteModal() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('siteId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Site';
    document.getElementById('name').value = '';
    document.getElementById('address').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('siteModal').style.display = 'flex';
}

function closeSiteModal() {
    document.getElementById('siteModal').style.display = 'none';
}

function editSite(site) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('siteId').value = site.id;
    document.getElementById('modalTitle').textContent = 'Edit Site';
    document.getElementById('name').value = site.name;
    document.getElementById('address').value = site.address || '';
    document.getElementById('description').value = site.description || '';
    document.getElementById('is_active').checked = parseInt(site.is_active) === 1;
    
    document.getElementById('siteModal').style.display = 'flex';
}

// Close modal when clicking outside
document.getElementById('siteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSiteModal();
    }
});
</script>

<?php include 'footer.php'; ?>
