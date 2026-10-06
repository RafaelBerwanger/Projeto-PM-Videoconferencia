<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// 1. PUXA DADOS DO USUÁRIO
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$user = $stmt->fetch();

// 2. CONSULTA PROCEDIMENTOS CADASTRADOS PELO ENCARREGADO
$sql = "SELECT p.*, u.nome AS encarregado_nome 
        FROM procedimentos p 
        LEFT JOIN usuarios u ON p.id_usuario = u.id 
        WHERE p.id_usuario = :id_user 
        ORDER BY p.id DESC";
$stmtProc = $pdo->prepare($sql);
$stmtProc->execute(['id_user' => $id_usuario]);
$procedimentos = $stmtProc->fetchAll();

// 3. CÁLCULO DAS ESTATÍSTICAS DOS CARDS
$total_feitos = count($procedimentos);
$total_fatd = 0;
$total_sindicancia = 0;
$total_ipm = 0;
$total_conselhos = 0;
$total_it = 0;

foreach ($procedimentos as $p) {
    $tipo = strtoupper($p['tipo']);
    if ($tipo === 'FATD') $total_fatd++;
    elseif ($tipo === 'SINDICÂNCIA') $total_sindicancia++;
    elseif ($tipo === 'IPM') $total_ipm++;
    elseif ($tipo === 'CD' || $tipo === 'CJ') $total_conselhos++;
    elseif ($tipo === 'IT') $total_it++;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Procedimentos - Intranet PMPR</title>
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

        <div class="page-banner">
            <i class="fa-solid fa-folder-open"></i> Gestão de Procedimentos Administrativos
        </div>

        <div class="content-area">

            <!-- CARDS DE DASHBOARD COM VALORES DINÂMICOS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $total_feitos ?></h3>
                        <p>Total Feitos</p>
                    </div>
                    <i class="fa-solid fa-folder stat-icon" style="color: #2563eb;"></i>
                </div>

                <div class="stat-card fatd">
                    <div class="stat-info">
                        <h3><?= $total_fatd ?></h3>
                        <p>FATD</p>
                    </div>
                    <i class="fa-solid fa-file-shield stat-icon" style="color: #0284c7;"></i>
                </div>

                <div class="stat-card sindicancia">
                    <div class="stat-info">
                        <h3><?= $total_sindicancia ?></h3>
                        <p>Sindicâncias</p>
                    </div>
                    <i class="fa-solid fa-magnifying-glass-chart stat-icon" style="color: #d97706;"></i>
                </div>

                <div class="stat-card ipm">
                    <div class="stat-info">
                        <h3><?= $total_ipm ?></h3>
                        <p>IPM</p>
                    </div>
                    <i class="fa-solid fa-gavel stat-icon" style="color: #dc2626;"></i>
                </div>

                <div class="stat-card conselhos">
                    <div class="stat-info">
                        <h3><?= $total_conselhos ?></h3>
                        <p>CD / CJ</p>
                    </div>
                    <i class="fa-solid fa-scale-balanced stat-icon" style="color: #7c3aed;"></i>
                </div>

                <div class="stat-card it">
                    <div class="stat-info">
                        <h3><?= $total_it ?></h3>
                        <p>Inq. Técnico</p>
                    </div>
                    <i class="fa-solid fa-screwdriver-wrench stat-icon" style="color: #059669;"></i>
                </div>
            </div>

            <!-- BARRA DE AÇÕES E FILTROS -->
            <div class="card" style="padding: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; gap: 10px; flex: 1; min-width: 280px;">
                        <input type="text" id="buscaProcedimento" class="form-control" placeholder="Buscar por nº, portaria..." onkeyup="filtrarTabela()">
                        <select id="filtroTipo" class="form-control" style="max-width: 180px;" onchange="filtrarTabela()">
                            <option value="">Todos os Tipos</option>
                            <option value="FATD">FATD</option>
                            <option value="SINDICÂNCIA">Sindicância</option>
                            <option value="IPM">IPM</option>
                            <option value="CD">CD</option>
                            <option value="CJ">CJ</option>
                            <option value="IT">Inquérito Técnico (IT)</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-green" onclick="abrirModalNovo()">
                        <i class="fa-solid fa-plus-circle"></i> Novo Procedimento
                    </button>
                </div>
            </div>

            <!-- TABELA DINÂMICA DE PROCEDIMENTOS -->
            <div class="table-responsive">
                <table class="custom-table" id="tabelaProcedimentos">
                    <thead>
                        <tr>
                            <th>Procedimento</th>
                            <th>Nº / Portaria</th>
                            <th>Encarregado</th>
                            <th>Instauração</th>
                            <th>Prazo Final</th>
                            <th>Status</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($procedimentos)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">
                                    <i>Nenhum procedimento cadastrado até o momento. Clique em <b>"Novo Procedimento"</b> para começar.</i>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($procedimentos as $p): ?>
                                <?php
                                    // Formatação de datas para exibição brasileira
                                    $dataInst = date('d/m/Y', strtotime($p['data_instauracao']));
                                    $dataVenc = date('d/m/Y', strtotime($p['data_vencimento']));

                                    // Lógica visual do status do prazo
                                    $hoje = new DateTime();
                                    $vencimento = new DateTime($p['data_vencimento']);
                                    $diferenca = $hoje->diff($vencimento);

                                    $badgeClasse = 'badge-andamento';
                                    if ($hoje > $vencimento && $p['status'] !== 'Concluído') {
                                        $badgeClasse = 'badge-vencido';
                                        $textoStatus = 'Vencido';
                                    } elseif ($diferenca->days <= 5 && $hoje <= $vencimento && $p['status'] !== 'Concluído') {
                                        $badgeClasse = 'badge-prazo';
                                        $textoStatus = 'Prazo Próximo (' . $diferenca->days . ' dias)';
                                    } else {
                                        $textoStatus = htmlspecialchars($p['status']);
                                    }
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['tipo']) ?></strong></td>
                                    <td>
                                        <b><?= htmlspecialchars($p['numero']) ?></b>
                                        <?php if (!empty($p['portaria'])): ?>
                                            <br><small style="color: #64748b;"><?= htmlspecialchars($p['portaria']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['encarregado_nome'] ?? $user['nome']) ?></td>
                                    <td><?= $dataInst ?></td>
                                    <td><b><?= $dataVenc ?></b></td>
                                    <td><span class="badge-status <?= $badgeClasse ?>"><?= $textoStatus ?></span></td>
                                    <td style="text-align: center;">
                                        <button class="btn-acao" style="background:#2563eb; color:#fff;" title="Gerenciar Procedimento" onclick="location.href='ver_procedimento.php?id=<?= $p['id'] ?>'">
                                            <i class="fa-solid fa-folder-open"></i> Gerenciar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <?php require_once 'footer.php'; ?>

    </div>

    <!-- MODAL DE CADASTRO -->
    <div class="modal-overlay" id="modalProcedimento">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fa-solid fa-folder-plus"></i> Novo Procedimento Administrativo</h3>
                <i class="fa-solid fa-xmark modal-close" onclick="fecharModalNovo()"></i>
            </div>
            <form id="formNovoProcedimento">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group col-6">
                            <label for="proc_tipo">Tipo do Procedimento: <span class="required">*</span></label>
                            <select id="proc_tipo" name="tipo" class="form-control" required onchange="calcularPrazoSugerido()">
                                <option value="FATD">FATD (Formulário de Apuração)</option>
                                <option value="SINDICÂNCIA">Sindicância</option>
                                <option value="IPM">IPM (Inquérito Policial Militar)</option>
                                <option value="CD">CD (Conselho de Disciplina)</option>
                                <option value="CJ">CJ (Conselho de Justificação)</option>
                                <option value="IT">IT (Inquérito Técnico - Viatura/Avarias)</option>
                                <option value="APFD">APFD (Auto de Prisão em Flagrante)</option>
                                <option value="ADL">ADL (Apuração Disciplinar Licenciamento)</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="proc_num">Número / Ano: <span class="required">*</span></label>
                            <input type="text" id="proc_num" name="numero" class="form-control" placeholder="Ex: 042/2026" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="proc_portaria">Portaria / Documento de Origem:</label>
                            <input type="text" id="proc_portaria" name="portaria" class="form-control" placeholder="Ex: Portaria nº 123/2026-6º BPM / Portaria de Instauração">
                        </div>

                        <div class="form-group col-6">
                            <label for="proc_data_inst">Data de Instauração / Portaria: <span class="required">*</span></label>
                            <input type="date" id="proc_data_inst" name="data_instauracao" class="form-control" required onchange="calcularPrazoSugerido()">
                        </div>

                        <div class="form-group col-6">
                            <label for="proc_prazo_dias">Prazo Inicial (Dias): <span class="required">*</span></label>
                            <input type="number" id="proc_prazo_dias" name="prazo_dias" class="form-control" value="10" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="proc_sintese">Síntese dos Fatos / Objeto de Apuração:</label>
                            <textarea id="proc_sintese" name="sintese" class="form-control" rows="3" placeholder="Resumo dos fatos a serem apurados na portaria..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModalNovo()" style="background:#64748b; color:#fff; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Cancelar</button>
                    <button type="submit" class="btn btn-green"><i class="fa-solid fa-save"></i> Salvar Procedimento</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNovo() {
            document.getElementById('modalProcedimento').style.display = 'flex';
            if (!document.getElementById('proc_data_inst').value) {
                document.getElementById('proc_data_inst').value = new Date().toISOString().split('T')[0];
            }
            calcularPrazoSugerido();
        }

        function fecharModalNovo() {
            document.getElementById('modalProcedimento').style.display = 'none';
        }

        function calcularPrazoSugerido() {
            const tipo = document.getElementById('proc_tipo').value;
            const campoDias = document.getElementById('proc_prazo_dias');

            if (tipo === 'FATD') campoDias.value = 10;
            else if (tipo === 'SINDICÂNCIA') campoDias.value = 30;
            else if (tipo === 'IPM') campoDias.value = 40;
            else if (tipo === 'CD' || tipo === 'CJ') campoDias.value = 30;
            else if (tipo === 'IT') campoDias.value = 30;
            else campoDias.value = 20;
        }

        function filtrarTabela() {
            const busca = document.getElementById('buscaProcedimento').value.toLowerCase();
            const tipo = document.getElementById('filtroTipo').value.toLowerCase();
            const linhas = document.querySelectorAll('#tabelaProcedimentos tbody tr');

            linhas.forEach(linha => {
                const textoLinha = linha.innerText.toLowerCase();
                const correspondeBusca = textoLinha.includes(busca);
                const correspondeTipo = (tipo === '' || textoLinha.includes(tipo));

                if (correspondeBusca && correspondeTipo) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        }

        // ENVIO AJAX DO FORMULÁRIO DO MODAL
        document.getElementById('formNovoProcedimento').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('button[type="submit"]');
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gravando...';

            try {
                const resposta = await fetch('salvar_procedimento.php', {
                    method: 'POST',
                    body: formData
                });

                const dados = await resposta.json();

                if (dados.sucesso) {
                    fecharModalNovo();
                    // Recarrega a página para listar o novo procedimento salvo
                    window.location.reload();
                } else {
                    alert('Erro: ' + dados.erro);
                }
            } catch (erro) {
                console.error('Erro na requisição:', erro);
                alert('Falha de comunicação com o servidor.');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-save"></i> Salvar Procedimento';
            }
        });
    </script>

</body>

</html>