<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$user = $stmt->fetch();

$valPostoEncarregado   = $user['posto_graduacao'] ?? '';
$valUnidadeEncarregado = $user['unidade'] ?? '';

// Função auxiliar para selecionar a opção do banco
function estaSelecionado($valorOpcao, $valorBanco)
{
    return ($valorOpcao === $valorBanco) ? 'selected' : '';
}
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

    <script src="./functionsPresencial.js"></script>
    <script src="./carregaCidadesIbge.js"></script>
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

            <!-- ALERTA DE DESENVOLVIMENTO -->
            <!--             <div class="alert-dev" style="font-size: 20px; color: brown;">
                <i class="fa-solid fa-triangle-exclamation"></i> <strong>Aviso:</strong> Módulo em desenvolvimento
                (funcionalidade ainda não disponível totalmente).
            </div> -->

            <form id="formOitiva">

                <!-- DADOS DO ENCARREGADO -->
                <div class="card" id="dados_encarregado">
                    <div class="block-title">
                        <i class="fa-solid fa-user-gear"></i> Dados do Encarregado
                    </div>

                    <div class="form-grid">
                        <!-- Nome Encarregado (Puxado do BD) -->
                        <div class="form-group col-6">
                            <label for="entnome_enc">Nome:</label>
                            <input id="entnome_enc" type="text" class="form-control" placeholder="Nome do Encarregado" value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
                        </div>

                        <!-- CPF Encarregado (Puxado do BD) -->
                        <div class="form-group col-3">
                            <label for="entcni_enc"
                                title="Na certidão, os 3 primeiros dígitos serão ocultados, bem como os dois últimos (ex: XXX.123.123-XX)">CPF/CIN:</label>
                            <input id="entcni_enc" type="text" class="form-control" maxlength="14"
                                placeholder="000.000.000-00" value="<?= htmlspecialchars($user['cpf'] ?? '') ?>" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <!-- Posto/Graduação Encarregado (Padronizado e Pré-selecionado) -->
                        <div class="form-group col-3">
                            <label for="ent_posto">Posto/Graduação:</label>
                            <select id="ent_posto" class="form-control">
                                <option value="" disabled>Selecione...</option>

                                <optgroup label="Oficiais Superiores">
                                    <option value="Cel." <?= estaSelecionado('Cel.', $valPostoEncarregado) ?>>Coronel (Cel.)</option>
                                    <option value="Ten.-Cel." <?= estaSelecionado('Ten.-Cel.', $valPostoEncarregado) ?>>Tenente-Coronel (Ten.-Cel.)</option>
                                    <option value="Maj." <?= estaSelecionado('Maj.', $valPostoEncarregado) ?>>Major (Maj.)</option>
                                </optgroup>

                                <optgroup label="Oficiais Intermediários e Subalternos">
                                    <option value="Cap." <?= estaSelecionado('Cap.', $valPostoEncarregado) ?>>Capitão (Cap.)</option>
                                    <option value="1º Ten." <?= estaSelecionado('1º Ten.', $valPostoEncarregado) ?>>1º Tenente (1º Ten.)</option>
                                    <option value="2º Ten." <?= estaSelecionado('2º Ten.', $valPostoEncarregado) ?>>2º Tenente (2º Ten.)</option>
                                    <option value="Asp. a Of." <?= estaSelecionado('Asp. a Of.', $valPostoEncarregado) ?>>Aspirante-a-Oficial (Asp. a Of.)</option>
                                </optgroup>

                                <optgroup label="Praças Especiais">
                                    <option value="Cadete 3º Ano" <?= estaSelecionado('Cadete 3º Ano', $valPostoEncarregado) ?>>Cadete 3º Ano</option>
                                    <option value="Cadete 2º Ano" <?= estaSelecionado('Cadete 2º Ano', $valPostoEncarregado) ?>>Cadete 2º Ano</option>
                                    <option value="Cadete 1º Ano" <?= estaSelecionado('Cadete 1º Ano', $valPostoEncarregado) ?>>Cadete 1º Ano</option>
                                </optgroup>

                                <optgroup label="Praças Graduados">
                                    <option value="Subten." <?= estaSelecionado('Subten.', $valPostoEncarregado) ?>>Subtenente (Subten.)</option>
                                    <option value="1º Sgt." <?= estaSelecionado('1º Sgt.', $valPostoEncarregado) ?>>1º Sargento (1º Sgt.)</option>
                                    <option value="2º Sgt." <?= estaSelecionado('2º Sgt.', $valPostoEncarregado) ?>>2º Sargento (2º Sgt.)</option>
                                    <option value="3º Sgt." <?= estaSelecionado('3º Sgt.', $valPostoEncarregado) ?>>3º Sargento (3º Sgt.)</option>
                                    <option value="Cabo" <?= estaSelecionado('Cabo', $valPostoEncarregado) ?>>Cabo (Cb.)</option>
                                </optgroup>

                                <optgroup label="Praças">
                                    <option value="Soldado 1ª Classe" <?= estaSelecionado('Soldado 1ª Classe', $valPostoEncarregado) ?>>Soldado 1ª Classe (Sd. 1ª Cl.)</option>
                                    <option value="Soldado 2ª Classe" <?= estaSelecionado('Soldado 2ª Classe', $valPostoEncarregado) ?>>Soldado 2ª Classe (Sd. 2ª Cl. - Aluno)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="quadro_enc">Quadro:</label>
                            <select id="quadro_enc" class="form-control">
                                <option value="QP PM">QP PM</option>
                                <option value="QOEM PM">QOEM PM</option>
                                <option value="PM">Sem quadro</option>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="ent_proc">Procedimento:</label>
                            <select id="ent_proc" class="form-control">
                                <option value="IPM">IPM</option>
                                <option value="FATD">FATD</option>
                                <option value="SINDICÂNCIA">SINDICÂNCIA</option>
                                <option value="APFD">APFD</option>
                                <option value="CD/CJ">CD/CJ</option>
                                <option value="ADL">ADL</option>
                                <option value="Inquérito Técnico">IT</option>
                            </select>
                        </div>

                        <div class="form-group col-2">
                            <label for="ent_num_proc">Número:</label>
                            <input id="ent_num_proc" class="form-control" placeholder="Número do Procedimento">
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

                        <!-- Unidades PMPR completas (Com pré-seleção) -->
                        <div class="form-group col-2">
                            <label for="opm">OPM:</label>
                            <select name="opm" id="opm" class="form-control">
                                <option value="" disabled>Selecione a Unidade...</option>

                                <optgroup label="Comandos Regionais (CRPM)">
                                    <option value="1º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('1º CRPM - Curitiba', $valUnidadeEncarregado) ?>>1º CRPM - Curitiba</option>
                                    <option value="2º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('2º CRPM - Londrina', $valUnidadeEncarregado) ?>>2º CRPM - Londrina</option>
                                    <option value="3º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('3º CRPM - Maringá', $valUnidadeEncarregado) ?>>3º CRPM - Maringá</option>
                                    <option value="4º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('4º CRPM - Ponta Grossa', $valUnidadeEncarregado) ?>>4º CRPM - Ponta Grossa</option>
                                    <option value="5º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('5º CRPM - Cascavel', $valUnidadeEncarregado) ?>>5º CRPM - Cascavel</option>
                                    <option value="6º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('6º CRPM - São José dos Pinhais', $valUnidadeEncarregado) ?>>6º CRPM - São José dos Pinhais</option>
                                    <option value="7º COMANDO REGIONAL DE POLÍCIA MILITAR" <?= estaSelecionado('7º CRPM - Pato Branco', $valUnidadeEncarregado) ?>>7º CRPM - Pato Branco</option>
                                </optgroup>

                                <optgroup label="Comandos Especializados e Apoio">
                                    <option value="CME - Curitiba" <?= estaSelecionado('CME - Curitiba', $valUnidadeEncarregado) ?>>CME - Curitiba</option>
                                    <option value="CPE - Curitiba" <?= estaSelecionado('CPE - Curitiba', $valUnidadeEncarregado) ?>>CPE - Curitiba</option>
                                    <option value="COMAV - Curitiba" <?= estaSelecionado('COMAV - Curitiba', $valUnidadeEncarregado) ?>>COMAV - Curitiba</option>
                                    <option value="CIROCAM - Curitiba" <?= estaSelecionado('CIROCAM - Curitiba', $valUnidadeEncarregado) ?>>CIROCAM - Curitiba</option>
                                    <option value="COPOM - Curitiba" <?= estaSelecionado('COPOM - Curitiba', $valUnidadeEncarregado) ?>>COPOM - Curitiba</option>
                                </optgroup>

                                <optgroup label="Batalhões de Polícia Militar (BPM)">
                                    <option value="1º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('1º BPM - Ponta Grossa', $valUnidadeEncarregado) ?>>1º BPM - Ponta Grossa</option>
                                    <option value="2º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('2º BPM - Jacarezinho', $valUnidadeEncarregado) ?>>2º BPM - Jacarezinho</option>
                                    <option value="3º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('3º BPM - Pato Branco', $valUnidadeEncarregado) ?>>3º BPM - Pato Branco</option>
                                    <option value="4º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('4º BPM - Maringá', $valUnidadeEncarregado) ?>>4º BPM - Maringá</option>
                                    <option value="5º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('5º BPM - Londrina', $valUnidadeEncarregado) ?>>5º BPM - Londrina</option>
                                    <option value="6º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('6º BPM - Cascavel', $valUnidadeEncarregado) ?>>6º BPM - Cascavel</option>
                                    <option value="7º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('7º BPM - Cruzeiro do Oeste', $valUnidadeEncarregado) ?>>7º BPM - Cruzeiro do Oeste</option>
                                    <option value="8º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('8º BPM - Paranavaí', $valUnidadeEncarregado) ?>>8º BPM - Paranavaí</option>
                                    <option value="9º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('9º BPM - Paranaguá', $valUnidadeEncarregado) ?>>9º BPM - Paranaguá</option>
                                    <option value="10º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('10º BPM - Apucarana', $valUnidadeEncarregado) ?>>10º BPM - Apucarana</option>
                                    <option value="11º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('11º BPM - Campo Mourão', $valUnidadeEncarregado) ?>>11º BPM - Campo Mourão</option>
                                    <option value="12º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('12º BPM - Curitiba', $valUnidadeEncarregado) ?>>12º BPM - Curitiba</option>
                                    <option value="13º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('13º BPM - Curitiba', $valUnidadeEncarregado) ?>>13º BPM - Curitiba</option>
                                    <option value="14º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('14º BPM - Foz do Iguaçu', $valUnidadeEncarregado) ?>>14º BPM - Foz do Iguaçu</option>
                                    <option value="15º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('15º BPM - Rolândia', $valUnidadeEncarregado) ?>>15º BPM - Rolândia</option>
                                    <option value="16º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('16º BPM - Guarapuava', $valUnidadeEncarregado) ?>>16º BPM - Guarapuava</option>
                                    <option value="17º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('17º BPM - São José dos Pinhais', $valUnidadeEncarregado) ?>>17º BPM - São José dos Pinhais</option>
                                    <option value="18º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('18º BPM - Cornélio Procópio', $valUnidadeEncarregado) ?>>18º BPM - Cornélio Procópio</option>
                                    <option value="19º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('19º BPM - Toledo', $valUnidadeEncarregado) ?>>19º BPM - Toledo</option>
                                    <option value="20º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('20º BPM - Curitiba', $valUnidadeEncarregado) ?>>20º BPM - Curitiba</option>
                                    <option value="21º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('21º BPM - Francisco Beltrão', $valUnidadeEncarregado) ?>>21º BPM - Francisco Beltrão</option>
                                    <option value="22º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('22º BPM - Colombo', $valUnidadeEncarregado) ?>>22º BPM - Colombo</option>
                                    <option value="23º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('23º BPM – Curitiba', $valUnidadeEncarregado) ?>>23º BPM – Curitiba</option>
                                    <option value="25º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('25º BPM - Umuarama', $valUnidadeEncarregado) ?>>25º BPM - Umuarama</option>
                                    <option value="26º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('26º BPM - Telêmaco Borba', $valUnidadeEncarregado) ?>>26º BPM - Telêmaco Borba</option>
                                    <option value="27º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('27º BPM - União da Vitória', $valUnidadeEncarregado) ?>>27º BPM - União da Vitória</option>
                                    <option value="28° BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('28° BPM - Lapa', $valUnidadeEncarregado) ?>>28° BPM - Lapa</option>
                                    <option value="29º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('29º BPM - Piraquara', $valUnidadeEncarregado) ?>>29º BPM - Piraquara</option>
                                    <option value="30º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('30º BPM - Londrina', $valUnidadeEncarregado) ?>>30º BPM - Londrina</option>
                                    <option value="31º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('31º BPM - Assis Chateaubriand', $valUnidadeEncarregado) ?>>31º BPM - Assis Chateaubriand</option>
                                    <option value="32º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('32º BPM - Sarandi', $valUnidadeEncarregado) ?>>32º BPM - Sarandi</option>
                                    <option value="33° BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('33° BPM - Curitiba', $valUnidadeEncarregado) ?>>33° BPM - Curitiba</option>
                                    <option value="34º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('34º BPM - Almirante Tamandaré', $valUnidadeEncarregado) ?>>34º BPM - Almirante Tamandaré</option>
                                </optgroup>

                                <optgroup label="Companhias Independentes (CIPM)">
                                    <option value="3ª CIPM - Loanda" <?= estaSelecionado('3ª CIPM - Loanda', $valUnidadeEncarregado) ?>>3ª CIPM - Loanda</option>
                                    <option value="5ª CIPM - Cianorte" <?= estaSelecionado('5ª CIPM - Cianorte', $valUnidadeEncarregado) ?>>5ª CIPM - Cianorte</option>
                                    <option value="6ª CIPM - Ivaiporã" <?= estaSelecionado('6ª CIPM - Ivaiporã', $valUnidadeEncarregado) ?>>6ª CIPM - Ivaiporã</option>
                                    <option value="7ª CIPM - Arapongas" <?= estaSelecionado('7ª CIPM - Arapongas', $valUnidadeEncarregado) ?>>7ª CIPM - Arapongas</option>
                                    <option value="8ª CIPM - Irati" <?= estaSelecionado('8ª CIPM - Irati', $valUnidadeEncarregado) ?>>8ª CIPM - Irati</option>
                                    <option value="9ª CIPM - Colorado" <?= estaSelecionado('9ª CIPM - Colorado', $valUnidadeEncarregado) ?>>9ª CIPM - Colorado</option>
                                    <option value="10ª CIPM - Laranjeiras do Sul" <?= estaSelecionado('10ª CIPM - Laranjeiras do Sul', $valUnidadeEncarregado) ?>>10ª CIPM - Laranjeiras do Sul</option>
                                    <option value="11ª CIPM - Cambé" <?= estaSelecionado('11ª CIPM - Cambé', $valUnidadeEncarregado) ?>>11ª CIPM - Cambé</option>
                                    <option value="12ª CIPM - Palmas" <?= estaSelecionado('12ª CIPM - Palmas', $valUnidadeEncarregado) ?>>12ª CIPM - Palmas</option>
                                </optgroup>

                                <optgroup label="Batalhões Especializados">
                                    <option value="BOPE - Piraquara" <?= estaSelecionado('BOPE - Piraquara', $valUnidadeEncarregado) ?>>BOPE - Piraquara</option>
                                    <option value="BPAmb - São José dos Pinhais" <?= estaSelecionado('BPAmb - São José dos Pinhais', $valUnidadeEncarregado) ?>>BPAmb - São José dos Pinhais</option>
                                    <option value="BPChoque - Curitiba" <?= estaSelecionado('BPChoque - Curitiba', $valUnidadeEncarregado) ?>>BPChoque - Curitiba</option>
                                    <option value="BPEC - Curitiba" <?= estaSelecionado('BPEC - Curitiba', $valUnidadeEncarregado) ?>>BPEC - Curitiba</option>
                                    <option value="BPFron - Marechal Cândido Rondon" <?= estaSelecionado('BPFron - Marechal Cândido Rondon', $valUnidadeEncarregado) ?>>BPFron - Marechal Cândido Rondon</option>
                                    <option value="BPRONE - Curitiba" <?= estaSelecionado('BPRONE - Curitiba', $valUnidadeEncarregado) ?>>BPRONE - Curitiba</option>
                                    <option value="BPRv - Curitiba" <?= estaSelecionado('BPRv - Curitiba', $valUnidadeEncarregado) ?>>BPRv - Curitiba</option>
                                    <option value="BPTran - Curitiba" <?= estaSelecionado('BPTran - Curitiba', $valUnidadeEncarregado) ?>>BPTran - Curitiba</option>
                                    <option value="RPMon - Curitiba" <?= estaSelecionado('RPMon - Curitiba', $valUnidadeEncarregado) ?>>RPMon - Curitiba</option>
                                </optgroup>
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

                                    <!-- Posto/Graduação do Escrivão Padronizado -->
                                    <div class="form-group col-2">
                                        <label for="ent_posto_esc">Posto/Graduação:</label>
                                        <select id="ent_posto_esc" class="form-control">
                                            <option value="" disabled selected>Selecione...</option>

                                            <optgroup label="Oficiais Superiores">
                                                <option value="Cel.">Coronel (Cel.)</option>
                                                <option value="Ten.-Cel.">Tenente-Coronel (Ten.-Cel.)</option>
                                                <option value="Maj.">Major (Maj.)</option>
                                            </optgroup>

                                            <optgroup label="Oficiais Intermediários e Subalternos">
                                                <option value="Cap.">Capitão (Cap.)</option>
                                                <option value="1º Ten.">1º Tenente (1º Ten.)</option>
                                                <option value="2º Ten.">2º Tenente (2º Ten.)</option>
                                                <option value="Asp. a Of.">Aspirante-a-Oficial (Asp. a Of.)</option>
                                            </optgroup>

                                            <optgroup label="Praças Especiais">
                                                <option value="Cadete 3º Ano">Cadete 3º Ano</option>
                                                <option value="Cadete 2º Ano">Cadete 2º Ano</option>
                                                <option value="Cadete 1º Ano">Cadete 1º Ano</option>
                                            </optgroup>

                                            <optgroup label="Praças Graduados">
                                                <option value="Subten.">Subtenente (Subten.)</option>
                                                <option value="1º Sgt.">1º Sargento (1º Sgt.)</option>
                                                <option value="2º Sgt.">2º Sargento (2º Sgt.)</option>
                                                <option value="3º Sgt.">3º Sargento (3º Sgt.)</option>
                                                <option value="Cabo">Cabo (Cb.)</option>
                                            </optgroup>

                                            <optgroup label="Praças">
                                                <option value="Soldado 1ª Classe">Soldado 1ª Classe (Sd. 1ª Cl.)</option>
                                                <option value="Soldado 2ª Classe">Soldado 2ª Classe (Sd. 2ª Cl. - Aluno)</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="form-group col-2">
                                        <label for="quadro_esc">Quadro:</label>
                                        <select id="quadro_esc" class="form-control">
                                            <option value="QP PM">QP PM</option>
                                            <option value="QOE PM">QOE PM</option>
                                            <option value="QOEM PM" selected>QOEM PM</option>
                                            <option value="PM">Sem quadro</option>
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
                        <div class="form-group col-4">
                            <label for="ent_qualidade">Qualidade:</label>
                            <select id="ent_qualidade" class="form-control">
                                <option value="Testemunha">Testemunha</option>
                                <option value="Acusado">Acusado</option>
                                <option value="Vítima">Vítima</option>
                            </select>
                        </div>

                        <div class="form-group col-8">
                            <label>Tipo de Depoente:</label>
                            <div class="radio-inline-group">
                                <label><input name="tipoDepoente_name" type="radio" value="civil" checked
                                        onclick="tipoDepoente()"> Civil</label>
                                <label><input name="tipoDepoente_name" type="radio" value="militar"
                                        onclick="tipoDepoente()"> Militar</label>
                                <span id="selecionaPosto"></span>
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
                            <input id="entcin" type="text" class="form-control" maxlength="14"
                                placeholder="000.000.000-00" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <div class="form-group col-4">
                            <label for="nacionalidade">Nacionalidade:</label>
                            <input type="text" id="nacionalidade" value="brasileira" class="form-control">
                        </div>

                        <div class="form-group col-4">
                            <label for="telef">Telefone:</label>
                            <input id="telef" type="text" class="form-control" placeholder="(00) 00000-0000"
                                onkeyup="aplicarMascaraTelefone()">
                        </div>

                        <!-- FILIAÇÃO -->
                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Filiação</legend>
                                <div class="form-grid">
                                    <div class="form-group col-6">
                                        <label for="entmae">Mãe:</label>
                                        <input id="entmae" type="text" class="form-control"
                                            placeholder="Nome completo da mãe">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entpai">Pai:</label>
                                        <input id="entpai" type="text" class="form-control"
                                            placeholder="Nome completo do pai">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <!-- NATURALIDADE IBGE -->
                        <div class="form-group col-6">
                            <label for="est_naturalidade">Estado (Naturalidade):</label>
                            <select id="est_naturalidade" class="form-control"
                                onchange="buscarCidadesIBGE(this.value, 'cidade_naturalidade')">
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
                            <input type="text" name="profissao" id="profissao" class="form-control"
                                placeholder="Digite a ocupação">
                        </div>

                        <div class="form-group col-6">
                            <label for="local_profissao">Local onde exerce a Profissão:</label>
                            <input type="text" name="local_profissao" id="local_profissao" class="form-control"
                                placeholder="Empresa / Local de trabalho">
                        </div>

                        <!-- ESTADO CIVIL -->
                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Estado Civil</legend>
                                <div class="radio-inline-group">
                                    <label><input id="casado" name="estc" type="radio" value="Casado"> Casado</label>
                                    <label><input id="solteiro" name="estc" type="radio" value="Solteiro">
                                        Solteiro</label>
                                    <label><input id="viúvo" name="estc" type="radio" value="Viúvo"> Viúvo</label>
                                    <label><input id="convivente" name="estc" type="radio" value="Convivente">
                                        Convivente</label>
                                    <label><input id="divorciado" name="estc" type="radio" value="Divorciado">
                                        Divorciado</label>
                                    <label><input id="naoinf" name="estc" type="radio" value="Não informado" checked>
                                        Não informado</label>
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
                                        <input id="entnumero" type="number" min="1" max="999999" class="form-control"
                                            placeholder="Numeral">
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
                                        <select id="entest" class="form-control"
                                            onchange="buscarCidadesIBGE(this.value, 'entmun')">
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
                            <select id="est_oitiva" class="form-control">
                                <option value="PR">PR</option>
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
                            <input id="loc_oitiva" type="text" class="form-control"
                                value="Sala do Cartório do Sexto Batalhão de Polícia Militar">
                        </div>

                        <div class="form-group col-12">
                            <label for="endOitiva">Endereço do local da oitiva:</label>
                            <input type="text" id="endOitiva" class="form-control"
                                placeholder="Rua da Bandeira, nº 123">
                        </div>

                        <div class="form-group col-6">
                            <label>Advogado presente? <span class="required">*</span></label>
                            <div class="radio-inline-group">
                                <label><input name="ent_adv" type="radio" value="sim" onclick="adv_presente()">
                                    Sim</label>
                                <label><input name="ent_adv" type="radio" value="nao" onclick="adv_presente()">
                                    Não</label>
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
                        <br>


                    </div>

                    <!-- Campo de Texto do Depoimento com Botão de Transcrição -->
                    <div class="input-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                            <label for="depoimento_txt" style="font-weight: bold;">Texto do Depoimento / Declarações:</label>

                            <div style="display: flex; gap: 8px;">
                                <!-- BOTÃO 1: DITADO POR VOZ -->
                                <button type="button" id="btn_voz" onclick="alternarDitado()" class="btn-acao" style="background: #2563eb; color: #fff; border: none; cursor: pointer; padding: 6px 12px; border-radius: 4px; font-size: 13px;">
                                    <i class="fa-solid fa-microphone" id="icone_voz"></i> <span id="texto_btn_voz">Ditado por Voz</span>
                                </button>

                                <!-- BOTÃO 2: REFINAR COM IA -->
                                <button type="button" id="btn_ia" onclick="refinarTextoIA()" class="btn-acao" style="background: #8b5cf6; color: #fff; border: none; cursor: pointer; padding: 6px 12px; border-radius: 4px; font-size: 13px;">
                                    <i class="fa-solid fa-wand-magic-sparkles" id="icone_ia"></i> <span id="texto_btn_ia">Refinar com IA</span>
                                </button>
                            </div>
                        </div>

                        <textarea id="depoimento_txt" name="depoimento_txt" rows="10" style="width: 100%; padding: 10px; font-family: Arial, sans-serif; font-size: 14px;" placeholder="Digite ou clique em 'Ditado por Voz' para transcrever o depoimento falado..."></textarea>
                    </div>
                </div>

                <!-- BOTÕES E AÇÕES DA PÁGINA -->
                <div class="btn-container" style="justify-content: center;">
                    <button id="sbmt" type="button" class="btn btn-green" onclick="sbmt1();">
                        <i class="fa-solid fa-print"></i> Gerar e Imprimir Oitiva
                    </button>
                </div>
                <div id="btnconf" style="text-align: center; margin-top: 10px;"></div>
            </form>

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