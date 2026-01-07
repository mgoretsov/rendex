<?php

return [

    // Database Configuration
    'database' => [

        'type' => 'sqlite',
        
        'sqlite' => [
            'path' => BASE_PATH . '/database.sqlite'
        ],
        
    ],
    
    // Currency Configuration
    'currency' => [
        'bgn_to_eur' => 0.511292,  // 1 BGN = 0.511292 EUR
        'eur_to_bgn' => 1.95583,   // 1 EUR = 1.95583 BGN
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 10,
        'max_per_page' => 100
    ],
    
    // Search
    'search' => [
        'debounce_ms' => 500,  // Milliseconds to wait before search
        'min_length' => 0      // Minimum characters before search
    ],
    
    // Application
    'app' => [
        'name' => 'Product Search System',
        'timezone' => 'Europe/Sofia',
        'locale' => 'bg_BG',
        'debug' => true
    ],
    
    // Security
    'security' => [
        'csrf_protection' => false,
        'rate_limiting' => false
    ]

];
