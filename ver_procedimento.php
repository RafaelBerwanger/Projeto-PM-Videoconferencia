<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_proc = $_GET['id'] ?? 1; // ID do procedimento vindo da URL

// Puxa dados do usuário logado
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossiê do Procedimento - Intranet PMPR</title>
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
        <div class="page-banner" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <i class="fa-solid fa-folder-open"></i> FATD Nº 001/2026 - 6º BPM
                <span class="badge-status badge-andamento" style="margin-left: 10px;">Em Andamento</span>
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
                    <i class="fa-solid fa-users"></i> Matriz de Envolvidos (0)
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_cronograma', this)">
                    <i class="fa-solid fa-calendar-days"></i> Cronograma & Prazos
                </button>
                <button type="button" class="tab-btn" onclick="alternarAba('aba_timeline', this)">
                    <i class="fa-solid fa-timeline"></i> Timeline de Atos (0)
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
                            <p>FATD</p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Número / Ano:</strong></label>
                            <p>001/2026 - 6º BPM</p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Data de Instauração:</strong></label>
                            <p>01/10/2026</p>
                        </div>
                        <div class="form-group col-3">
                            <label><strong>Vencimento Atual:</strong></label>
                            <p><span class="badge-status badge-prazo">11/10/2026 (Faltam 5 dias)</span></p>
                        </div>

                        <div class="form-group col-12">
                            <label><strong>Síntese dos Fatos / Objeto de Apuração:</strong></label>
                            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px;">
                                Apurar responsabilidade disciplinar acerca do descumprimento de horário de expediente no dia 28/09/2026 por parte de praça pertencente à 1ª Cia.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ABA 2: MATRIZ DE ENVOLVIDOS -->
            <div id="aba_envolvidos" class="tab-content">
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div class="block-title" style="margin-bottom:0;">
                            <i class="fa-solid fa-users"></i> Pessoas Vinculadas ao Procedimento
                        </div>
                        <button type="button" class="btn btn-green" onclick="alert('Formulário de cadastro de envolvido...')">
                            <i class="fa-solid fa-user-plus"></i> Adicionar Envolvido
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
                                    <th>Contato</th>
                                    <th style="text-align: center;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge-status badge-andamento">Encarregado</span></td>
                                    <td><?= htmlspecialchars($user['nome'] ?? 'Cap. Encarregado') ?></td>
                                    <td><?= htmlspecialchars($user['posto_graduacao'] ?? 'Capitão') ?></td>
                                    <td><?= htmlspecialchars($user['cpf'] ?? '000.000.000-00') ?></td>
                                    <td>-(OPM)-</td>
                                    <td style="text-align: center;">-</td>
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
                        <i class="fa-solid fa-clock-history"></i> Controle de Prazos Legais (FATD)
                    </div>
                    <div style="padding: 10px; background: #e0f2fe; border-left: 4px solid #0284c7; border-radius: 4px; color: #0369a1; font-size: 13px; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-info"></i> <strong>Prazo Regulamentar:</strong> 10 dias corridos conforme Portaria/Regulamento PMPR.
                    </div>
                    <!-- Área onde inseriremos a biblioteca do Calendário Interativo -->
                    <div id="calendario_procedimento" style="background: #fff; padding: 20px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 32px; color: #94a3b8; margin-bottom: 10px;"></i>
                        <p style="margin:0; color:#64748b;">Calendário visual e agendamentos de oitivas e vencimentos.</p>
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
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-title">Portaria de Instauração Nº 001/2026</span>
                                    <span class="timeline-date">01/10/2026</span>
                                </div>
                                <p style="margin:0; font-size: 12px; color: #64748b;">Instauração do feito e delegação de autoridade nomeante.</p>
                            </div>
                        </div>
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
                            <button type="button" class="btn btn-green" style="width: 100%; justify-content: center; padding: 12px;" onclick="location.href='presencial.php?proc_id=1'">
                                <i class="fa-solid fa-file-pen"></i> Nova Oitiva Presencial
                            </button>
                        </div>
                        <div class="form-group col-4">
                            <button type="button" class="btn" style="width: 100%; justify-content: center; padding: 12px; background: #0284c7; color:#fff;" onclick="alert('Notificação de Citação...')">
                                <i class="fa-solid fa-envelope-open-text"></i> Notificação de Citação
                            </button>
                        </div>
                        <div class="form-group col-4">
                            <button type="button" class="btn" style="width: 100%; justify-content: center; padding: 12px; background: #7c3aed; color:#fff;" onclick="alert('Pedido de Prorrogação...')">
                                <i class="fa-solid fa-clock-rotate-left"></i> Solicitar Prorrogação
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
            // Oculta todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(aba => {
                aba.classList.remove('active');
            });

            // Desmarca todos os botões
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Exibe a aba clicada e ativa o botão
            document.getElementById(idAba).classList.add('active');
            elementoBotao.classList.add('active');
        }
    </script>

</body>

</html>