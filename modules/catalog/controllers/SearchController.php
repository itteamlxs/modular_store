<?php
declare(strict_types=1);

class SearchController
{
    public function search(): void
    {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        $pdo = Database::conn();
        $stmt = $pdo->prepare("
            SELECT id, name, price, stock, image_url, category 
            FROM v_products 
            WHERE name LIKE :query OR category LIKE :query
            ORDER BY name ASC
            LIMIT 20
        ");
        
        $stmt->execute(['query' => '%' . $query . '%']);
        $products = $stmt->fetchAll();
        
        echo json_encode($products);
    }
}