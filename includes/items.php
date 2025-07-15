<?php
require_once 'database.php';

class Items {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public function reportItem($type, $itemName, $description, $location, $date, $imagePath, $reportedBy) {
        // Validate date is not in the future
        $today = date('Y-m-d');
        if ($date > $today) {
            throw new Exception("Date cannot be in the future");
        }
    
        if ($type === 'lost') {
            $stmt = $this->db->conn->prepare(
                "INSERT INTO Lost_Items 
                 (item_name, description, lost_location, lost_date, reported_by, image_path) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
        } else {
            $stmt = $this->db->conn->prepare(
                "INSERT INTO Found_Items 
                 (item_name, description, found_location, found_date, reported_by, image_path) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
        }
        
        return $stmt->execute([$itemName, $description, $location, $date, $reportedBy, $imagePath]);
    }

    public function getAllItems($filter = 'all') {
        if ($filter === 'lost') {
            $query = "SELECT li.lost_item_id, 
                        li.item_name, 
                        li.description, 
                        li.lost_location AS location, 
                        li.lost_date AS date, 
                        'lost' AS type, 
                        li.image_path
                    FROM lost_items li
                    LEFT JOIN claims c ON li.lost_item_id = c.lost_item_id
                    WHERE c.lost_item_id IS NULL OR c.status IN ('pending', 'rejected')";

        } elseif ($filter === 'found') {
            $query = "SELECT fi.found_item_id, 
                        fi.item_name, 
                        fi.description, 
                        fi.found_location AS location, 
                        fi.found_date AS date, 
                        'found' AS type, 
                        fi.image_path
                FROM found_items fi
                LEFT JOIN claims c ON fi.found_item_id = c.found_item_id
                WHERE c.found_item_id IS NULL OR c.status IN ('pending', 'rejected')";
     
        } else {
            $query = "SELECT 
                li.lost_item_id AS id, 
                li.item_name, 
                li.description, 
                li.lost_location AS location, 
                li.lost_date AS date, 
                'lost' AS type, 
                li.image_path
            FROM lost_items li
            LEFT JOIN claims c ON li.lost_item_id = c.lost_item_id
            WHERE c.lost_item_id IS NULL OR c.status IN ('pending', 'rejected')

            UNION ALL

            SELECT 
                fi.found_item_id AS id, 
                fi.item_name, 
                fi.description, 
                fi.found_location AS location, 
                fi.found_date AS date, 
                'found' AS type, 
                fi.image_path
            FROM found_items fi
            LEFT JOIN claims c ON fi.found_item_id = c.found_item_id
            WHERE c.found_item_id IS NULL OR c.status IN ('pending', 'rejected')";
        }
        
        $stmt = $this->db->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItemDetails($id, $type) {
        $table = ($type === 'lost') ? 'Lost_Items' : 'Found_Items';
        $idColumn = ($type === 'lost') ? 'lost_item_id' : 'found_item_id';
    
        $stmt = $this->db->conn->prepare("
            SELECT i.*, u.username 
            FROM $table i 
            JOIN users u ON i.reported_by = u.user_id 
            WHERE i.$idColumn = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function findMatchingLostItem($foundItemId, $userId) {
        // Get the found item details
        $foundItem = $this->getItemDetails($foundItemId, 'found');
        
        // Search for matching lost items reported by this user
        $stmt = $this->db->conn->prepare(
            "SELECT lost_item_id 
             FROM Lost_Items 
             WHERE item_name LIKE ? 
             AND reported_by = ?
             ORDER BY lost_date DESC
             LIMIT 1"
        );
        
        $searchTerm = '%' . $foundItem['item_name'] . '%';
        $stmt->execute([$searchTerm, $userId]);
        
        return $stmt->fetchColumn();
    }
}
?>