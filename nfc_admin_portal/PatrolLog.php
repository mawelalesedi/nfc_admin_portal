<?php
/**
 * Patrol Log Model Class
 */

class PatrolLog {
    private $db;
    private $table = 'patrol_logs';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $sql = "SELECT pl.*, g.name as guard_name, s.name as site_name, nt.label as tag_label, nt.description as tag_description
                FROM {$this->table} pl
                LEFT JOIN guards g ON pl.guard_id = g.id
                LEFT JOIN sites s ON pl.site_id = s.id
                LEFT JOIN nfc_tags nt ON pl.nfc_tag_id = nt.id
                ORDER BY pl.scanned_at DESC";
        $result = $this->db->query($sql);
        
        $logs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }
        
        return $logs;
    }

    public function getById($id) {
        $sql = "SELECT pl.*, g.name as guard_name, s.name as site_name, nt.label as tag_label, nt.description as tag_description
                FROM {$this->table} pl
                LEFT JOIN guards g ON pl.guard_id = g.id
                LEFT JOIN sites s ON pl.site_id = s.id
                LEFT JOIN nfc_tags nt ON pl.nfc_tag_id = nt.id
                WHERE pl.id = ?";
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
        $sql = "INSERT INTO {$this->table} (guard_id, nfc_tag_id, site_id, scanned_at, notes)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $scanned_at = $data['scanned_at'] ?? date('Y-m-d H:i:s');
        
        $stmt->bind_param(
            "iiiss",
            $data['guard_id'],
            $data['nfc_tag_id'],
            $data['site_id'],
            $scanned_at,
            $data['notes']
        );
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }

    public function getByGuard($guard_id, $limit = 50) {
        $sql = "SELECT pl.*, g.name as guard_name, s.name as site_name, nt.label as tag_label, nt.description as tag_description
                FROM {$this->table} pl
                LEFT JOIN guards g ON pl.guard_id = g.id
                LEFT JOIN sites s ON pl.site_id = s.id
                LEFT JOIN nfc_tags nt ON pl.nfc_tag_id = nt.id
                WHERE pl.guard_id = ?
                ORDER BY pl.scanned_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $guard_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        return $logs;
    }

    public function getBySite($site_id, $limit = 50) {
        $sql = "SELECT pl.*, g.name as guard_name, s.name as site_name, nt.label as tag_label, nt.description as tag_description
                FROM {$this->table} pl
                LEFT JOIN guards g ON pl.guard_id = g.id
                LEFT JOIN sites s ON pl.site_id = s.id
                LEFT JOIN nfc_tags nt ON pl.nfc_tag_id = nt.id
                WHERE pl.site_id = ?
                ORDER BY pl.scanned_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $site_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        return $logs;
    }

    public function getToday() {
        $today = date('Y-m-d');
        $sql = "SELECT pl.*, g.name as guard_name, s.name as site_name, nt.label as tag_label, nt.description as tag_description
                FROM {$this->table} pl
                LEFT JOIN guards g ON pl.guard_id = g.id
                LEFT JOIN sites s ON pl.site_id = s.id
                LEFT JOIN nfc_tags nt ON pl.nfc_tag_id = nt.id
                WHERE DATE(pl.scanned_at) = ?
                ORDER BY pl.scanned_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        return $logs;
    }

    public function getTodayCount() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(scanned_at) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getGuardPatrolStats($guard_id, $days = 7) {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $sql = "SELECT DATE(scanned_at) as date, COUNT(*) as patrol_count
                FROM {$this->table}
                WHERE guard_id = ? AND DATE(scanned_at) >= ?
                GROUP BY DATE(scanned_at)
                ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $guard_id, $start_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    public function getSitePatrolStats($site_id, $days = 7) {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $sql = "SELECT DATE(scanned_at) as date, COUNT(*) as patrol_count
                FROM {$this->table}
                WHERE site_id = ? AND DATE(scanned_at) >= ?
                GROUP BY DATE(scanned_at)
                ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $site_id, $start_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getCountsByGuard() {
        $sql = "SELECT g.name, COUNT(pl.id) as count 
                FROM guards g 
                LEFT JOIN {$this->table} pl ON g.id = pl.guard_id 
                GROUP BY g.id, g.name 
                ORDER BY count DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getCountsBySite() {
        $sql = "SELECT s.name, COUNT(pl.id) as count 
                FROM sites s 
                LEFT JOIN {$this->table} pl ON s.id = pl.site_id 
                GROUP BY s.id, s.name 
                ORDER BY count DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getTrendData($days = 7) {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        $sql = "SELECT DATE(scanned_at) as date, COUNT(*) as count 
                FROM {$this->table} 
                WHERE scanned_at >= ? 
                GROUP BY DATE(scanned_at) 
                ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $start_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
