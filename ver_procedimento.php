<?php
require_once 'sessao.php';

$user       = exigirLogin();
exigirModuloProcedimentos();
$id_usuario = (int) $user['id'];
$id_proc    = (int) ($_GET['id'] ?? 0); // ID do procedimento vindo da URL

// Busca o procedimento garantindo que pertence ao usuário logado
$stmt = $pdo->prepare('SELECT * FROM procedimentos WHERE id = :id AND id_usuario = :id_user LIMIT 1');
$stmt->execute(['id' => $id_proc, 'id_user' => $id_usuario]);
$proc = $stmt->fetch();

if (!$proc) {
    header('Location: procedimentos.php');
    exit;
}

// Timeline de atos
$stmtAtos = $pdo->prepare('SELECT * FROM procedimento_atos WHERE id_procedimento = :id ORDER BY data_ato ASC, id ASC');
$stmtAtos->execute(['id' => $id_proc]);
$atos = $stmtAtos->fetchAll();

// Situação do prazo
$hoje       = new DateTime('today');
$vencimento = new DateTime($proc['data_vencimento']);
$diasRestantes = (int) $hoje->diff($vencimento)->format('%r%a');

if ($proc['status'] === 'Concluído') {
    $badgeStatus = 'badge-concluido';
    $textoPrazo  = 'Concluído';
} elseif ($diasRestantes < 0) {
    $badgeStatus = 'badge-vencido';
    $textoPrazo  = 'Vencido há ' . abs($diasRestantes) . (abs($diasRestantes) === 1 ? ' dia' : ' dias');
} elseif ($diasRestantes <= 5) {
    $badgeStatus = 'badge-prazo';
    $textoPrazo  = $diasRestantes === 0 ? 'Vence hoje' : 'Faltam ' . $diasRestantes . ($diasRestantes === 1 ? ' dia' : ' dias');
} else {
    $badgeStatus = 'badge-andamento';
    $textoPrazo  = 'Faltam ' . $diasRestantes . ' dias';
}

$tituloProc = $proc['tipo'] . ' Nº ' . $proc['numero'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossiê <?= e($tituloProc) ?> - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos da Intranet -->
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link rel="stylesheet" href="style_auxilio.css">
    <link rel="stylesheet" href="style_procedimentos.css?v=1.0.1">
</head>

<body>

    <?php require_once 'sidebar.php'; ?>

    <div class="main-wrapper">

        <?php require_once 'top-nav.php'; ?>

        <!-- BANNER DE IDENTIFICAÇÃO DO PROCEDIMENTO -->
        <div class="page-banner" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <i class="fa-solid fa-folder-open"></i> <?= e($tituloProc) ?>
                <span class="badge-status <?= $badgeStatus ?>" style="margin-left: 10px;"><?= e($proc['status']) ?></span>
            </div>
            <a href="procedimentos.php" class="btn btn-secondary" style="background: #475569; color: #fff; text-decoration: none; font-size: 13px; padding: 6px 12px; border-radius: 4px;">
                <i class="fa-solid fa-arrow-left"></i> Voltar à Lista
            </a>
        </div>

        <div class="content-area">

            <!-- CABEÇALHO NAVEGÁVEL POR ABAS -->
            <div class="tabs-header">
                <button type="button" class="tab-btn active" onclick="alternarAba('aba_visao_geral', this)">
                    <i class="fa-solid fa-circle-info"></i> Visão Geral & Capa
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_envolvidos', this)">
                    <i class="fa-solid fa-users"></i> Matriz de Envolvidos (1)
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_cronograma', this)">
                    <i class="fa-solid fa-calendar-days"></i> Cronograma & Prazos
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_timeline', this)">
                    <i class="fa-solid fa-timeline"></i> Timeline de Atos (<?= count($atos) ?>)
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_gerador', this)">
                    <i class="fa-solid fa-file-export"></i> Gerador de Peças
                </button>
            </div>

            <!-- ABA 1: VISÃO GERAL -->
            <div id="aba_visao_geral" class="tab-content active">
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-file-contract"></i> Resumo da Portaria / Objeto
                    </div>
                    <div class="form-grid">
                        <div class="form-group col-3">
                            <label><strong>Tipo:</strong></label>
                            <p><?= e($proc['tipo']) ?></p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Número / Ano:</strong></label>
                            <p><?= e($proc['numero']) ?></p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Data de Instauração:</strong></label>
                            <p><?= date('d/m/Y', strtotime($proc['data_instauracao'])) ?></p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Vencimento Atual:</strong></label>
                            <p><span class="badge-status <?= $badgeStatus ?>"><?= $vencimento->format('d/m/Y') ?> (<?= e($textoPrazo) ?>)</span></p>
                        </div>

                        <?php if (!empty($proc['portaria'])): ?>
                            <div class="form-group col-12">
                                <label><strong>Portaria / Documento de Origem:</strong></label>
                                <p><?= e($proc['portaria']) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="form-group col-12">
                            <label><strong>Síntese dos Fatos / Objeto de Apuração:</strong></label>
                            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px; white-space: pre-line;"><?= $proc['sintese'] !== '' && $proc['sintese'] !== null ? e($proc['sintese']) : '<i style="color:#94a3b8;">Não informada.</i>' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ABA 2: MATRIZ DE ENVOLVIDOS -->
            <div id="aba_envolvidos" class="tab-content">
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 8px;">
                        <div class="block-title" style="margin-bottom:0;">
                            <i class="fa-solid fa-users"></i> Pessoas Vinculadas ao Procedimento
                        </div>
                        <button type="button" class="btn btn-green" disabled title="Funcionalidade em desenvolvimento" style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fa-solid fa-user-plus"></i> Adicionar Envolvido (em breve)
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Papel Legal</th>
                                    <th>Nome Completo</th>
                                    <th>Posto/Grad / Profissão</th>
                                    <th>CPF/RG</th>
                                    <th>OPM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge-status badge-andamento">Encarregado</span></td>
                                    <td><?= e($user['nome']) ?></td>
                                    <td><?= e($user['posto_graduacao']) ?></td>
                                    <td><?= e($user['cpf']) ?><?= !empty($user['rg']) ? ' / RG ' . e($user['rg']) : '' ?></td>
                                    <td><?= e($user['unidade']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ABA 3: CRONOGRAMA & CALENDÁRIO -->
            <div id="aba_cronograma" class="tab-content">
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-clock-rotate-left"></i> Controle de Prazos Legais (<?= e($proc['tipo']) ?>)
                    </div>
                    <div style="padding: 10px; background: #e0f2fe; border-left: 4px solid #0284c7; border-radius: 4px; color: #0369a1; font-size: 13px; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-info"></i> <strong>Prazo cadastrado:</strong> <?= (int) $proc['prazo_dias'] ?> dias corridos a partir de <?= date('d/m/Y', strtotime($proc['data_instauracao'])) ?>, com vencimento em <strong><?= $vencimento->format('d/m/Y') ?></strong> (<?= e($textoPrazo) ?>).
                    </div>
                    <div id="calendario_procedimento" style="background: #fff; padding: 20px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 32px; color: #94a3b8; margin-bottom: 10px;"></i>
                        <p style="margin:0; color:#64748b;">Calendário visual de oitivas e vencimentos (em desenvolvimento).</p>
                    </div>
                </div>
            </div>

            <!-- ABA 4: TIMELINE DE ATOS -->
            <div id="aba_timeline" class="tab-content">
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-timeline"></i> Sequência Cronológica de Documentos / Atos
                    </div>

                    <div class="timeline-container">
                        <?php if (empty($atos)): ?>
                            <p style="color:#64748b; font-size: 13px;">Nenhum ato registrado.</p>
                        <?php endif; ?>
                        <?php foreach ($atos as $ato): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon"><i class="fa-solid fa-check"></i></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title"><?= e($ato['titulo']) ?></span>
                                        <span class="timeline-date"><?= date('d/m/Y', strtotime($ato['data_ato'])) ?></span>
                                    </div>
                                    <?php if (!empty($ato['descricao'])): ?>
                                        <p style="margin:0; font-size: 12px; color: #64748b;"><?= e($ato['descricao']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ABA 5: GERADOR DE PEÇAS -->
            <div id="aba_gerador" class="tab-content">
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-file-export"></i> Atalhos para Geração de Peças
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-4">
                            <a href="presencial.php?proc_id=<?= (int) $proc['id'] ?>" class="btn btn-green" style="width: 100%; justify-content: center; padding: 12px; text-decoration: none;">
                                <i class="fa-solid fa-file-pen"></i> Nova Oitiva Presencial
                            </a>
                        </div>
                        <div class="form-group col-4">
                            <button type="button" class="btn" disabled title="Funcionalidade em desenvolvimento" style="width: 100%; justify-content: center; padding: 12px; background: #0284c7; color:#fff; opacity: 0.6; cursor: not-allowed;">
                                <i class="fa-solid fa-envelope-open-text"></i> Notificação de Citação (em breve)
                            </button>
                        </div>
                        <div class="form-group col-4">
                            <button type="button" class="btn" disabled title="Funcionalidade em desenvolvimento" style="width: 100%; justify-content: center; padding: 12px; background: #7c3aed; color:#fff; opacity: 0.6; cursor: not-allowed;">
                                <i class="fa-solid fa-clock-rotate-left"></i> Solicitar Prorrogação (em breve)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php require_once 'footer.php'; ?>

    </div>

    <!-- SCRIPT DE ALTERNÂNCIA DAS ABAS -->
    <script>
        function alternarAba(idAba, elementoBotao) {
            document.querySelectorAll('.tab-content').forEach(aba => aba.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            document.getElementById(idAba).classList.add('active');
            elementoBotao.classList.add('active');
        }
    </script>

</body>

</html>
