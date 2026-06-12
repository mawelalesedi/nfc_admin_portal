<?php
/**
 * Guard Model Class
 */

class Guard {
    private $db;
    private $table = 'guards';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $result = $this->db->query($sql);
        
        $guards = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['assigned_site_ids'] = json_decode($row['assigned_site_ids'], true) ?? [];
                $guards[] = $row;
            }
        }
        
        return $guards;
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $row['assigned_site_ids'] = json_decode($row['assigned_site_ids'], true) ?? [];
            return $row;
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, phone, email, assigned_site_ids, status, notes)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $assigned_sites = json_encode($data['assigned_site_ids'] ?? []);
        
        $stmt->bind_param(
            "ssssss",
            $data['name'],
            $data['phone'],
            $data['email'],
            $assigned_sites,
            $data['status'],
            $data['notes']
        );
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, phone = ?, email = ?, 
                assigned_site_ids = ?, status = ?, notes = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        $assigned_sites = json_encode($data['assigned_site_ids'] ?? []);
        
        $stmt->bind_param(
            "ssssssi",
            $data['name'],
            $data['phone'],
            $data['email'],
            $assigned_sites,
            $data['status'],
            $data['notes'],
            $id
        );
        
        return $stmt->execute();
    }

    public function deactivate($id) {
        $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getActiveGuards() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        $result = $this->db->query($sql);
        
        $guards = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['assigned_site_ids'] = json_decode($row['assigned_site_ids'], true) ?? [];
                $guards[] = $row;
            }
        }
        
        return $guards;
    }

    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
