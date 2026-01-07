<?php

class Database {

    private static $instance = null;
    private $pdo;
    
    private function __construct() {

        $dbPath = BASE_PATH . '/database.sqlite';
        
        try {
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initDatabase();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

    }
    
    public static function getInstance() {

        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;

    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    private function initDatabase() {

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                sku TEXT UNIQUE NOT NULL,
                stock INTEGER NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                currency TEXT NOT NULL CHECK(currency IN ('BGN', 'EUR')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $count = $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        
        if ( $count == 0 ) {

            $products = [
                ['Laptop Dell XPS 15', 'DELL-XPS-15', 5, 2499.99, 'BGN'],
                ['Mouse Logitech MX Master', 'LOG-MX-MASTER', 25, 89.90, 'EUR'],
                ['Keyboard Mechanical RGB', 'KB-MECH-RGB', 15, 189.50, 'BGN'],
                ['Monitor Samsung 27"', 'SAM-MON-27', 8, 299.00, 'EUR'],
                ['USB-C Hub 7-port', 'USB-HUB-7', 50, 45.99, 'BGN'],
                ['Webcam Logitech C920', 'LOG-C920', 12, 79.99, 'EUR'],
                ['Headphones Sony WH-1000XM5', 'SONY-WH-1000', 7, 349.00, 'EUR'],
                ['SSD Samsung 1TB', 'SAM-SSD-1TB', 30, 199.90, 'BGN'],
                ['RAM Corsair 16GB DDR4', 'CORS-RAM-16', 20, 89.99, 'EUR'],
                ['Router TP-Link AX3000', 'TPL-AX3000', 10, 159.99, 'BGN'],
                ['External HDD 2TB', 'HDD-EXT-2TB', 18, 75.50, 'EUR'],
                ['Graphics Card RTX 4060', 'GPU-RTX-4060', 3, 899.00, 'EUR'],
                ['Power Supply 750W', 'PSU-750W', 14, 179.90, 'BGN'],
                ['Laptop Stand Aluminum', 'STAND-ALU', 35, 29.99, 'BGN'],
                ['Docking Station USB-C', 'DOCK-USBC', 9, 149.00, 'EUR'],
            ];
            
            $stmt = $this->pdo->prepare("
                INSERT INTO products (name, sku, stock, price, currency) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($products as $product) {
                $stmt->execute($product);
            }

        }

    }

}