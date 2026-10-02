<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Encarregado - Intranet PMPR</title>
  <link rel="icon" href="img/favicon.ico" type="image/x-icon">

  <?php
  session_start();
  // Se o usuário não estiver logado, redireciona para a tela de login
  if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
  }

  $nome_usuario  = $_SESSION['usuario_nome'] ?? 'Policial Militar';
  $posto_usuario = $_SESSION['usuario_posto_graduacao'] ?? '';
  $opm_usuario   = $_SESSION['usuario_unidade'] ?? 'PMPR';
  $perfil_usuario = $_SESSION['usuario_perfil'] ?? 'usuario';
  ?>

  <!-- Font Awesome para ícones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Ficheiros CSS da Intranet PMPR -->
  <link rel="stylesheet" href="style.css?v=1.0.1">
  <link rel="stylesheet" href="style_auxilio.css">

  <style>
    /* Estilos específicos do Painel Inicial */
    .welcome-card {
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .welcome-info h2 {
      font-size: 20px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 6px;
    }

    .welcome-info p {
      font-size: 13px;
      color: #64748b;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card-modulo {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 24px;
      text-decoration: none;
      color: inherit;
      transition: all 0.2s ease-in-out;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      position: relative;
      overflow: hidden;
    }

    .card-modulo:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
      border-color: #2b80c5;
    }

    .card-modulo .card-icon {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      background-color: #f1f5f9;
      color: #2b80c5;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin-bottom: 16px;
      transition: background-color 0.2s;
    }

    .card-modulo:hover .card-icon {
      background-color: #2b80c5;
      color: #ffffff;
    }

    .card-modulo h3 {
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 8px;
    }

    .card-modulo p {
      font-size: 13px;
      color: #64748b;
      line-height: 1.5;
      margin-bottom: 16px;
    }

    .card-modulo .card-link {
      font-size: 13px;
      font-weight: 600;
      color: #2b80c5;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: auto;
    }

    .info-box {
      background: #f8fafc;
      border-left: 4px solid #2b80c5;
      padding: 16px;
      border-radius: 0 8px 8px 0;
      margin-top: 10px;
    }

    .info-box h4 {
      font-size: 14px;
      color: #1e293b;
      margin-bottom: 4px;
      font-weight: 600;
    }

    .info-box p {
      font-size: 13px;
      color: #64748b;
      margin: 0;
    }
  </style>
</head>

<body>

  <!-- Inclui a barra lateral centralizada -->
  <?php require_once 'sidebar.php'; ?>

  <!-- CONTEÚDO PRINCIPAL -->
  <div class="main-wrapper">

    <?php require_once 'top-nav.php'; ?>

    <!-- BANNER DE TÍTULO DA PÁGINA -->
    <div class="page-banner">
      <i class="fa-solid fa-house"></i> Painel Inicial de Apoio ao Encarregado
    </div>

    <div class="content-area">

      <!-- CARTÃO DE BOAS-VINDAS -->
      <div class="welcome-card">
        <div class="welcome-info">
          <h2>Bem-vindo, <?= htmlspecialchars(($posto_usuario ? $posto_usuario . ' ' : '') . $nome_usuario) ?>!</h2>
          <p><i class="fa-solid fa-building-shield" style="color: #2b80c5;"></i> OPM: <?= htmlspecialchars($opm_usuario) ?></p>
        </div>
        <div>
          <a href="meu_perfil.php" class="btn btn-blue" style="font-size: 13px; padding: 8px 16px;">
            <i class="fa-solid fa-user-gear"></i> Editar Dados
          </a>
        </div>
      </div>

      <!-- GRID DE MÓDULOS -->
      <div class="dashboard-grid">

        <!-- MÓDULO 1: OITIVAS AUDIOVISUAIS -->
        <a href="audiovisual.php" class="card-modulo">
          <div class="card-icon">
            <i class="fa-solid fa-video"></i>
          </div>
          <h3>Oitivas Audiovisuais</h3>
          <p>Elaboração de relatórios de gravação audiovisual, checklist interativo, captura de quadro do vídeo e geração de códigos HASH criptográficos (SHA-1 / SHA-256).</p>
          <div class="card-link">
            Acessar módulo <i class="fa-solid fa-arrow-right"></i>
          </div>
        </a>

        <!-- MÓDULO 2: OITIVAS ESCRITAS -->
        <a href="presencial.php" class="card-modulo">
          <div class="card-icon">
            <i class="fa-solid fa-file-pen"></i>
          </div>
          <h3>Oitivas Escritas</h3>
          <p>Auxiliar para confecção de Termos de Inquirição de Testemunhas, Qualificação/Interrogatório de Acusados e Declarações de Vítimas presenciais.</p>
          <div class="card-link">
            Acessar módulo <i class="fa-solid fa-arrow-right"></i>
          </div>
        </a>

        <!-- MÓDULO 3: GESTÃO DE ACESSOS (SÓ ADMIN) OU MEU PERFIL -->
        <?php if ($perfil_usuario === 'admin'): ?>
          <a href="admin_usuarios.php" class="card-modulo">
            <div class="card-icon" style="color: #f59e0b;">
              <i class="fa-solid fa-user-shield"></i>
            </div>
            <h3>Gestão de Acessos</h3>
            <p>Aprovação de novos cadastros de militares, controle de permissões de acesso ao sistema e atalho de contato via WhatsApp institucional.</p>
            <div class="card-link" style="color: #f59e0b;">
              Gerenciar usuários <i class="fa-solid fa-arrow-right"></i>
            </div>
          </a>
        <?php else: ?>
          <a href="meu_perfil.php" class="card-modulo">
            <div class="card-icon">
              <i class="fa-solid fa-user-pen"></i>
            </div>
            <h3>Meu Perfil Cadastral</h3>
            <p>Consulte e atualize suas informações pessoais, Posto/Graduação, OPM de origem, telefone de contato e senha de acesso.</p>
            <div class="card-link">
              Acessar perfil <i class="fa-solid fa-arrow-right"></i>
            </div>
          </a>
        <?php endif; ?>

      </div>

      <!-- BLOCOS DE INFORMAÇÕES/ORIENTAÇÕES -->
      <div class="card">
        <div class="block-title">
          <i class="fa-solid fa-circle-info"></i> Orientações ao Encarregado
        </div>

        <div class="info-box">
          <h4>Padronização dos Procedimentos</h4>
          <p>Selecione o módulo desejado acima para dar início aos trabalhos. Lembre-se de conferir todos os dados de qualificação dos depoentes e número dos procedimentos antes do encerramento e impressão dos documentos oficiais.</p>
        </div>
      </div>

    </div>

    <!-- RODAPÉ -->
    <!-- Inclui o Rodapé Centralizado -->
        <?php require_once 'footer.php'; ?>

  </div>

  <script>
    function atualizarRelogioBrasilia() {
      // Obtém a data/hora ajustada para o fuso horário de Brasília (America/Sao_Paulo)
      const opcoes = {
        timeZone: 'America/Sao_Paulo',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };

      const agora = new Date().toLocaleString('pt-BR', opcoes);
      const [data, hora] = agora.split(', ');

      document.getElementById('relogio-brasilia').textContent = `${data} - ${hora}`;
    }

    // Atualiza imediatamente e depois a cada 1 segundo (1000ms)
    atualizarRelogioBrasilia();
    setInterval(atualizarRelogioBrasilia, 1000);
  </script>

</body>

</html>