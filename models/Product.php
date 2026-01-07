<?php

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($search = '', $sortBy = 'name', $sortDir = 'ASC', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $allowedSort = ['name', 'sku', 'stock', 'price'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'name';
        }
        
        $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';
        
        $where = '';
        $params = [];
        
        if (!empty($search)) {
            $where = "WHERE name LIKE ? OR sku LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam];
        }
        
        $countSql = "SELECT COUNT(*) FROM products $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        $orderClause = $sortBy;
        if ($sortBy === 'price') {
            $orderClause = "(CASE WHEN currency = 'EUR' THEN price * " . EUR_TO_BGN . " ELSE price END)";
        }
        
        // Данни
        $sql = "SELECT * FROM products $where ORDER BY $orderClause $sortDir LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        foreach ($products as &$product) {
            $product['price_bgn'] = $this->convertToBGN($product['price'], $product['currency']);
            $product['price_eur'] = $this->convertToEUR($product['price'], $product['currency']);
        }
        
        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    private function convertToBGN($price, $currency) {
        if ($currency === 'BGN') {
            return floatval($price);
        } else {
            return floatval($price) * EUR_TO_BGN;
        }
    }
    
    private function convertToEUR($price, $currency) {
        if ($currency === 'EUR') {
            return floatval($price);
        } else {
            return floatval($price) * BGN_TO_EUR;
        }
    }
}