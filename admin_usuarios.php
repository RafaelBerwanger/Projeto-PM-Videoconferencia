<?php
session_start();
require_once 'db.php';

// Bloqueia acesso de quem não estiver logado OU não for admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_perfil'] ?? '') !== 'admin') {
  header('Location: index.php');
  exit;
}

$id_admin_logado = $_SESSION['usuario_id'];

// Lógica de Ações (Aprovar / Recusar / Excluir)
if (isset($_GET['acao']) && isset($_GET['id'])) {
  $id   = (int) $_GET['id'];
  $acao = $_GET['acao'];

  if ($acao === 'aprovar') {
    $stmt = $pdo->prepare('UPDATE usuarios SET status = "aprovado" WHERE id = :id');
    $stmt->execute(['id' => $id]);
  } elseif ($acao === 'recusar') {
    $stmt = $pdo->prepare('UPDATE usuarios SET status = "recusado" WHERE id = :id');
    $stmt->execute(['id' => $id]);
  } elseif ($acao === 'excluir') {
    // Trava de Segurança: Não permite que o próprio Admin se exclua
    if ($id !== $id_admin_logado) {
      $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
      $stmt->execute(['id' => $id]);
    }
  }

  header('Location: admin_usuarios.php');
  exit;
}

// -------------------------------------------------------------
// LÓGICA DE PAGINAÇÃO E CONSULTAS AO BANCO DE DADOS
// -------------------------------------------------------------
$limite_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina_atual - 1) * $limite_por_pagina;

// 1. Busca solicitações pendentes (todas)
$pendentes = $pdo->query('SELECT * FROM usuarios WHERE status = "pendente" ORDER BY criado_em DESC')->fetchAll();

// 2. Conta total de usuários cadastrados (aprovados ou recusados) para o Card e Paginação
$total_cadastrados_stmt = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE status != "pendente"');
$total_cadastrados = $total_cadastrados_stmt->fetchColumn();
$total_paginas = ceil($total_cadastrados / $limite_por_pagina);

// 3. Busca os usuários cadastrados com limites (Paginação)
$stmt_todos = $pdo->prepare('SELECT * FROM usuarios WHERE status != "pendente" ORDER BY nome ASC LIMIT :inicio, :limite');
$stmt_todos->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt_todos->bindValue(':limite', $limite_por_pagina, PDO::PARAM_INT);
$stmt_todos->execute();
$todos = $stmt_todos->fetchAll();

// Função auxiliar para gerar link do WhatsApp
function gerarLinkWhatsapp($telefone)
{
  $numeroApenas = preg_replace('/\D/', '', $telefone);
  if (strlen($numeroApenas) >= 10 && !str_starts_with($numeroApenas, '55')) {
    $numeroApenas = '55' . $numeroApenas;
  }
  return 'https://wa.me/' . $numeroApenas;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestão de Usuários - Admin PMPR</title>
  <link rel="stylesheet" href="style.css?v=1.0.1">
  <link rel="stylesheet" href="style_auxilio.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    .tabela-admin {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      background: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      font-size: 13px;
      border: 1px solid #cbd5e1;
    }

    .tabela-admin th,
    .tabela-admin td {
      padding: 10px 14px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
    }

    .tabela-admin th {
      background: #0f172a;
      color: #f8fafc;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.5px;
    }

    .btn-acao {
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.2s;
    }

    .btn-aprovar {
      background: #22c55e;
      color: #fff;
    }

    .btn-aprovar:hover {
      background: #16a34a;
    }

    .btn-recusar {
      background: #f59e0b;
      color: #fff;
    }

    .btn-recusar:hover {
      background: #d97706;
    }

    .btn-excluir {
      background: #ef4444;
      color: #fff;
    }

    .btn-excluir:hover {
      background: #dc2626;
    }

    .badge-status {
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
    }

    .status-aprovado {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
    }

    .status-recusado {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
    }

    .link-wpp {
      color: #16a34a;
      text-decoration: none;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .link-wpp:hover {
      text-decoration: underline;
    }

    /* CARD DE CONTADOR E PAGINAÇÃO */
    .card-contador {
      background: #ffffff;
      border-left: 4px solid #2b80c5;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      display: inline-flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
    }

    .card-contador i {
      font-size: 28px;
      color: #2b80c5;
    }

    .card-contador .titulo-card {
      font-size: 12px;
      color: #64748b;
      font-weight: bold;
      text-transform: uppercase;
    }

    .card-contador .valor-card {
      font-size: 22px;
      font-weight: bold;
      color: #0f172a;
    }

    .paginacao {
      display: flex;
      justify-content: center;
      gap: 6px;
      margin-top: 20px;
    }

    .paginacao a,
    .paginacao span {
      padding: 6px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      text-decoration: none;
      color: #334155;
      font-size: 13px;
      background: #fff;
    }

    .paginacao a:hover {
      background-color: #2b80c5;
      color: #fff;
      border-color: #2b80c5;
    }

    .paginacao .ativa {
      background-color: #2b80c5;
      color: #fff;
      border-color: #2b80c5;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <!-- BARRA LATERAL -->
  <?php require_once 'sidebar.php'; ?>

  <div class="main-wrapper">

    <!-- NAV SUPERIOR -->
    <?php require_once 'top-nav.php'; ?>

    <div class="page-banner">
      <i class="fa-solid fa-user-shield"></i> Painel de Gestão de Acessos e Usuários
    </div>

    <div class="content-area">

      <!-- CARD DE TOTAL CADASTRADOS -->
      <div class="card-contador">
        <i class="fa-solid fa-users-gear"></i>
        <div>
          <div class="titulo-card">Usuários Cadastrados</div>
          <div class="valor-card"><?= $total_cadastrados ?></div>
        </div>
      </div>

      <!-- TABELA 1: SOLICITAÇÕES PENDENTES -->
      <div class="card">
        <div class="block-title" style="color: #d97706;">
          <i class="fa-solid fa-clock"></i> Solicitações Pendentes (<?= count($pendentes) ?>)
        </div>

        <?php if (count($pendentes) > 0): ?>
          <table class="tabela-admin">
            <thead>
              <tr>
                <th>Posto / Nome / Documentos</th>
                <th>Unidade / OPM</th>
                <th>Contato / Telefone</th>
                <th>Usuário</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendentes as $u): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars(($u['posto_graduacao'] ?? '') . ' ' . $u['nome']) ?></strong>
                    <div style="font-size: 11px; color: #64748b;">RG: <?= htmlspecialchars($u['rg'] ?? 'N/A') ?> | CPF: <?= htmlspecialchars($u['cpf'] ?? 'N/A') ?></div>
                  </td>
                  <td><i class="fa-solid fa-building-shield" style="color: #64748b;"></i> <?= htmlspecialchars($u['unidade'] ?? 'N/A') ?></td>
                  <td>
                    <?php if (!empty($u['telefone'])): ?>
                      <a href="<?= gerarLinkWhatsapp($u['telefone']) ?>" target="_blank" class="link-wpp">
                        <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($u['telefone']) ?>
                      </a>
                    <?php else: ?>
                      <span style="color: #94a3b8;">Sem telefone</span>
                    <?php endif; ?>
                  </td>
                  <td><code><?= htmlspecialchars($u['usuario']) ?></code></td>
                  <td style="font-size: 11px; color: #64748b;">
                    <?= !empty($u['criado_em']) ? date('d/m/Y H:i', strtotime($u['criado_em'])) : 'N/A' ?>
                  </td>
                  <td style="display: flex; gap: 4px;">
                    <a href="admin_usuarios.php?acao=aprovar&id=<?= $u['id'] ?>" class="btn-acao btn-aprovar"><i class="fa-solid fa-check"></i> Aprovar</a>
                    <a href="admin_usuarios.php?acao=recusar&id=<?= $u['id'] ?>" class="btn-acao btn-recusar" onclick="return confirm('Deseja recusar esta solicitação?')"><i class="fa-solid fa-xmark"></i> Negar</a>
                    <a href="admin_usuarios.php?acao=excluir&id=<?= $u['id'] ?>" class="btn-acao btn-excluir" onclick="return confirm('Tem certeza que deseja EXCLUIR DEFINITIVAMENTE este registro?')"><i class="fa-solid fa-trash"></i> Excluir</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p style="color: #64748b; font-size: 13px; margin-top: 10px;">Nenhuma solicitação pendente no momento.</p>
        <?php endif; ?>
      </div>

      <!-- TABELA 2: USUÁRIOS CADASTRADOS (PAGINADA) -->
      <div class="card" style="margin-top: 25px;">
        <div class="block-title">
          <i class="fa-solid fa-users"></i> Usuários Cadastrados
        </div>

        <table class="tabela-admin">
          <thead>
            <tr>
              <th>Posto / Nome / Documentos</th>
              <th>Unidade / OPM</th>
              <th>Contato</th>
              <th>Usuário</th>
              <th>Criação</th>
              <th>Último Acesso</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($todos) > 0): ?>
              <?php foreach ($todos as $u): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars(($u['posto_graduacao'] ?? '') . ' ' . $u['nome']) ?></strong>
                    <div style="font-size: 11px; color: #64748b;">RG: <?= htmlspecialchars($u['rg'] ?? 'N/A') ?> | CPF: <?= htmlspecialchars($u['cpf'] ?? 'N/A') ?></div>
                  </td>
                  <td><i class="fa-solid fa-building-shield" style="color: #64748b;"></i> <?= htmlspecialchars($u['unidade'] ?? 'N/A') ?></td>
                  <td>
                    <?php if (!empty($u['telefone'])): ?>
                      <a href="<?= gerarLinkWhatsapp($u['telefone']) ?>" target="_blank" class="link-wpp">
                        <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($u['telefone']) ?>
                      </a>
                    <?php else: ?>
                      <span style="color: #94a3b8;">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td><code><?= htmlspecialchars($u['usuario']) ?></code></td>

                  <!-- DATA DE CRIAÇÃO -->
                  <td style="font-size: 11px; color: #64748b;">
                    <?= !empty($u['criado_em']) ? date('d/m/Y H:i', strtotime($u['criado_em'])) : 'N/A' ?>
                  </td>

                  <!-- ÚLTIMO ACESSO -->
                  <td style="font-size: 11px; color: #64748b;">
                    <?= !empty($u['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : '<span style="color:#94a3b8; font-style:italic;">Nunca acessou</span>' ?>
                  </td>

                  <td>
                    <span class="badge-status status-<?= $u['status'] ?>">
                      <?= strtoupper($u['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($u['id'] !== $id_admin_logado): ?>
                      <a href="admin_usuarios.php?acao=excluir&id=<?= $u['id'] ?>" class="btn-acao btn-excluir" onclick="return confirm('ATENÇÃO: Deseja apagar permanentemente o usuário <?= htmlspecialchars($u['nome']) ?>?')">
                        <i class="fa-solid fa-trash"></i> Apagar
                      </a>
                    <?php else: ?>
                      <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Seu Usuário</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; color: #64748b; padding: 15px;">Nenhum usuário cadastrado até o momento.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- BARRA DE PAGINAÇÃO -->
        <?php if ($total_paginas > 1): ?>
          <div class="paginacao">
            <?php if ($pagina_atual > 1): ?>
              <a href="?pagina=<?= $pagina_atual - 1 ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
              <a href="?pagina=<?= $i ?>" class="<?= ($i === $pagina_atual) ? 'ativa' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($pagina_atual < $total_paginas): ?>
              <a href="?pagina=<?= $pagina_atual + 1 ?>">Próximo &raquo;</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>

    <!-- RODAPÉ -->
    <?php require_once 'footer.php'; ?>

  </div>

</body>

</html>