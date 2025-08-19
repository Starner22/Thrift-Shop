<?php
class ProductQueryBuilder {
    protected $db;
    protected $query;
    protected $params = [];

    public function __construct($db) {
        $this->db = $db;
        $this->query = "SELECT p.*, u.name as seller_name, c.name as category_name
                        FROM product p 
                        JOIN user u ON p.sellerID = u.userID 
                        LEFT JOIN categories c ON p.categoryID = c.categoryID
                        WHERE p.status = 'approved'";
    }

    public function search(string $term) {
        if (!empty($term)) {
            $this->query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $this->params[] = "%{$term}%";
            $this->params[] = "%{$term}%";
        }
        return $this; 
    }

    public function category(int $category_id) {
        if ($category_id > 0) {
            $this->query .= " AND p.categoryID = ?";
            $this->params[] = $category_id;
        }
        return $this;
    }

    public function priceRange(string $range) {
        if (!empty($range)) {
            switch ($range) {
                case 'under_50': $this->query .= " AND p.price < 50"; break;
                case '50_100': $this->query .= " AND p.price BETWEEN 50 AND 100"; break;
                case '100_500': $this->query .= " AND p.price BETWEEN 100 AND 500"; break;
                case 'over_500': $this->query .= " AND p.price > 500"; break;
            }
        }
        return $this;
    }

    public function fetchAll() {
        $this->query .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}