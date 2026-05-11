<?php
// diagnostico.php - Script de diagnóstico para problemas de conexión en Laravel

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Laravel - Conexión a Base de Datos</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico de Laravel - Conexión a Base de Datos</h1>";

// 1. Verificar versión de PHP
echo "<div class='section'>";
echo "<div class='title'>📌 1. Verificación de PHP</div>";
echo "Versión de PHP: " . phpversion() . "<br>";
if (version_compare(phpversion(), '8.2.0', '>=')) {
    echo "<span class='success'>✅ PHP 8.2 o superior</span><br>";
} else {
    echo "<span class='error'>❌ PHP debe ser 8.2 o superior para Laravel 12</span><br>";
}

// Verificar extensiones necesarias
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'openssl', 'mbstring', 'tokenizer', 'json', 'ctype', 'fileinfo'];
echo "Extensiones cargadas:<br>";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✅ $ext<br>";
    } else {
        echo "  <span class='error'>❌ $ext (FALTA)</span><br>";
    }
}
echo "</div>";

// 2. Verificar archivo .env
echo "<div class='section'>";
echo "<div class='title'>📄 2. Verificar archivo .env</div>";
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    echo "<span class='success'>✅ Archivo .env encontrado</span><br>";
    
    // Leer variables de DB
    $env_content = file_get_contents($env_file);
    $db_config = [];
    
    if (preg_match('/DB_CONNECTION=(.*)/', $env_content, $matches)) $db_config['DB_CONNECTION'] = trim($matches[1]);
    if (preg_match('/DB_HOST=(.*)/', $env_content, $matches)) $db_config['DB_HOST'] = trim($matches[1]);
    if (preg_match('/DB_PORT=(.*)/', $env_content, $matches)) $db_config['DB_PORT'] = trim($matches[1]);
    if (preg_match('/DB_DATABASE=(.*)/', $env_content, $matches)) $db_config['DB_DATABASE'] = trim($matches[1]);
    if (preg_match('/DB_USERNAME=(.*)/', $env_content, $matches)) $db_config['DB_USERNAME'] = trim($matches[1]);
    if (preg_match('/DB_PASSWORD=(.*)/', $env_content, $matches)) $db_config['DB_PASSWORD'] = trim($matches[1]);
    
    echo "<pre>";
    echo "Configuración de Base de Datos en .env:\n";
    echo "=========================================\n";
    echo "DB_CONNECTION: " . ($db_config['DB_CONNECTION'] ?? 'NO DEFINIDO') . "\n";
    echo "DB_HOST: " . ($db_config['DB_HOST'] ?? 'NO DEFINIDO') . "\n";
    echo "DB_PORT: " . ($db_config['DB_PORT'] ?? 'NO DEFINIDO') . "\n";
    echo "DB_DATABASE: " . ($db_config['DB_DATABASE'] ?? 'NO DEFINIDO') . "\n";
    echo "DB_USERNAME: " . ($db_config['DB_USERNAME'] ?? 'NO DEFINIDO') . "\n";
    echo "DB_PASSWORD: " . (isset($db_config['DB_PASSWORD']) && $db_config['DB_PASSWORD'] ? '***DEFINIDA***' : 'VACÍA') . "\n";
    echo "</pre>";
    
    // Verificar APP_KEY
    if (preg_match('/APP_KEY=(.*)/', $env_content, $matches) && strlen(trim($matches[1])) > 20) {
        echo "<span class='success'>✅ APP_KEY configurada</span><br>";
    } else {
        echo "<span class='error'>❌ APP_KEY no configurada o inválida. Ejecuta: php artisan key:generate</span><br>";
    }
    
} else {
    echo "<span class='error'>❌ Archivo .env NO encontrado. Renombra .env.example a .env</span><br>";
}
echo "</div>";

// 3. Probar conexión directa con MySQLi
echo "<div class='section'>";
echo "<div class='title'>🔄 3. Prueba de conexión directa con MySQLi</div>";

if (file_exists($env_file)) {
    $host = $db_config['DB_HOST'] ?? '127.0.0.1';
    $port = $db_config['DB_PORT'] ?? '3306';
    $dbname = $db_config['DB_DATABASE'] ?? '';
    $username = $db_config['DB_USERNAME'] ?? 'root';
    $password = $db_config['DB_PASSWORD'] ?? '';
    
    // Probar conexión a MySQL sin base de datos
    echo "Intentando conectar a MySQL Server...<br>";
    $mysqli = @new mysqli($host, $username, $password, '', (int)$port);
    
    if ($mysqli->connect_error) {
        echo "<span class='error'>❌ Error de conexión a MySQL: " . $mysqli->connect_error . "</span><br>";
        
        // Posibles causas
        echo "<br>Posibles causas:<br>";
        if ($host == 'localhost')
            echo "  • Verifica que XAMPP MySQL esté ejecutándose<br>";
        if ($host == '127.0.0.1')
            echo "  • Revisa si el puerto 3306 está en uso o bloqueado<br>";
        echo "  • Verifica usuario y contraseña de MySQL (default: root / vacío)<br>";
        echo "  • Comprueba que MySQL esté escuchando en $host:$port<br>";
        
    } else {
        echo "<span class='success'>✅ Conexión a MySQL exitosa</span><br>";
        echo "Versión del servidor MySQL: " . $mysqli->server_info . "<br>";
        
        // Verificar si la base de datos existe
        $result = $mysqli->query("SHOW DATABASES LIKE '$dbname'");
        if ($result && $result->num_rows > 0) {
            echo "<span class='success'>✅ Base de datos '$dbname' existe</span><br>";
            
            // Verificar tablas
            $mysqli->select_db($dbname);
            $tables = $mysqli->query("SHOW TABLES");
            if ($tables && $tables->num_rows > 0) {
                echo "<span class='success'>✅ La base de datos contiene " . $tables->num_rows . " tablas</span><br>";
                
                // Verificar tabla migrations
                $migration_check = $mysqli->query("SHOW TABLES LIKE 'migrations'");
                if ($migration_check && $migration_check->num_rows > 0) {
                    $mig_result = $mysqli->query("SELECT COUNT(*) as count FROM migrations");
                    $count = $mig_result->fetch_assoc();
                    echo "<span class='success'>✅ Tabla 'migrations' existe con {$count['count']} registros</span><br>";
                } else {
                    echo "<span class='warning'>⚠️ Tabla 'migrations' no encontrada. Ejecuta: php artisan migrate</span><br>";
                }
            } else {
                echo "<span class='warning'>⚠️ Base de datos vacía (sin tablas)</span><br>";
            }
        } else {
            echo "<span class='error'>❌ Base de datos '$dbname' NO existe</span><br>";
            echo "Intenta crearla con: php artisan db:create o manualmente en phpMyAdmin<br>";
        }
        
        $mysqli->close();
    }
} else {
    echo "<span class='error'>❌ No se puede probar conexión sin archivo .env</span><br>";
}
echo "</div>";

// 4. Verificar archivos de Laravel importantes
echo "<div class='section'>";
echo "<div class='title'>📁 4. Verificar archivos y configuraciones de Laravel</div>";

// Verificar bootstrap/cache
$bootstrap_cache = __DIR__ . '/bootstrap/cache';
if (is_writable($bootstrap_cache)) {
    echo "<span class='success'>✅ bootstrap/cache tiene permisos de escritura</span><br>";
} else {
    echo "<span class='error'>❌ bootstrap/cache NO tiene permisos de escritura (chmod 775)</span><br>";
}

// Verificar storage
$storage = __DIR__ . '/storage';
if (is_writable($storage)) {
    echo "<span class='success'>✅ storage tiene permisos de escritura</span><br>";
} else {
    echo "<span class='error'>❌ storage NO tiene permisos de escritura (chmod 775)</span><br>";
}

// Verificar composer
$vendor = __DIR__ . '/vendor';
if (file_exists($vendor)) {
    echo "<span class='success'>✅ vendor/ existe (dependencias instaladas)</span><br>";
    
    // Verificar Laravel Framework
    $framework = $vendor . '/laravel/framework';
    if (file_exists($framework)) {
        echo "<span class='success'>✅ Laravel Framework instalado</span><br>";
    } else {
        echo "<span class='error'>❌ Laravel Framework no encontrado en vendor</span><br>";
    }
} else {
    echo "<span class='error'>❌ vendor/ NO existe. Ejecuta: composer install</span><br>";
}

echo "</div>";

// 5. Probar conexión con Laravel (si es posible)
echo "<div class='section'>";
echo "<div class='title'>🎯 5. Prueba específica de Laravel</div>";

if (file_exists($vendor . '/autoload.php')) {
    try {
        require_once $vendor . '/autoload.php';
        
        if (file_exists($env_file)) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
            
            echo "<span class='success'>✅ Dotenv cargado correctamente</span><br>";
            
            // Intentar usar los facades de Laravel
            $app = require_once __DIR__ . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
            
            echo "<span class='success'>✅ Aplicación Laravel bootstrappeada</span><br>";
            
            // Probar conexión DB
            try {
                $db = Illuminate\Support\Facades\DB::connection();
                $pdo = $db->getPdo();
                echo "<span class='success'>✅ Conexión Laravel a Base de Datos EXITOSA</span><br>";
                echo "Driver: " . $db->getDriverName() . "<br>";
                
                // Probar query simple
                $result = $db->select('SELECT 1 as test');
                echo "<span class='success'>✅ Query de prueba exitosa</span><br>";
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Error de conexión en Laravel: " . $e->getMessage() . "</span><br>";
            }
            
        } else {
            echo "<span class='error'>❌ .env no encontrado para Laravel</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error cargando Laravel: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span class='error'>❌ vendor/autoload.php no encontrado. Ejecuta: composer install</span><br>";
}
echo "</div>";

// 6. Verificar puertos y servicios
echo "<div class='section'>";
echo "<div class='title'>🌐 6. Verificación de servicios</div>";

// Verificar si MySQL está corriendo
$connection = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 2);
if ($connection) {
    echo "<span class='success'>✅ Puerto 3306 (MySQL) está abierto y accesible</span><br>";
    fclose($connection);
} else {
    echo "<span class='error'>❌ Puerto 3306 (MySQL) NO está accesible: $errstr</span><br>";
    echo "Verifica que MySQL/ MariaDB esté ejecutándose en XAMPP<br>";
}

// Verificar Apache/Web Server
$web_connection = @fsockopen('127.0.0.1', 80, $errno, $errstr, 2);
if ($web_connection) {
    echo "<span class='success'>✅ Puerto 80 (HTTP) está abierto</span><br>";
    fclose($web_connection);
} else {
    echo "<span class='warning'>⚠️ Puerto 80 no responde (posiblemente no crítico)</span><br>";
}

echo "</div>";

// 7. Comandos útiles para resolver problemas
echo "<div class='section'>";
echo "<div class='title'>💡 7. Comandos útiles para resolver problemas</div>";
echo "<pre>
Si encuentras errores, ejecuta estos comandos en orden:

1. Limpiar caché de configuración:
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear

2. Regenerar APP_KEY (si es necesario):
   php artisan key:generate

3. Limpiar configuración de Laravel:
   php artisan optimize:clear

4. Si hay problemas con la base de datos:
   php artisan migrate:fresh --seed

5. Reinstalar dependencias (último recurso):
   composer dump-autoload
   composer install

6. Permisos en Windows (ejecutar como administrador):
   icacls .\\bootstrap\\cache /grant \"Todos\":F
   icacls .\\storage /grant \"Todos\":F

7. Verificar configuración de XAMPP:
   - Abrir xampp/mysql/bin/my.ini
   - Verificar port=3306
   - Verificar bind-address = 127.0.0.1
</pre>";
echo "</div>";

// 8. Solución de problemas específicos
echo "<div class='section'>";
echo "<div class='title'>🔍 8. Diagnóstico final y recomendaciones</div>";

// Recomendaciones específicas
if (isset($db_config['DB_DATABASE']) && $db_config['DB_DATABASE'] == 'saborGestion') {
    echo "• Base de datos configurada: <strong>saborGestion</strong><br>";
    echo "• Verifica que esta base de datos exista en phpMyAdmin<br>";
    echo "• Sino existe, créala: CREATE DATABASE saborGestion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;<br>";
}

echo "<br><strong>Archivos a revisar manualmente:</strong><br>";
echo "✓ config/database.php - Verificar configuración 'mysql'<br>";
echo "✓ .env - Asegurar que no haya espacios después de las variables<br>";
echo "✓ php.ini de XAMPP - Descomentar: extension=mysqli y extension=pdo_mysql<br>";

echo "</div>";

echo "</body></html>";
?>