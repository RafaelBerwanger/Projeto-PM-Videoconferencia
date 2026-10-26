<?php
require_once 'sessao.php';

// Captura e limpa as mensagens de erro ou sucesso armazenadas na sessão
$mensagem_sucesso = $_SESSION['sucesso_cadastro'] ?? '';
$mensagem_erro    = $_SESSION['erro_login'] ?? '';

unset($_SESSION['sucesso_cadastro']);
unset($_SESSION['erro_login']);

// Se o usuário já estiver autenticado, redireciona para a página principal
if (isset($_SESSION['usuario_id'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Controle de Acesso - Oitivas PMPR</title>
  <link rel="stylesheet" href="style_login.css">
  <!-- Ícones do Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>

  <div class="login-container">
    <div class="login-header">
      <!-- IMAGEM DINO PNG -->
      <div style="text-align: center; margin-bottom: 15px;">
        <img src="img/dino.png"
          alt="Dino Encarregado PMPR"
          style="width: 100%; max-width: 220px; height: auto; display: block; margin: 0 auto;">
      </div>
      <div class="logo-icon">
        <!-- <i class="fa-solid fa-shield-halved"></i> -->
      </div>
      <h2>Acesso ao Sistema</h2>
      <p>Insira suas credenciais para continuar</p>
    </div>


    <!-- ALERTA DE CONFIRMAÇÃO DE SOLICITAÇÃO ENVIADA -->
    <?php if (!empty($mensagem_sucesso)): ?>
      <div class="message-box success" style="display: flex; align-items: center; gap: 10px; background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #22c55e;"></i>
        <div>
          <strong>Solicitação enviada com sucesso!</strong><br>
          <?= htmlspecialchars($mensagem_sucesso) ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- ALERTA DE ERRO DE LOGIN (CASO HAJA) -->
    <?php if (!empty($mensagem_erro)): ?>
      <div class="message-box error" style="display: flex; align-items: center; gap: 10px; background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; color: #ef4444;"></i>
        <div><?= htmlspecialchars($mensagem_erro) ?></div>
      </div>
    <?php endif; ?>

    <!-- Envio seguro via POST para o processador PHP -->
    <form action="processaLogin.php" method="POST">
      <?= csrf_campo() ?>
      <!-- Campo de Usuário -->
      <div class="input-group">
        <label for="usuario">Usuário</label>
        <div class="input-wrapper">
          <i class="fa-regular fa-user input-icon"></i>
          <input
            type="text"
            id="usuario"
            name="usuario"
            placeholder="Digite seu usuário"
            required
            autocomplete="username">
        </div>
      </div>

      <!-- Campo de Senha -->
      <div class="input-group">
        <label for="senha">Senha</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-lock input-icon"></i>
          <input
            type="password"
            id="senha"
            name="senha"
            placeholder="••••••••"
            required
            autocomplete="current-password">
          <i class="fa-regular fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
        </div>
      </div>

      <!-- Opções Adicionais -->
      <div class="form-options">
        <span class="forgot-password">Esqueceu a senha? Contate o administrador do sistema.</span>
      </div>

      <!-- Botão de Login -->
      <button type="submit" class="btn-login">
        <span>Entrar</span>
        <i class="fa-solid fa-arrow-right"></i>
      </button>
    </form>



    <div class="login-footer">
      <p>Não tem uma conta? <a href="solicitar_acesso.php">Solicite acesso</a></p>
      <p style="margin-top: 6px; font-size: 12px;"><a href="politica_privacidade.php">Política de Privacidade</a></p>
    </div>
  </div>

    <script>
      // Função para alternar visibilidade da senha (mostrar/ocultar)
      function togglePasswordVisibility() {
        const passwordInput = document.getElementById('senha');
        const toggleIcon = document.getElementById('togglePassword');

        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          toggleIcon.classList.remove('fa-eye');
          toggleIcon.classList.add('fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          toggleIcon.classList.remove('fa-eye-slash');
          toggleIcon.classList.add('fa-eye');
        }
      }
    </script>

</body>

</html>