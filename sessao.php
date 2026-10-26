<?php
// Inicialização comum a todas as páginas: sessão segura, conexão com o banco
// e funções de autenticação / proteção CSRF.

if (session_status() === PHP_SESSION_NONE) {
    // Cookie expira ao fechar o navegador e não fica acessível via JavaScript
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

require_once __DIR__ . '/db.php';

// ---------------------------------------------------------------
// MÓDULOS EM DESENVOLVIMENTO
// Procedimentos ainda não foi liberado: fica fora do menu e as páginas
// recusam acesso direto pela URL. Para liberar, troque para true.
// ---------------------------------------------------------------
const MODULO_PROCEDIMENTOS_LIBERADO = false;

// Bloqueia as páginas do módulo de Procedimentos enquanto não for liberado
function exigirModuloProcedimentos(bool $respostaJson = false): void
{
    if (MODULO_PROCEDIMENTOS_LIBERADO) {
        return;
    }
    if ($respostaJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => false, 'erro' => 'Funcionalidade ainda não disponível.']);
    } else {
        header('Location: index.php');
    }
    exit;
}

// ---------------------------------------------------------------
// POLÍTICA DE PRIVACIDADE / TERMO DE CONSENTIMENTO (LGPD)
// Ao alterar a política de forma relevante, mude a versão: todos os
// usuários terão de aceitar de novo no próximo acesso.
// ---------------------------------------------------------------
const TERMO_VERSAO_ATUAL = '1.1';
const TERMO_DATA_ATUALIZACAO = '26/10/2026';

// Escapa valores para saída HTML
function e($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Exige usuário logado e não pendente/recusado no banco. Retorna o registro completo.
// Se o admin excluir/recusar o usuário, a sessão ativa deixa de valer.
function exigirLogin(bool $respostaJson = false): array
{
    global $pdo;

    $user = null;
    if (isset($_SESSION['usuario_id'])) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $_SESSION['usuario_id']]);
        $user = $stmt->fetch();
    }

    if (!$user || in_array($user['status'], ['pendente', 'recusado'], true)) {
        $_SESSION = [];
        session_destroy();
        if ($respostaJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
        } else {
            header('Location: login.php');
        }
        exit;
    }

    // Sem aceite da versão atual da Política de Privacidade: pede o aceite antes de liberar o sistema
    if (($user['termo_versao'] ?? null) !== TERMO_VERSAO_ATUAL && basename($_SERVER['PHP_SELF']) !== 'aceitar_termos.php') {
        if ($respostaJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'erro' => 'É necessário aceitar a Política de Privacidade. Recarregue a página.']);
        } else {
            header('Location: aceitar_termos.php');
        }
        exit;
    }

    // Mantém a sessão sincronizada com o banco (ex.: perfil alterado pelo admin)
    $_SESSION['usuario_nome']            = $user['nome'];
    $_SESSION['usuario_posto_graduacao'] = $user['posto_graduacao'];
    $_SESSION['usuario_unidade']         = $user['unidade'];
    $_SESSION['usuario_perfil']          = $user['perfil'];

    return $user;
}

function exigirAdmin(): array
{
    $user = exigirLogin();
    if ($user['perfil'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
    return $user;
}

// ---------------------------------------------------------------
// PROTEÇÃO CSRF
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Aceita o token via campo POST ou cabeçalho X-CSRF-Token (requisições fetch)
function csrf_valido(): bool
{
    $enviado = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($enviado) && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $enviado);
}
