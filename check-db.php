<?php
// check-db.php - Debug delle variabili database

echo "=== DEBUG DATABASE VARIABLES ===\n";

// Variabili MYSQL di Railway
$mysql_vars = ['MYSQLHOST', 'MYSQLPORT', 'MYSQLDATABASE', 'MYSQLUSER', 'MYSQLPASSWORD'];
foreach ($mysql_vars as $var) {
    echo "$var: " . (isset($_ENV[$var]) ? $_ENV[$var] : 'NOT SET') . "\n";
}

// Variabili DB di Laravel
$db_vars = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($db_vars as $var) {
    echo "$var: " . (isset($_ENV[$var]) ? $_ENV[$var] : 'NOT SET') . "\n";
}

echo "\n=== TESTING MYSQL CONNECTION ===\n";

// Test connessione diretta
if (isset($_ENV['MYSQLHOST'])) {
    try {
        $host = $_ENV['MYSQLHOST'];
        $port = $_ENV['MYSQLPORT'] ?? '3306';
        $dbname = $_ENV['MYSQLDATABASE'];
        $username = $_ENV['MYSQLUSER'];
        $password = $_ENV['MYSQLPASSWORD'];
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        echo "✅ MySQL Connection SUCCESS!\n";
        echo "Connected to: $host:$port/$dbname\n";
        
        // Crea la tabella migrations se non esiste
        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )");
        echo "✅ Migrations table ready\n";
        
    } catch (Exception $e) {
        echo "❌ MySQL Connection FAILED: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ MYSQLHOST not set\n";
}

echo "\n=== FIXING .ENV FILE ===\n";

// Leggi il file .env esistente
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Verifica se c'è DB_CONNECTION=sqlite
    if (strpos($envContent, 'DB_CONNECTION=sqlite') !== false) {
        echo "⚠️  Found DB_CONNECTION=sqlite - FIXING...\n";
        $envContent = str_replace('DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql', $envContent);
        file_put_contents($envPath, $envContent);
        echo "✅ Fixed DB_CONNECTION to mysql\n";
    }
    
    // Controlla se mancano variabili MYSQL
    $requiredVars = [
        'DB_HOST' => '{{.MYSQLHOST}}',
        'DB_PORT' => '{{.MYSQLPORT}}',
        'DB_DATABASE' => '{{.MYSQLDATABASE}}',
        'DB_USERNAME' => '{{.MYSQLUSER}}',
        'DB_PASSWORD' => '{{.MYSQLPASSWORD}}',
    ];
    
    foreach ($requiredVars as $var => $value) {
        if (!preg_match("/^$var=.*/m", $envContent)) {
            $envContent .= "\n$var=$value";
            echo "✅ Added $var=$value\n";
        }
    }
    
    file_put_contents($envPath, $envContent);
    echo "✅ .env file updated\n";
} else {
    echo "❌ .env file not found\n";
}