<?php
session_start();
require_once 'db.php';

// Bloqueia acesso de quem não estiver logado OU não for admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_perfil'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

// Lógica de Ação (Aprovar / Negar)
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id   = (int) $_GET['id'];
    $acao = $_GET['acao'];

    if ($acao === 'aprovar') {
        $stmt = $pdo->prepare('UPDATE usuarios SET status = "aprovado" WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } elseif ($acao === 'recusard') {
        $stmt = $pdo->prepare('UPDATE usuarios SET status = "recusado" WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    header('Location: admin_usuarios.php');
    exit;
}

// Busca solicitações pendentes e cadastrados com todas as informações
$pendentes = $pdo->query('SELECT * FROM usuarios WHERE status = "pendente" ORDER BY criado_em DESC')->fetchAll();
$todos     = $pdo->query('SELECT * FROM usuarios WHERE status != "pendente" ORDER BY nome ASC')->fetchAll();

// Função auxiliar para formatar link do WhatsApp
function gerarLinkWhatsapp($telefone) {
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
  <title>Gestão de Usuários - Admin</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    .admin-container { padding: 30px; color: #fff; max-width: 1200px; margin: 0 auto; }
    .tabela-admin { width: 100%; border-collapse: collapse; margin-top: 15px; background: #1e293b; border-radius: 8px; overflow: hidden; font-size: 14px; }
    .tabela-admin th, .tabela-admin td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #334155; }
    .tabela-admin th { background: #0f172a; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
    .btn-aprovar { background: #22c55e; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px; }
    .btn-recusar { background: #ef4444; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .btn-aprovar:hover { background: #16a34a; }
    .btn-recusar:hover { background: #dc2626; }
    .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-aprovado { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.4); }
    .status-recusado { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
    .link-wpp { color: #22c55e; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; }
    .link-wpp:hover { text-decoration: underline; }
  </style>
</head>
<body style="display: flex; min-height: 100vh; background: #0f172a;">

  <div class="admin-container" style="flex: 1;">
    <h2><i class="fa-solid fa-user-shield" style="color: #3b82f6;"></i> Painel de Aprovação de Acessos</h2>
    <p style="color: #94a3b8; font-size: 14px; margin-top: 4px;">Gerencie as solicitações e informações de contato de todos os usuários do sistema.</p>

    <!-- TABELA DE PENDENTES -->
    <h3 style="margin-top: 30px; color: #f59e0b; font-size: 16px;"><i class="fa-solid fa-clock"></i> Solicitações Pendentes (<?= count($pendentes) ?>)</h3>
    <?php if (count($pendentes) > 0): ?>
      <table class="tabela-admin">
        <thead>
          <tr>
            <th>Posto / Nome</th>
            <th>Unidade / OPM</th>
            <th>Contato / Telefone</th>
            <th>Usuário</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendentes as $u): ?>
            <tr>
              <td>
                <strong style="color: #f8fafc;"><?= htmlspecialchars(($u['posto_graduacao'] ?? '') . ' ' . $u['nome']) ?></strong>
                <div style="font-size: 11px; color: #64748b;">RG: <?= htmlspecialchars($u['rg'] ?? 'N/A') ?> | CPF: <?= htmlspecialchars($u['cpf'] ?? 'N/A') ?></div>
              </td>
              <td><i class="fa-solid fa-building-shield" style="color: #94a3b8;"></i> <?= htmlspecialchars($u['unidade'] ?? 'Não informada') ?></td>
              <td>
                <?php if (!empty($u['telefone'])): ?>
                  <a href="<?= gerarLinkWhatsapp($u['telefone']) ?>" target="_blank" class="link-wpp">
                    <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($u['telefone']) ?>
                  </a>
                <?php else: ?>
                  <span style="color: #64748b;">Sem telefone</span>
                <?php endif; ?>
              </td>
              <td><code style="background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #cbd5e1;"><?= htmlspecialchars($u['usuario']) ?></code></td>
              <td style="font-size: 12px; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
              <td>
                <a href="admin_usuarios.php?acao=aprovar&id=<?= $u['id'] ?>" class="btn-aprovar"><i class="fa-solid fa-check"></i> Aprovar</a>
                <a href="admin_usuarios.php?acao=recusar&id=<?= $u['id'] ?>" class="btn-recusar" onclick="return confirm('Deseja recusar este acesso?')"><i class="fa-solid fa-xmark"></i> Negar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="color: #64748b; margin-top: 10px; font-size: 13px;">Nenhuma solicitação pendente no momento.</p>
    <?php endif; ?>

    <!-- TABELA DE USUÁRIOS JÁ AVALIADOS -->
    <h3 style="margin-top: 40px; color: #cbd5e1; font-size: 16px;"><i class="fa-solid fa-users"></i> Usuários Cadastrados</h3>
    <table class="tabela-admin">
      <thead>
        <tr>
          <th>Posto / Nome</th>
          <th>Unidade / OPM</th>
          <th>Contato / Telefone</th>
          <th>Usuário</th>
          <th>Perfil</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todos as $u): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars(($u['posto_graduacao'] ?? '') . ' ' . $u['nome']) ?></strong>
              <div style="font-size: 11px; color: #64748b;">RG: <?= htmlspecialchars($u['rg'] ?? 'N/A') ?></div>
            </td>
            <td><i class="fa-solid fa-building-shield" style="color: #94a3b8;"></i> <?= htmlspecialchars($u['unidade'] ?? 'N/A') ?></td>
            <td>
              <?php if (!empty($u['telefone'])): ?>
                <a href="<?= gerarLinkWhatsapp($u['telefone']) ?>" target="_blank" class="link-wpp">
                  <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($u['telefone']) ?>
                </a>
              <?php else: ?>
                <span style="color: #64748b;">N/A</span>
              <?php endif; ?>
            </td>
            <td><code><?= htmlspecialchars($u['usuario']) ?></code></td>
            <td><span style="font-size: 12px; font-weight: 600; color: #94a3b8;"><?= strtoupper($u['perfil']) ?></span></td>
            <td>
              <span class="badge-status status-<?= $u['status'] ?>">
                <?= strtoupper($u['status']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <br><br>
    <a href="index.php" style="color: #3b82f6; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
      <i class="fa-solid fa-arrow-left"></i> Voltar ao Sistema de Oitivas
    </a>
  </div>

</body>
</html>