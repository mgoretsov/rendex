<?php

class ProductController {

    private $model;
    
    public function __construct() {
        $this->model = new Product();
    }
    
    public function index() {

        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'name';
        $sortDir = $_GET['dir'] ?? 'ASC';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        
        $data = $this->model->getAll($search, $sortBy, $sortDir, $page, 10);
        
        $this->renderView('products/index', $data);

    }
    
    public function search() {

        header('Content-Type: application/json');
        
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'name';
        $sortDir = $_GET['dir'] ?? 'ASC';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        
        $data = $this->model->getAll($search, $sortBy, $sortDir, $page, 10);
        
        echo json_encode($data);

    }
    
    private function renderView($view, $data = []) {

        extract($data);
        
        ob_start();
        require BASE_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        require BASE_PATH . '/views/layout.php';
        
    }
}