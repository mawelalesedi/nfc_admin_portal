<?php
/**
 * NFC Tag Model Class
 */

class NfcTag {
    private $db;
    private $table = 'nfc_tags';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT nt.*, s.name as site_name FROM {$this->table} nt 
                LEFT JOIN sites s ON nt.site_id = s.id 
                ORDER BY nt.label ASC";
        $result = $this->db->query($sql);
        
        $tags = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tags[] = $row;
            }
        }
        
        return $tags;
    }

    public function getById($id) {
        $sql = "SELECT nt.*, s.name as site_name FROM {$this->table} nt 
                LEFT JOIN sites s ON nt.site_id = s.id 
                WHERE nt.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getByTagUid($tag_uid) {
        $sql = "SELECT nt.*, s.name as site_name FROM {$this->table} nt 
                LEFT JOIN sites s ON nt.site_id = s.id 
                WHERE nt.tag_uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $tag_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (tag_uid, label, site_id, latitude, longitude, description, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $is_active = $data['is_active'] ?? true;
        
        $stmt->bind_param(
            "ssiddsi",
            $data['tag_uid'],
            $data['label'],
            $data['site_id'],
            $data['latitude'],
            $data['longitude'],
            $data['description'],
            $is_active
        );
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET tag_uid = ?, label = ?, site_id = ?, 
                latitude = ?, longitude = ?, description = ?, is_active = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        $is_active = $data['is_active'] ?? true;
        
        $stmt->bind_param(
            "ssiddsii",
            $data['tag_uid'],
            $data['label'],
            $data['site_id'],
            $data['latitude'],
            $data['longitude'],
            $data['description'],
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

    public function getActiveTags() {
        $sql = "SELECT nt.*, s.name as site_name FROM {$this->table} nt 
                LEFT JOIN sites s ON nt.site_id = s.id 
                WHERE nt.is_active = TRUE ORDER BY nt.label ASC";
        $result = $this->db->query($sql);
        
        $tags = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tags[] = $row;
            }
        }
        
        return $tags;
    }

    public function getTagsBySite($site_id) {
        $sql = "SELECT * FROM {$this->table} WHERE site_id = ? AND is_active = TRUE ORDER BY label ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $site_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        
        return $tags;
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
