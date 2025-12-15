<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MyJobApplicationController;
use App\Http\Controllers\MyJobController;
use Illuminate\Support\Facades\Route;



use Illuminate\Support\Facades\DB;

// ... tue route esistenti ...

// ROUTE DI DEBUG
Route::get('/debug', function () {
    echo "<h1>🔧 DEBUG LARAVEL ON RAILWAY</h1>";
    
    // 1. PHP Info
    echo "<h2>📊 PHP Info</h2>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
    
    // 2. Environment
    echo "<h2>🌍 Environment</h2>";
    echo "APP_ENV: " . env('APP_ENV') . "<br>";
    echo "APP_DEBUG: " . env('APP_DEBUG') . "<br>";
    echo "APP_URL: " . env('APP_URL') . "<br>";
    
    // 3. Database
    echo "<h2>🗄️ Database</h2>";
    try {
        echo "DB_CONNECTION: " . env('DB_CONNECTION') . "<br>";
        echo "DB_HOST: " . env('DB_HOST') . "<br>";
        echo "DB_PORT: " . env('DB_PORT') . "<br>";
        echo "DB_DATABASE: " . env('DB_DATABASE') . "<br>";
        echo "DB_USERNAME: " . env('DB_USERNAME') . "<br>";
        
        // Test connessione
        DB::connection()->getPdo();
        echo "✅ Database Connection: OK<br>";
        
        // Verifica tabelle
        $tables = DB::select('SHOW TABLES');
        echo "✅ Tables in database: " . count($tables) . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Database Error: " . $e->getMessage() . "<br>";
    }
    
    // 4. File System
    echo "<h2>📁 File System</h2>";
    $paths = [
        'storage' => storage_path(),
        'bootstrap/cache' => base_path('bootstrap/cache'),
        '.env' => base_path('.env'),
        'vendor' => base_path('vendor'),
    ];
    
    foreach ($paths as $name => $path) {
        echo $name . ": ";
        if (file_exists($path)) {
            echo "✅ EXISTS | ";
            echo is_writable($path) ? "WRITABLE" : "NOT WRITABLE";
        } else {
            echo "❌ NOT FOUND";
        }
        echo "<br>";
    }
    
    // 5. Laravel Components
    echo "<h2>⚙️ Laravel Components</h2>";
    try {
        $app = app();
        echo "✅ Application: LOADED<br>";
        echo "✅ Service Providers: " . count($app->getLoadedProviders()) . "<br>";
    } catch (Exception $e) {
        echo "❌ Application Error: " . $e->getMessage() . "<br>";
    }
    
    // 6. Routes
    echo "<h2>🛣️ Routes</h2>";
    try {
        $routes = Route::getRoutes()->getRoutes();
        echo "Total Routes: " . count($routes) . "<br>";
        foreach ($routes as $route) {
            echo $route->uri() . " → " . ($route->getName() ?: 'No name') . "<br>";
        }
    } catch (Exception $e) {
        echo "Routes Error: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<h3>🎉 Debug completato!</h3>";
    echo "<p>Se vedi questa pagina, Laravel funziona. Il problema è nelle tue route/controller.</p>";
    
    return ""; // No layout
});

// Route per testare errori
Route::get('/test-error', function() {
    // Forza un errore per vedere come Laravel lo gestisce
    throw new Exception('Test error - Questo è un errore di test!');
});

// Route semplice per testare se Laravel risponde
Route::get('/test', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel is working!',
        'timestamp' => now(),
        'env' => app()->environment(),
        'debug' => config('app.debug'),
    ]);
});

Route::get('', fn() => to_route('jobs.index'));

Route::resource('jobs', JobController::class)
    ->only(['index', 'show']);

Route::get('login', fn() => to_route('auth.create'))
    ->name('login');
Route::resource('auth', AuthController::class)
    ->only(['create', 'store']);
Route::delete('logout', fn() => to_route('auth.destroy'))
    ->name('logout');
Route::delete('auth', [AuthController::class, 'destroy'])
    ->name('auth.destroy');

Route::middleware('auth')->group(function () {
    Route::resource('job.application', JobApplicationController::class)
        ->only(['create', 'store']);

    Route::resource('my-job-applications', MyJobApplicationController::class)
        ->only(['index', 'destroy']);

    Route::resource('employer', EmployerController::class)
        ->only(['create', 'store']);

    Route::middleware('employer')
        ->resource('my-jobs', MyJobController::class);
});