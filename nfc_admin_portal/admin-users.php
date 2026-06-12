<?php
/**
 * Admin Users Management Page
 */
require_once 'auth.php';
requireLogin();

require_once 'User.php';

$page_title = 'Admin Users';

// Initialize database and models
$db = new Database();
$user = new User($db);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'user',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($user->create($data)) {
                $message = 'User created successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error creating user. Username or email may already exist.';
                $message_type = 'danger';
            }
        } elseif ($_POST['action'] === 'update_role') {
            $user_id = $_POST['user_id'] ?? 0;
            $role = $_POST['role'] ?? 'user';
            if ($user->updateRole($user_id, $role)) {
                $message = 'User role updated successfully!';
                $message_type = 'success';
            }
        } elseif ($_POST['action'] === 'update_status') {
            $user_id = $_POST['user_id'] ?? 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            if ($user->updateStatus($user_id, $is_active)) {
                $message = 'User status updated!';
                $message_type = 'success';
            }
        } elseif ($_POST['action'] === 'delete') {
            $user_id = $_POST['user_id'] ?? 0;
            if ($user->delete($user_id)) {
                $message = 'User deleted successfully!';
                $message_type = 'success';
            }
        }
    }
}

// Get all users
$all_users = $user->getAll();
$admin_count = $user->getCountByRole('admin');
$user_count = $user->getCountByRole('user');

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

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
        <div class="card">
            <p style="color: var(--color-text-secondary); margin: 0 0 var(--spacing-sm) 0;">Total Users</p>
            <h2 style="margin: 0; color: white;"><?php echo count($all_users); ?></h2>
        </div>
        <div class="card">
            <p style="color: var(--color-text-secondary); margin: 0 0 var(--spacing-sm) 0;">Admins</p>
            <h2 style="margin: 0; color: var(--color-success);"><?php echo $admin_count; ?></h2>
        </div>
        <div class="card">
            <p style="color: var(--color-text-secondary); margin: 0 0 var(--spacing-sm) 0;">Regular Users</p>
            <h2 style="margin: 0; color: var(--color-info);"><?php echo $user_count; ?></h2>
        </div>
    </div>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
        <h2>User Management</h2>
        <button class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>

    <!-- Users Table -->
    <div class="card">
        <?php if (count($all_users) > 0): ?>
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="role" onchange="this.form.submit()" style="padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                                            <option value="admin" <?php echo ($u['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="user" <?php echo ($u['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($u['is_active']) ? 'success' : 'inactive'; ?>">
                                        <?php echo ($u['is_active']) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?></small>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($u['created_at'])); ?></small>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
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
                <span>No users registered yet. <a href="#" onclick="openAddUserModal(); return false;">Add one now</a>.</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Add User Modal -->
<div id="userModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header">
            <h3 class="card-title">Add New User</h3>
            <button type="button" onclick="closeUserModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        <form method="POST" class="card-body">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active
                </label>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create User
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('role').value = 'user';
    document.getElementById('is_active').checked = true;
    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUserModal();
    }
});
</script>

<?php include 'footer.php'; ?>
