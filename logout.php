<?php
require_once 'sessao.php';

$_SESSION = [];                // Limpa todas as variáveis de sessão

// Remove o cookie de sessão do navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();             // Destrói a sessão no servidor

// Redireciona de volta para a tela de login
header('Location: login.php');
exit;
