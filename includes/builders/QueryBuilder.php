<?php
class SQLQuery {
    public $query = "";
    public $params = [];

    public function getQuery(): string {
        return $this->query;
    }

    public function getParams(): array {
        return $this->params;
    }
}

interface QueryBuilder {
    public function reset(): void;
    public function setBaseQuery(): void;
    public function addSearch(string $term): void;
    public function addCategory(int $category_id): void;
    public function addPriceRange(string $range): void;
    public function getResult(): SQLQuery;
}

class ProductQueryBuilder implements QueryBuilder {
    private $query;

    public function __construct() {
        $this->reset();
    }

    public function reset(): void {
        $this->query = new SQLQuery();
    }

    public function setBaseQuery(): void {
        $this->query->query = "SELECT p.*, u.name as seller_name, c.name as category_name
                               FROM product p
                               JOIN user u ON p.sellerID = u.userID
                               LEFT JOIN categories c ON p.categoryID = c.categoryID
                               WHERE p.status = 'approved'";
    }

    public function addSearch(string $term): void {
        if (!empty($term)) {
            $this->query->query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $this->query->params[] = "%{$term}%";
            $this->query->params[] = "%{$term}%";
        }
    }

    public function addCategory(int $category_id): void {
        if ($category_id > 0) {
            $this->query->query .= " AND p.categoryID = ?";
            $this->query->params[] = $category_id;
        }
    }

    public function addPriceRange(string $range): void {
        switch ($range) {
            case 'under_50': $this->query->query .= " AND p.price < 50"; break;
            case '50_100': $this->query->query .= " AND p.price BETWEEN 50 AND 100"; break;
            case '100_500': $this->query->query .= " AND p.price BETWEEN 100 AND 500"; break;
            case 'over_500': $this->query->query .= " AND p.price > 500"; break;
        }
    }

    public function getResult(): SQLQuery {
        $this->query->query .= " ORDER BY p.created_at DESC";
        return $this->query;
    }
}



class QueryDirector {
    private $builder;

    public function __construct(QueryBuilder $builder) {
        $this->builder = $builder;
    }

    public function buildSearchQuery(string $term, int $category, string $priceRange): SQLQuery {
        $this->builder->reset();
        $this->builder->setBaseQuery();
        $this->builder->addSearch($term);
        $this->builder->addCategory($category);
        $this->builder->addPriceRange($priceRange);
        return $this->builder->getResult();
    }
}