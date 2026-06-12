<?php
/**
 * NFC Tags Management Page
 */
require_once 'auth.php';
requireLogin();

require_once 'NfcTag.php';
require_once 'Site.php';

$page_title = 'NFC Tags';

// Initialize database and models
$db = new Database();
$nfc_tag = new NfcTag($db);
$site = new Site($db);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $message_type = 'danger';
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $data = [
                'tag_uid' => $_POST['tag_uid'] ?? '',
                'label' => $_POST['label'] ?? '',
                'site_id' => $_POST['site_id'] ?? 0,
                'latitude' => $_POST['latitude'] ?? 0,
                'longitude' => $_POST['longitude'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($_POST['action'] === 'create') {
                if ($nfc_tag->create($data)) {
                    $message = 'NFC Tag registered successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error registering NFC tag. Tag UID may already exist.';
                    $message_type = 'danger';
                }
            } else {
                $tag_id = $_POST['tag_id'] ?? 0;
                if ($nfc_tag->update($tag_id, $data)) {
                    $message = 'NFC Tag updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error updating NFC tag.';
                    $message_type = 'danger';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $tag_id = $_POST['tag_id'] ?? 0;
            if ($nfc_tag->delete($tag_id)) {
                $message = 'NFC Tag deleted successfully!';
                $message_type = 'success';
            }
        }
    }
}

// Get all NFC tags and sites
$all_tags = $nfc_tag->getAll();
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
        <h2>NFC Tags & Checkpoints</h2>
        <button class="btn btn-primary" onclick="openAddTagModal()">
            <i class="fas fa-plus"></i> Register NFC Tag
        </button>
    </div>

    <!-- NFC Tags Table -->
    <div class="card">
        <?php if (count($all_tags) > 0): ?>
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Tag UID</th>
                            <th>Site</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_tags as $tag): ?>
                            <tr>
                                <td><strong style="color: white;"><?php echo htmlspecialchars($tag['label']); ?></strong></td>
                                <td><span class="badge badge-primary" style="background-color: #e67e22;"><?php echo htmlspecialchars(substr($tag['tag_uid'], 0, 12) . '...'); ?></span></td>
                                <td><?php echo htmlspecialchars($tag['site_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $tag['is_active'] ? 'success' : 'inactive'; ?>">
                                        <?php echo $tag['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick='editTag(<?php echo json_encode($tag); ?>)'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this NFC tag?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="tag_id" value="<?php echo $tag['id']; ?>">
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
                <span>No NFC tags registered yet. <a href="#" onclick="openAddTagModal(); return false;">Register one now</a>.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Add/Edit NFC Tag Modal -->
<div id="tagModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title" id="modalTitle">Register NFC Tag</h3>
            <button type="button" onclick="closeTagModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <form method="POST" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="tag_id" id="tagId" value="">

            <div class="form-group">
                <label for="tag_uid">NFC Tag UID *</label>
                <input type="text" id="tag_uid" name="tag_uid" placeholder="e.g., 04:B1:2A:3C:5D:6E:7F" required>
            </div>

            <div class="form-group">
                <label for="label">Checkpoint Label *</label>
                <input type="text" id="label" name="label" placeholder="e.g., Main Gate, North Fence" required>
            </div>

            <div class="form-group">
                <label for="site_id">Assigned Site *</label>
                <select id="site_id" name="site_id" required>
                    <option value="">-- Select Site --</option>
                    <?php foreach ($all_sites as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Additional details about this checkpoint"></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active
                </label>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Register Tag
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeTagModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddTagModal() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('tagId').value = '';
    document.getElementById('modalTitle').textContent = 'Register NFC Tag';
    document.getElementById('tag_uid').value = '';
    document.getElementById('label').value = '';
    document.getElementById('site_id').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('tagModal').style.display = 'flex';
}

function closeTagModal() {
    document.getElementById('tagModal').style.display = 'none';
}

function editTag(tag) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('tagId').value = tag.id;
    document.getElementById('modalTitle').textContent = 'Edit NFC Tag';
    document.getElementById('tag_uid').value = tag.tag_uid;
    document.getElementById('label').value = tag.label;
    document.getElementById('site_id').value = tag.site_id;
    document.getElementById('description').value = tag.description || '';
    document.getElementById('is_active').checked = parseInt(tag.is_active) === 1;
    
    document.getElementById('tagModal').style.display = 'flex';
}

// Close modal when clicking outside
document.getElementById('tagModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTagModal();
    }
});
</script>

<?php include 'footer.php'; ?>
