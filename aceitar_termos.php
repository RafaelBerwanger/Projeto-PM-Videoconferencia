<?php
require_once 'sessao.php';

// Usuário logado que ainda não aceitou a versão atual da Política de Privacidade
$user = exigirLogin();
$erro = '';

if (($user['termo_versao'] ?? null) === TERMO_VERSAO_ATUAL) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif (($_POST['aceite'] ?? '') !== 'sim') {
        $erro = 'Para usar o sistema é necessário marcar a opção de aceite.';
    } else {
        try {
            $pdo->prepare('UPDATE usuarios SET termo_versao = :v, termo_aceito_em = NOW() WHERE id = :id')
                ->execute(['v' => TERMO_VERSAO_ATUAL, 'id' => $user['id']]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            // Normalmente: colunas do termo ainda não criadas (ver migração no database.sql)
            error_log('Erro ao registrar aceite do termo: ' . $e->getMessage());
            $erro = 'Não foi possível registrar o aceite. Avise o administrador do sistema.';
        }
    }
}

$atualizacao = !empty($user['termo_versao']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Aceite</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="login-container" style="max-width: 560px;">
        <div class="login-header">
            <div class="logo-icon"><i class="fa-solid fa-user-shield"></i></div>
            <h2><?= $atualizacao ? 'Política de Privacidade atualizada' : 'Proteção de Dados Pessoais' ?></h2>
            <p>Olá, <?= e(trim($user['posto_graduacao'] . ' ' . $user['nome'])) ?>. Antes de continuar, leia e aceite o termo abaixo.</p>
        </div>

        <?php if ($erro): ?>
            <div class="message-box error" style="display:block; padding:10px; margin-bottom:15px; border-radius:6px; font-size:13px; text-align:center;"><?= e($erro) ?></div>
        <?php endif; ?>

        <div style="font-size: 13px; line-height: 1.6; color: #334155; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; margin-bottom: 14px;">
            <p style="margin-top:0;"><strong>Em resumo:</strong></p>
            <ul style="margin: 0 0 0 18px; padding: 0;">
                <li>Guardamos apenas seus dados de cadastro (nome, posto, unidade, RG, CPF, e-mail, telefone e acesso).</li>
                <li>Os dados das oitivas e os vídeos ficam no seu navegador e computador, e <b>não</b> são enviados ao servidor.</li>
                <li>"Refinar com IA" envia o texto do depoimento a um serviço nos Estados Unidos (Groq), somente quando você usa o recurso.</li>
                <li>A videochamada usa o MiroTalk P2P: áudio e vídeo vão direto entre os participantes, e a gravação é feita no seu navegador.</li>
                <li>Você pode corrigir seus dados em <i>Meu Perfil</i> e revogar o consentimento pedindo a exclusão da conta.</li>
            </ul>
            <p style="margin-bottom:0;"><a href="politica_privacidade.php" target="_blank"><i class="fa-solid fa-file-shield"></i> Ler a Política de Privacidade completa (versão <?= e(TERMO_VERSAO_ATUAL) ?>)</a></p>
        </div>

        <form method="POST" action="aceitar_termos.php">
            <?= csrf_campo() ?>
            <label style="display:flex; gap:8px; align-items:flex-start; font-size:13px; color:#1e293b; cursor:pointer; margin-bottom:16px;">
                <input type="checkbox" name="aceite" value="sim" required style="margin-top:3px;">
                <span>Li e concordo com a Política de Privacidade e consinto com o tratamento dos meus dados pessoais nos termos da LGPD (Lei nº 13.709/2018).</span>
            </label>
            <button type="submit" class="btn-login"><span>Aceitar e continuar</span> <i class="fa-solid fa-check"></i></button>
        </form>

        <div class="login-footer">
            <p>Não concorda? <a href="logout.php">Sair do sistema</a></p>
        </div>
    </div>
</body>

</html>
