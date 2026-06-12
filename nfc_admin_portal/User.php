<?php
/**
 * User / Admin Model Class
 */

class User {
    private $db;
    private $table = 'admin_users';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT id, username, email, role, is_active, last_login, created_at, updated_at 
                FROM {$this->table} ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }

    public function getById($id) {
        $sql = "SELECT id, username, email, role, is_active, last_login, created_at, updated_at 
                FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getByEmail($email) {
        $sql = "SELECT id, username, email, role, is_active, last_login, created_at, updated_at 
                FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (username, email, password_hash, role, is_active)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $is_active = $data['is_active'] ?? true;
        $role = $data['role'] ?? 'user';
        
        $stmt->bind_param(
            "ssssi",
            $data['username'],
            $data['email'],
            $password_hash,
            $role,
            $is_active
        );
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }

    public function updateRole($id, $role) {
        if (!in_array($role, ['admin', 'user'])) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET role = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $role, $id);
        
        return $stmt->execute();
    }

    public function updateStatus($id, $is_active) {
        $sql = "UPDATE {$this->table} SET is_active = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $is_active, $id);
        
        return $stmt->execute();
    }

    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function changePassword($id, $new_password) {
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        $sql = "UPDATE {$this->table} SET password_hash = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $password_hash, $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAdmins() {
        $sql = "SELECT id, username, email, role, is_active, last_login, created_at, updated_at 
                FROM {$this->table} WHERE role = 'admin' ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        
        $admins = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $admins[] = $row;
            }
        }
        
        return $admins;
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getCountByRole($role) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE role = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
