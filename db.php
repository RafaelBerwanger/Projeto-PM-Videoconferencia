<?php
// Credenciais ficam no config.php (fora do Git). Sem ele, usa o padrão do XAMPP local.
if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$db   = defined('DB_NAME') ? DB_NAME : 'encarregado';
$user = defined('DB_USER') ? DB_USER : 'root';
$pass = defined('DB_PASS') ? DB_PASS : ''; // No XAMPP a senha do root por padrão é vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Não expõe detalhes da conexão ao usuário final
    error_log('Erro de conexão com o banco: ' . $e->getMessage());
    http_response_code(500);
    die('Erro ao conectar ao banco de dados. Tente novamente mais tarde.');
}
unset($host, $db, $user, $pass);
