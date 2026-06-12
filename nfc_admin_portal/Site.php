<?php
/**
 * Site Model Class
 */

class Site {
    private $db;
    private $table = 'sites';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $result = $this->db->query($sql);
        
        $sites = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sites[] = $row;
            }
        }
        
        return $sites;
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, address, description, latitude, longitude, is_active)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $is_active = $data['is_active'] ?? true;
        
        $stmt->bind_param(
            "sssddi",
            $data['name'],
            $data['address'],
            $data['description'],
            $data['latitude'],
            $data['longitude'],
            $is_active
        );
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, address = ?, description = ?, 
                latitude = ?, longitude = ?, is_active = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        $is_active = $data['is_active'] ?? true;
        
        $stmt->bind_param(
            "sssddii",
            $data['name'],
            $data['address'],
            $data['description'],
            $data['latitude'],
            $data['longitude'],
            $is_active,
            $id
        );
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getActiveSites() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name ASC";
        $result = $this->db->query($sql);
        
        $sites = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sites[] = $row;
            }
        }
        
        return $sites;
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getSiteWithCheckpoints($id) {
        $site = $this->getById($id);
        
        if ($site) {
            // Get NFC tags for this site
            $sql = "SELECT * FROM nfc_tags WHERE site_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $site['checkpoints'] = [];
            while ($row = $result->fetch_assoc()) {
                $site['checkpoints'][] = $row;
            }
        }
        
        return $site;
    }
}
