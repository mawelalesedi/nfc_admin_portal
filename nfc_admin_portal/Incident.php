<?php
/**
 * Incident Model Class
 */

class Incident {
    private $db;
    private $table = 'incidents';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT i.*, s.name as site_name, g.name as guard_name
                FROM {$this->table} i
                LEFT JOIN sites s ON i.site_id = s.id
                LEFT JOIN guards g ON i.guard_id = g.id
                ORDER BY i.reported_at DESC";
        $result = $this->db->query($sql);

        $incidents = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $incidents[] = $row;
            }
        }

        return $incidents;
    }

    public function getById($id) {
        $sql = "SELECT i.*, s.name as site_name, g.name as guard_name
                FROM {$this->table} i
                LEFT JOIN sites s ON i.site_id = s.id
                LEFT JOIN guards g ON i.guard_id = g.id
                WHERE i.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, description, severity, status, site_id, guard_id, location, latitude, longitude, reported_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $site_id = !empty($data['site_id']) ? $data['site_id'] : null;
        $guard_id = !empty($data['guard_id']) ? $data['guard_id'] : null;
        $latitude = isset($data['latitude']) && $data['latitude'] !== '' ? $data['latitude'] : null;
        $longitude = isset($data['longitude']) && $data['longitude'] !== '' ? $data['longitude'] : null;
        $reported_at = date('Y-m-d H:i:s');

        $stmt->bind_param(
            "ssssiisdds",
            $data['title'],
            $data['description'],
            $data['severity'],
            $data['status'],
            $site_id,
            $guard_id,
            $data['location'],
            $latitude,
            $longitude,
            $reported_at
        );

        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }

        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET title = ?, description = ?, severity = ?, status = ?, site_id = ?, guard_id = ?, location = ?, latitude = ?, longitude = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        $site_id = !empty($data['site_id']) ? $data['site_id'] : null;
        $guard_id = !empty($data['guard_id']) ? $data['guard_id'] : null;
        $latitude = isset($data['latitude']) && $data['latitude'] !== '' ? $data['latitude'] : null;
        $longitude = isset($data['longitude']) && $data['longitude'] !== '' ? $data['longitude'] : null;

        $stmt->bind_param(
            "ssssiisddi",
            $data['title'],
            $data['description'],
            $data['severity'],
            $data['status'],
            $site_id,
            $guard_id,
            $data['location'],
            $latitude,
            $longitude,
            $id
        );

        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getCountByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $row = $result->fetch_assoc()) ? $row['count'] : 0;
    }

    public function getTodayCount() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(reported_at) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $row = $result->fetch_assoc()) ? $row['count'] : 0;
    }

    public function getSeverityDistribution() {
        $sql = "SELECT severity, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY severity";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getTrendData($days = 7) {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $sql = "SELECT DATE(reported_at) as date, COUNT(*) as count 
                FROM {$this->table} 
                WHERE reported_at >= ? 
                GROUP BY DATE(reported_at) 
                ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $start_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
