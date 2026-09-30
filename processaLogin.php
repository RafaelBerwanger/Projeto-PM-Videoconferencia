<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$senha   = trim($_POST['senha'] ?? '');

// 1. Validação de campos vazios
if (empty($usuario) || empty($senha)) {
    $_SESSION['erro_login'] = 'Preencha todos os campos.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nome, rg, cpf, posto_graduacao, unidade, telefone, senha, perfil, status FROM usuarios WHERE usuario = :usuario LIMIT 1');
$stmt->execute(['usuario' => $usuario]);
$user = $stmt->fetch();

if ($user && password_verify($senha, $user['senha'])) {
    if ($user['status'] === 'pendente') {
        $_SESSION['erro_login'] = 'Sua solicitação de acesso ainda está em análise pelo Administrador.';
        header('Location: login.php');
        exit;
    } elseif ($user['status'] === 'recusado') {
        $_SESSION['erro_login'] = 'Sua solicitação de acesso foi recusada.';
        header('Location: login.php');
        exit;
    }

    // Salva perfil completo na sessão
    $_SESSION['usuario_id']              = $user['id'];
    $_SESSION['usuario_nome']            = $user['nome'];
    $_SESSION['usuario_posto_graduacao'] = $user['posto_graduacao'];
    $_SESSION['usuario_unidade']         = $user['unidade'];
    $_SESSION['usuario_perfil']          = $user['perfil'];
    unset($_SESSION['erro_login']);

    header('Location: index.php');
    exit;
} else {
    // ⚠️ O QUE FALTAVA: Trata usuário inexistente ou senha errada
    $_SESSION['erro_login'] = 'Usuário ou senha incorretos.';
    header('Location: login.php');
    exit;
}