<?php
require_once 'sessao.php';
require_once 'listas.php';

$user = exigirLogin();

$valPostoEncarregado   = $user['posto_graduacao'] ?? '';
$valUnidadeEncarregado = $user['unidade'] ?? '';

// Quando aberto a partir do dossiê (?proc_id=), já preenche tipo e número do procedimento
$procTipo   = '';
$procNumero = '';
if (!empty($_GET['proc_id'])) {
    $stmt = $pdo->prepare('SELECT tipo, numero FROM procedimentos WHERE id = :id AND id_usuario = :u LIMIT 1');
    $stmt->execute(['id' => (int) $_GET['proc_id'], 'u' => (int) $user['id']]);
    if ($proc = $stmt->fetch()) {
        $mapaTipos  = ['CD' => 'CD/CJ', 'CJ' => 'CD/CJ', 'IT' => 'Inquérito Técnico'];
        $procTipo   = $mapaTipos[$proc['tipo']] ?? $proc['tipo'];
        $procNumero = $proc['numero'];
    }
}

const LISTA_PROCEDIMENTOS = ['' => [
    'IPM' => 'IPM', 'FATD' => 'FATD', 'SINDICÂNCIA' => 'SINDICÂNCIA', 'APFD' => 'APFD',
    'CD/CJ' => 'CD/CJ', 'ADL' => 'ADL', 'Inquérito Técnico' => 'IT',
]];
const LISTA_QUADROS = ['' => [
    'QOPM' => 'QOPM', 'QOEM PM' => 'QOEM PM', 'QOE PM' => 'QOE PM', 'QP PM' => 'QP PM', 'PM' => 'Sem quadro',
]];

// Quadro sugerido conforme o posto do encarregado (oficial x praça)
$ehOficial = in_array($valPostoEncarregado, array_keys(LISTA_POSTOS['Oficiais Superiores'] + LISTA_POSTOS['Oficiais Intermediários e Subalternos']), true);
$quadroSugerido = $ehOficial ? 'QOPM' : 'QP PM';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auxiliar de Oitivas Escritas - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Ficheiros CSS da Intranet PMPR -->
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link rel="stylesheet" href="style_auxilio.css">

    <!-- Bibliotecas JS e Scripts do Sistema -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>

    <script src="./functionsPresencial.js?v=2.3"></script>
    <script src="./carregaCidadesIbge.js?v=2.0"></script>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>

<body>

    <!-- Inclui a barra lateral centralizada -->
    <?php require_once 'sidebar.php'; ?>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-wrapper">

        <!-- NAV SUPERIOR -->
        <?php require_once 'top-nav.php'; ?>

        <!-- BANNER DE TÍTULO DA PÁGINA -->
        <div class="page-banner">
            <i class="fa-solid fa-file-pen"></i> Auxiliar de Oitivas Escritas
        </div>

        <div class="content-area">

            <form id="formOitiva" onsubmit="return false;">

                <!-- DADOS DO ENCARREGADO -->
                <div class="card" id="dados_encarregado">
                    <div class="block-title">
                        <i class="fa-solid fa-user-gear"></i> Dados do Encarregado
                    </div>

                    <div class="form-grid">
                        <!-- Nome Encarregado (Puxado do BD) -->
                        <div class="form-group col-6">
                            <label for="entnome_enc">Nome:</label>
                            <input id="entnome_enc" type="text" class="form-control" placeholder="Nome do Encarregado" value="<?= e($user['nome'] ?? '') ?>">
                        </div>

                        <!-- CPF Encarregado (Puxado do BD) -->
                        <div class="form-group col-3">
                            <label for="entcni_enc"
                                title="Na certidão, os 3 primeiros dígitos serão ocultados, bem como os dois últimos (ex: XXX.123.123-XX)">CPF/CIN:</label>
                            <input id="entcni_enc" type="text" class="form-control" maxlength="14"
                                placeholder="000.000.000-00" value="<?= e($user['cpf'] ?? '') ?>" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <!-- Posto/Graduação Encarregado -->
                        <div class="form-group col-3">
                            <label for="ent_posto">Posto/Graduação:</label>
                            <select id="ent_posto" class="form-control">
                                <?= renderOpcoes(LISTA_POSTOS, $valPostoEncarregado) ?>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="quadro_enc">Quadro:</label>
                            <select id="quadro_enc" class="form-control">
                                <?= renderOpcoes(LISTA_QUADROS, $quadroSugerido) ?>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="ent_proc">Procedimento:</label>
                            <select id="ent_proc" class="form-control">
                                <?= renderOpcoes(LISTA_PROCEDIMENTOS, $procTipo ?: 'IPM') ?>
                            </select>
                        </div>

                        <div class="form-group col-2">
                            <label for="ent_num_proc">Número:</label>
                            <input id="ent_num_proc" class="form-control" placeholder="Número do Procedimento" value="<?= e($procNumero) ?>">
                        </div>

                        <div class="form-group col-2">
                            <label for="crpm">CRPM:</label>
                            <select name="crpm" id="crpm" class="form-control">
                                <option value="1º COMANDO REGIONAL">1º</option>
                                <option value="2º COMANDO REGIONAL">2º</option>
                                <option value="3º COMANDO REGIONAL">3º</option>
                                <option value="4º COMANDO REGIONAL">4º</option>
                                <option value="5º COMANDO REGIONAL" selected>5º</option>
                                <option value="6º COMANDO REGIONAL">6º</option>
                                <option value="7º COMANDO REGIONAL">7º</option>
                                <option value="COMANDO DE MISSÕES ESPECIAIS">CME</option>
                                <option value="COMANDO DO POLICIAMENTO ESPECIALIZADO">CPE</option>
                            </select>
                        </div>

                        <div class="form-group col-2">
                            <label for="opm">OPM:</label>
                            <select name="opm" id="opm" class="form-control">
                                <?= renderOpcoes(LISTA_UNIDADES, $valUnidadeEncarregado, 'Selecione a Unidade...') ?>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <span id="escriba" class="link-escriba" onclick="insereEscriba()">
                                <i class="fa-solid fa-user-plus"></i> <u>Inserir Escrivão</u>
                            </span>

                            <div id="abre_escriba" class="escriba-container" style="display:none;">
                                <div class="form-grid">
                                    <div class="form-group col-6">
                                        <label for="entnome_esc">Nome:</label>
                                        <input id="entnome_esc" type="text" class="form-control">
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="entcin_esc"
                                            title="Na certidão, os 3 primeiros dígitos serão ocultados, bem como os dois últimos (ex: XXX.123.123-XX)">CPF/CIN:</label>
                                        <input id="entcin_esc" type="text" class="form-control" maxlength="14"
                                            placeholder="000.000.000-00" onkeyup="aplicarMascaraCPF(this)">
                                    </div>

                                    <div class="form-group col-2">
                                        <label for="ent_posto_esc">Posto/Graduação:</label>
                                        <select id="ent_posto_esc" class="form-control">
                                            <?= renderOpcoes(LISTA_POSTOS) ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-2">
                                        <label for="quadro_esc">Quadro:</label>
                                        <select id="quadro_esc" class="form-control">
                                            <?= renderOpcoes(LISTA_QUADROS, 'QOEM PM') ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO 2: DADOS DO DEPOENTE -->
                <div class="card" id="dados_depoente">
                    <div class="block-title">
                        <i class="fa-solid fa-user"></i> Dados do Depoente
                    </div>

                    <div class="form-grid">

                        <div class="form-group col-6">
                            <label for="condicao_oitiva">Tipo do Depoente / Qualificação: <span class="required">*</span></label>
                            <select id="condicao_oitiva" name="ent_qualidade" class="form-control" onchange="tratarAosCostumes()">
                                <optgroup label="Testemunhas e Informantes">
                                    <option value="Testemunha" selected>Testemunha (Presta compromisso de dizer a verdade)</option>
                                    <option value="Informante">Informante (Isento do compromisso - Parentesco/Interesse)</option>
                                    <option value="Declarante">Declarante (Livre de compromisso)</option>
                                </optgroup>

                                <optgroup label="Partes Investigadas">
                                    <option value="Sindicado">Sindicado (Em Sindicância)</option>
                                    <option value="Acusado">Acusado (Em PAD, CD, CJ ou Conselho de Disciplina)</option>
                                    <option value="Indiciado">Indiciado (Em IPM / Inquérito)</option>
                                    <option value="Noticiado">Noticiado / Denunciado</option>
                                </optgroup>

                                <optgroup label="Ofendidos">
                                    <option value="Vítima">Vítima / Ofendido(a)</option>
                                </optgroup>

                                <optgroup label="Peritos e Auxiliares">
                                    <option value="Perito">Perito / Técnico Especialista</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label>Tipo de Depoente:</label>
                            <div class="radio-inline-group">
                                <label><input name="tipoDepoente_name" type="radio" value="civil" checked onclick="tipoDepoente()"> Civil</label>
                                <label><input name="tipoDepoente_name" type="radio" value="militar" onclick="tipoDepoente()"> Militar</label>
                                <span id="selecionaPosto"></span>
                            </div>
                        </div>

                        <!-- ALERTA DO STATUS DO COMPROMISSO LEGAL (SEMPRE VISÍVEL) -->
                        <div class="form-group col-12">
                            <div id="alerta_compromisso" class="block-title" style="background-color: #e3f2fd; border-left-color: #2196f3; color: #0d47a1; margin-bottom: 0;">
                                <i class="fa-solid fa-circle-info"></i>
                                <span id="texto_status_compromisso">Situação: O depoente PRESTARÁ o compromisso legal de dizer a verdade.</span>
                            </div>
                        </div>

                        <!-- ================================================================= -->
                        <!-- BLOCO "AOS COSTUMES" (OCULTO POR PADRÃO - SÓ EXIBIDO SE INFORMANTE) -->
                        <!-- ================================================================= -->
                        <div class="form-group col-12" id="card_aos_costumes" style="display: none; margin-top: 10px;">
                            <div class="inner-fieldset">
                                <legend><i class="fa-solid fa-scale-balanced"></i> Aos Costumes (Específico para Informantes)</legend>

                                <div class="form-grid">
                                    <div class="form-group col-12">
                                        <label>Aos costumes disse:</label>
                                        <div class="radio-inline-group">
                                            <label>
                                                <input type="radio" name="aos_costumes_opcao" value="nada" checked onchange="tratarAosCostumes()">
                                                Nada disse nem lhe foi tocado
                                            </label>
                                            <label>
                                                <input type="radio" name="aos_costumes_opcao" value="impedimento" onchange="tratarAosCostumes()">
                                                Declarou relação de parentesco / amizade / inimizade / interesse
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-12" id="box_detalhe_costumes" style="display: none;">
                                        <label for="aos_costumes_detalhe">Especifique a relação/parentesco com as partes: <span class="required">*</span></label>
                                        <input type="text" id="aos_costumes_detalhe" name="aos_costumes_detalhe" class="form-control" placeholder="Ex: É irmão do acusado / Amigo íntimo do sindicado / Inimizade capital com a vítima">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-6">
                            <label for="entnome">Nome:</label>
                            <input id="entnome" type="text" class="form-control" placeholder="Nome do Depoente">
                        </div>

                        <div class="form-group col-3">
                            <label for="entnasc">Nascimento:</label>
                            <input id="entnasc" type="date" class="form-control" onchange="pegaidade()">
                        </div>

                        <div class="form-group col-3">
                            <label for="entidade">Idade:</label>
                            <input id="entidade" type="number" class="form-control">
                        </div>

                        <div class="form-group col-4">
                            <label for="entcin">CPF / CNI:</label>
                            <input id="entcin" type="text" class="form-control" maxlength="14" placeholder="000.000.000-00" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <div class="form-group col-4">
                            <label for="nacionalidade">Nacionalidade:</label>
                            <input type="text" id="nacionalidade" value="brasileira" class="form-control">
                        </div>

                        <div class="form-group col-4">
                            <label for="telef">Telefone:</label>
                            <input id="telef" type="text" class="form-control" placeholder="(00) 00000-0000" onkeyup="aplicarMascaraTelefone()">
                        </div>

                        <!-- FILIAÇÃO -->
                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Filiação</legend>
                                <div class="form-grid">
                                    <div class="form-group col-6">
                                        <label for="entmae">Mãe:</label>
                                        <input id="entmae" type="text" class="form-control" placeholder="Nome completo da mãe">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entpai">Pai:</label>
                                        <input id="entpai" type="text" class="form-control" placeholder="Nome completo do pai">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <!-- NATURALIDADE IBGE -->
                        <div class="form-group col-6">
                            <label for="est_naturalidade">Estado (Naturalidade):</label>
                            <select id="est_naturalidade" class="form-control" onchange="buscarCidadesIBGE(this.value, 'cidade_naturalidade')">
                                <option value="">Carregando estados...</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="cidade_naturalidade">Cidade (Naturalidade):</label>
                            <select id="cidade_naturalidade" class="form-control" disabled>
                                <option value="">Selecione o estado primeiro</option>
                            </select>
                        </div>

                        <!-- PROFISSÃO -->
                        <div class="form-group col-6">
                            <label for="profissao">Profissão:</label>
                            <input type="text" name="profissao" id="profissao" class="form-control" placeholder="Digite a ocupação">
                        </div>

                        <div class="form-group col-6">
                            <label for="local_profissao">Local onde exerce a Profissão:</label>
                            <input type="text" name="local_profissao" id="local_profissao" class="form-control" placeholder="Empresa / Local de trabalho">
                        </div>

                        <!-- ESTADO CIVIL -->
                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Estado Civil</legend>
                                <div class="radio-inline-group">
                                    <label><input id="casado" name="estc" type="radio" value="Casado"> Casado</label>
                                    <label><input id="solteiro" name="estc" type="radio" value="Solteiro"> Solteiro</label>
                                    <label><input id="viúvo" name="estc" type="radio" value="Viúvo"> Viúvo</label>
                                    <label><input id="convivente" name="estc" type="radio" value="Convivente"> Convivente</label>
                                    <label><input id="divorciado" name="estc" type="radio" value="Divorciado"> Divorciado</label>
                                    <label><input id="naoinf" name="estc" type="radio" value="Não informado" checked> Não informado</label>
                                </div>
                            </fieldset>
                        </div>

                        <!-- ENDEREÇO -->
                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Endereço Residencial</legend>
                                <div class="form-grid">
                                    <div class="form-group col-8">
                                        <label for="entrua">Rua / Avenida:</label>
                                        <input id="entrua" type="text" class="form-control" placeholder="Logradouro">
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="entnumero">Número:</label>
                                        <input id="entnumero" type="number" min="1" max="999999" class="form-control" placeholder="Numeral">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entbairro">Bairro:</label>
                                        <input id="entbairro" type="text" class="form-control" placeholder="Bairro">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entcomp">Complemento:</label>
                                        <input id="entcomp" type="text" class="form-control" placeholder="Complemento">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entest">Estado (Residência):</label>
                                        <select id="entest" class="form-control" onchange="buscarCidadesIBGE(this.value, 'entmun')">
                                            <option value="">Carregando estados...</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entmun">Cidade (Residência):</label>
                                        <select id="entmun" class="form-control" disabled>
                                            <option value="">Selecione o estado primeiro</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                    </div>
                </div>

                <!-- SEÇÃO 3: DADOS DA GRAVAÇÃO E DEPOIMENTO -->
                <div class="card" id="dados_gravacao">
                    <div class="block-title">
                        <i class="fa-solid fa-file-signature"></i> Dados da Oitiva e Depoimento
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-3">
                            <label for="est_oitiva">Estado (UF):</label>
                            <select id="est_oitiva" class="form-control" onchange="buscarCidadesIBGE(this.value, 'cidade')">
                                <option value="PR">Paraná</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="cidade">Cidade da Oitiva:</label>
                            <select id="cidade" class="form-control" disabled>
                                <option value="">Carregando cidades...</option>
                            </select>
                        </div>

                        <div class="form-group col-5">
                            <label for="loc_oitiva">Local da Oitiva:</label>
                            <input id="loc_oitiva" type="text" class="form-control" value="Sala do Cartório do Sexto Batalhão de Polícia Militar">
                        </div>

                        <div class="form-group col-12">
                            <label for="endOitiva">Endereço do local da oitiva:</label>
                            <input type="text" id="endOitiva" class="form-control" placeholder="Rua da Bandeira, nº 123">
                        </div>

                        <div class="form-group col-6">
                            <label>Advogado presente? <span class="required">*</span></label>
                            <div class="radio-inline-group">
                                <label><input name="ent_adv" type="radio" value="sim" onclick="adv_presente()"> Sim</label>
                                <label><input name="ent_adv" type="radio" value="nao" onclick="adv_presente()"> Não</label>
                            </div>
                            <div id="adv" style="margin-top: 5px;"></div>
                        </div>

                        <div class="form-group col-3">
                            <label for="entinicio">Início:</label>
                            <input id="entinicio" type="time" class="form-control">
                        </div>

                        <div class="form-group col-3">
                            <label for="entfim">Término:</label>
                            <input id="entfim" type="time" class="form-control">
                        </div>
                    </div>

                    <!-- Campo de Texto do Depoimento com Botões -->
                    <div class="input-group" style="margin-top: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                            <label for="depoimento_txt" style="font-weight: bold;">Texto do Depoimento / Declarações:</label>

                            <div style="display: flex; gap: 8px;">
                                <button type="button" id="btn_voz" onclick="alternarDitado()" class="btn-acao" style="background: #2563eb; color: #fff; border: none; cursor: pointer; padding: 6px 12px; border-radius: 4px; font-size: 13px;">
                                    <i class="fa-solid fa-microphone" id="icone_voz"></i> <span id="texto_btn_voz">Ditado por Voz</span>
                                </button>

                                <button type="button" id="btn_ia" onclick="refinarTextoIA()" class="btn-acao" style="background: #8b5cf6; color: #fff; border: none; cursor: pointer; padding: 6px 12px; border-radius: 4px; font-size: 13px;">
                                    <i class="fa-solid fa-wand-magic-sparkles" id="icone_ia"></i> <span id="texto_btn_ia">Refinar com IA</span>
                                </button>
                            </div>
                        </div>

                        <textarea id="depoimento_txt" name="depoimento_txt" rows="10" style="width: 100%; padding: 10px; font-family: Arial, sans-serif; font-size: 14px;" placeholder="Digite ou clique em 'Ditado por Voz' para transcrever o depoimento falado..."></textarea>
                        <div id="status_voz" style="font-size: 12px; margin-top: 6px; min-height: 16px;" aria-live="polite"></div>
                        <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                            <i class="fa-regular fa-lightbulb"></i> Pontuação por voz: diga <b>"vírgula"</b>, <b>"ponto final"</b>, <b>"dois pontos"</b>, <b>"ponto e vírgula"</b>, <b>"ponto de interrogação"</b>, <b>"nova linha"</b> ou <b>"novo parágrafo"</b>. Você pode corrigir o texto enquanto dita.
                        </div>
                    </div>
                </div>

                <!-- BOTÃO DE IMPRESSÃO -->
                <div class="btn-container" style="justify-content: center;">
                    <button id="sbmt" type="button" class="btn btn-green" onclick="sbmt1();">
                        <i class="fa-solid fa-print"></i> Gerar e Imprimir Oitiva
                    </button>
                </div>
            </form>

        </div>

        <?php require_once 'footer.php'; ?>

    </div>

</body>

</html>