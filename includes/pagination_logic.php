<?php
/**
 * Pagination Logic Helper
 * Handles pagination parameters and validation
 */

function getPaginationParams($default_limit = 10, $min_limit = 5, $max_limit = 100) {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : $default_limit;
    $limit = max($min_limit, min($max_limit, $limit));
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = $page < 1 ? 1 : $page;
    
    $offset = ($page - 1) * $limit;
    
    return [
        'limit' => $limit,
        'page' => $page,
        'offset' => $offset,
        'min_limit' => $min_limit,
        'max_limit' => $max_limit
    ];
}

function calculateTotalPages($total_data, $limit) {
    return ceil($total_data / $limit);
}
