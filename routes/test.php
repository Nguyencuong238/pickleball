<?php
// Test file to check routes

use Illuminate\Support\Facades\Route;

Route::get('/test-routes', function() {
    $routes = Route::getRoutes();
    $apiRoutes = [];
    
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'instructor-review') !== false) {
            $apiRoutes[] = [
                'uri' => $route->uri(),
                'method' => $route->methods(),
                'name' => $route->name(),
            ];
        }
    }
    
    return response()->json($apiRoutes);
});
