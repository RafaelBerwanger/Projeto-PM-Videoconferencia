<?php
// Credenciais ficam no config.php (fora do Git). Sem ele, usa o padrão do XAMPP local.
if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// trim(): espaços colados por engano no config.php impedem a conexão
$host = defined('DB_HOST') ? trim(DB_HOST) : 'localhost';
$db   = defined('DB_NAME') ? trim(DB_NAME) : 'encarregado';
$user = defined('DB_USER') ? trim(DB_USER) : 'root';
$pass = defined('DB_PASS') ? DB_PASS : ''; // No XAMPP a senha do root por padrão é vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Não expõe detalhes da conexão ao usuário final; mostra só o código do MySQL
    // (ajuda o administrador a identificar a causa sem revelar usuário ou senha):
    // 1045 = usuário/senha incorretos | 1049 = banco não existe | 2002 = servidor (host) incorreto
    error_log('Erro de conexão com o banco: ' . $e->getMessage());
    $codigoErro = (int) ($e->errorInfo[1] ?? 0) ?: (int) $e->getCode();
    // Indica se as credenciais vieram do config.php ou do padrão local (XAMPP)
    $origem = defined('DB_HOST') ? 'config' : 'padrão local: config.php não encontrado ou sem DB_HOST';
    http_response_code(500);
    die('Erro ao conectar ao banco de dados. Tente novamente mais tarde. (código ' . $codigoErro . ', ' . $origem . ')');
}
unset($host, $db, $user, $pass);
