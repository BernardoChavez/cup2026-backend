<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Modular Loader
|--------------------------------------------------------------------------
|
| Scans the app/Modules folder and registers any routes.php file
| found within each business module folder automatically.
|
*/

$modulesPath = app_path('Modules');
if (is_dir($modulesPath)) {
    foreach (scandir($modulesPath) as $module) {
        if ($module === '.' || $module === '..') continue;
        $routeFile = $modulesPath . '/' . $module . '/routes.php';
        if (file_exists($routeFile)) {
            Route::group([], $routeFile);
        }
    }
}
