<?php
require_once 'sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!csrf_valido()) {
    $_SESSION['erro_login'] = 'Sessão expirada. Tente novamente.';
    header('Location: login.php');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$senha   = $_POST['senha'] ?? ''; // Senha não leva trim: espaços fazem parte dela

// 1. Validação de campos vazios
if ($usuario === '' || $senha === '') {
    $_SESSION['erro_login'] = 'Preencha todos os campos.';
    header('Location: login.php');
    exit;
}

// 2. Limite simples contra força bruta (por sessão): 5 tentativas a cada 5 minutos
$agora = time();
$tentativas = array_filter($_SESSION['tentativas_login'] ?? [], fn($t) => $t > $agora - 300);
if (count($tentativas) >= 5) {
    $_SESSION['tentativas_login'] = $tentativas;
    $_SESSION['erro_login'] = 'Muitas tentativas. Aguarde alguns minutos e tente novamente.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nome, posto_graduacao, unidade, senha, perfil, status FROM usuarios WHERE usuario = :usuario LIMIT 1');
$stmt->execute(['usuario' => $usuario]);
$user = $stmt->fetch();

// Compatibilidade: senhas antigas foram gravadas com trim()
$senhaOk = $user && (password_verify($senha, $user['senha']) || password_verify(trim($senha), $user['senha']));

if (!$senhaOk) {
    $tentativas[] = $agora;
    $_SESSION['tentativas_login'] = $tentativas;
    $_SESSION['erro_login'] = 'Usuário ou senha incorretos.';
    header('Location: login.php');
    exit;
}

if ($user['status'] === 'pendente') {
    $_SESSION['erro_login'] = 'Sua solicitação de acesso ainda está em análise pelo Administrador.';
    header('Location: login.php');
    exit;
} elseif ($user['status'] === 'recusado') {
    $_SESSION['erro_login'] = 'Sua solicitação de acesso foi recusada.';
    header('Location: login.php');
    exit;
}

// Novo ID de sessão após autenticar (evita fixação de sessão)
session_regenerate_id(true);
unset($_SESSION['tentativas_login'], $_SESSION['erro_login']);

// Atualiza o hash se o algoritmo padrão do PHP mudou
if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id')
        ->execute(['senha' => password_hash($senha, PASSWORD_DEFAULT), 'id' => $user['id']]);
}

// Atualiza a data e hora do último acesso
$stmt_acesso = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id');
$stmt_acesso->execute(['id' => $user['id']]);

// Salva perfil na sessão
$_SESSION['usuario_id']              = (int) $user['id'];
$_SESSION['usuario_nome']            = $user['nome'];
$_SESSION['usuario_posto_graduacao'] = $user['posto_graduacao'];
$_SESSION['usuario_unidade']         = $user['unidade'];
$_SESSION['usuario_perfil']          = $user['perfil'];

header('Location: index.php');
exit;
