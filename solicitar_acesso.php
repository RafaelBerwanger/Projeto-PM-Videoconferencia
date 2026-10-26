<?php
require_once 'sessao.php';
require_once 'listas.php';

// Usuário já logado não precisa solicitar acesso
if (isset($_SESSION['usuario_id'])) {
  header('Location: index.php');
  exit;
}

$mensagem = '';
$tipo_msg = '';

// Valores digitados são mantidos no formulário em caso de erro
$campos = ['nome', 'rg', 'cpf', 'email', 'posto_graduacao', 'unidade', 'telefone', 'usuario'];
$old = array_fill_keys($campos, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($campos as $c) {
    $old[$c] = trim($_POST[$c] ?? '');
  }
  $senha = $_POST['senha'] ?? '';

  $erro = null;
  if (!csrf_valido()) {
    $erro = 'Sessão expirada. Envie o formulário novamente.';
  } else {
    $erro = validarDadosCadastrais($old);
  }
  if (!$erro && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $old['usuario'])) {
    $erro = 'O usuário deve ter de 3 a 50 caracteres (letras, números, ponto, hífen ou sublinhado).';
  }
  if (!$erro && strlen($senha) < 6) {
    $erro = 'A senha deve ter no mínimo 6 caracteres.';
  }
  // Consentimento LGPD: sem aceite não há cadastro
  if (!$erro && ($_POST['aceite_termo'] ?? '') !== 'sim') {
    $erro = 'Para solicitar acesso é necessário aceitar a Política de Privacidade.';
  }

  if (!$erro) {
    try {
      // 1. Verificação se o Usuário, CPF ou E-mail já existem no banco
      $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario OR cpf = :cpf OR email = :email LIMIT 1');
      $stmtCheck->execute(['usuario' => $old['usuario'], 'cpf' => $old['cpf'], 'email' => $old['email']]);

      if ($stmtCheck->fetch()) {
        $erro = 'Usuário, CPF ou E-mail já cadastrados no sistema.';
      } else {
        // 2. Inserção do usuário com perfil "operador" e status "pendente",
        //    registrando a versão e a data do aceite da Política (prova do consentimento)
        $stmt = $pdo->prepare("
          INSERT INTO usuarios (nome, rg, cpf, email, posto_graduacao, unidade, telefone, usuario, senha, perfil, status, termo_versao, termo_aceito_em)
          VALUES (:nome, :rg, :cpf, :email, :posto, :unidade, :telefone, :usuario, :senha, 'operador', 'pendente', :termo, NOW())
        ");
        $stmt->execute([
          'nome'     => $old['nome'],
          'rg'       => $old['rg'],
          'cpf'      => $old['cpf'],
          'email'    => $old['email'],
          'posto'    => $old['posto_graduacao'],
          'unidade'  => $old['unidade'],
          'telefone' => $old['telefone'],
          'usuario'  => $old['usuario'],
          'senha'    => password_hash($senha, PASSWORD_DEFAULT),
          'termo'    => TERMO_VERSAO_ATUAL,
        ]);

        $_SESSION['sucesso_cadastro'] = 'Aguarde a aprovação do Administrador.';
        header('Location: login.php');
        exit;
      }
    } catch (PDOException $e) {
      error_log('Erro no cadastro: ' . $e->getMessage());
      $erro = 'Erro ao processar solicitação no banco de dados. Tente novamente.';
    }
  }

  $mensagem = $erro;
  $tipo_msg = 'error';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitar Acesso - Oitivas PMPR</title>
  <link rel="icon" href="img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="style_login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <div class="login-container" style="max-width: 480px;">
    <div class="login-header">
      <div class="logo-icon"><i class="fa-solid fa-user-plus"></i></div>
      <h2>Solicitar Acesso</h2>
      <p>Preencha os dados cadastrais para requerer acesso</p>
    </div>

    <!-- CAIXA DE MENSAGENS -->
    <?php if (!empty($mensagem)): ?>
      <div class="message-box <?= e($tipo_msg) ?>" style="display: block; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-size: 13px; text-align: center;">
        <?= e($mensagem) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="solicitar_acesso.php">
      <?= csrf_campo() ?>

      <!-- Posto / Graduação -->
      <div class="input-group">
        <label for="posto_graduacao">Posto / Graduação</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-ranking-star input-icon"></i>
          <select id="posto_graduacao" name="posto_graduacao" class="custom-select" required>
            <?= renderOpcoes(LISTA_POSTOS, $old['posto_graduacao']) ?>
          </select>
        </div>
      </div>

      <!-- Nome Completo -->
      <div class="input-group">
        <label for="nome">Nome Completo</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-id-card input-icon"></i>
          <input type="text" id="nome" name="nome" placeholder="Nome completo sem abreviações" value="<?= e($old['nome']) ?>" required>
        </div>
      </div>

      <!-- E-mail -->
      <div class="input-group">
        <label for="email">E-mail Corporativo ou Pessoal</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-envelope input-icon"></i>
          <input type="email" id="email" name="email" placeholder="seu.email@pm.pr.gov.br" value="<?= e($old['email']) ?>" required>
        </div>
      </div>

      <!-- RG e CPF -->
      <div style="display: flex; gap: 10px;">
        <div class="input-group" style="flex: 1;">
          <label for="rg">RG</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-address-card input-icon"></i>
            <input type="text" id="rg" name="rg" placeholder="Ex: 12.345.678-9" value="<?= e($old['rg']) ?>" required>
          </div>
        </div>
        <div class="input-group" style="flex: 1;">
          <label for="cpf">CPF</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-fingerprint input-icon"></i>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" oninput="mascaraCPF(this)" value="<?= e($old['cpf']) ?>" required>
          </div>
        </div>
      </div>

      <!-- Unidade / OPM -->
      <div class="input-group">
        <label for="unidade">Unidade / OPM</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-building-shield input-icon"></i>
          <select id="unidade" name="unidade" class="custom-select" required>
            <?= renderOpcoes(LISTA_UNIDADES, $old['unidade'], 'Selecione a Unidade...') ?>
          </select>
        </div>
      </div>

      <!-- Telefone -->
      <div class="input-group">
        <label for="telefone">Telefone / Whatsapp</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-phone input-icon"></i>
          <input type="text" id="telefone" name="telefone" placeholder="(41) 99999-9999" maxlength="15" oninput="mascaraTelefone(this)" value="<?= e($old['telefone']) ?>" required>
        </div>
      </div>

      <!-- Usuário e Senha -->
      <div style="display: flex; gap: 10px;">
        <div class="input-group" style="flex: 1;">
          <label for="usuario">Usuário</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-user input-icon"></i>
            <input type="text" id="usuario" name="usuario" placeholder="joao.silva" pattern="[A-Za-z0-9._\-]{3,50}" autocomplete="username" value="<?= e($old['usuario']) ?>" required>
          </div>
        </div>
        <div class="input-group" style="flex: 1;">
          <label for="senha">Senha</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="senha" name="senha" placeholder="mín. 6 caracteres" minlength="6" autocomplete="new-password" required>
          </div>
        </div>
      </div>

      <!-- Consentimento LGPD -->
      <label style="display:flex; gap:8px; align-items:flex-start; font-size:12.5px; color:#334155; cursor:pointer; margin-top:6px; line-height:1.5;">
        <input type="checkbox" name="aceite_termo" value="sim" required style="margin-top:3px;" <?= ($_POST['aceite_termo'] ?? '') === 'sim' ? 'checked' : '' ?>>
        <span>Li e concordo com a <a href="politica_privacidade.php" target="_blank">Política de Privacidade e o Termo de Consentimento</a> e autorizo o tratamento dos meus dados pessoais nos termos da LGPD (Lei nº 13.709/2018).</span>
      </label>

      <button type="submit" class="btn-login" style="margin-top: 10px;">
        <span>Enviar Solicitação</span>
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </form>

    <div class="login-footer">
      <p>Já possui uma conta? <a href="login.php">Voltar para o Login</a></p>
    </div>
  </div>

  <script>
    // Máscara para CPF: 000.000.000-00
    function mascaraCPF(i) {
      let v = i.value.replace(/\D/g, "").slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
      i.value = v;
    }

    // Máscara para Telefone: (00) 00000-0000 ou (00) 0000-0000
    function mascaraTelefone(i) {
      let v = i.value.replace(/\D/g, "").slice(0, 11);
      v = v.replace(/^(\d{2})(\d)/, "($1) $2");
      v = v.replace(/(\d)(\d{4})$/, "$1-$2");
      i.value = v;
    }
  </script>
</body>

</html>
