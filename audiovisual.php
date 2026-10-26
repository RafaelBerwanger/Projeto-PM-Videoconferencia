<?php
require_once 'sessao.php';
require_once 'listas.php';

$user = exigirLogin();

$valPostoEncarregado   = $user['posto_graduacao'] ?? '';
$valUnidadeEncarregado = $user['unidade'] ?? '';

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
    <title>Videoconferência / Gravação Audiovisual - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Ficheiros CSS -->
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link rel="stylesheet" href="style_auxilio.css">

    <!-- Bibliotecas JS Externas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.4.1/jspdf.debug.js"
        integrity="sha384-THVO/sM0mFD9h7dfSndI6TS0PgAGavwKvB5hAxRRvc0o9cPLohB0wb/PTA7LdUHs"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>

    <script src="./functionsJS.js?v=2.1"></script>
    <script>
        // Servidor MiroTalk P2P da sala de videoconferência (config.php)
        window.CONFIG_OITIVA = <?= json_encode([
            'dominio' => defined('MIROTALK_DOMINIO') ? MIROTALK_DOMINIO : 'p2p.mirotalk.com',
        ]) ?>;
    </script>
    <script src="./oitivaVideo.js?v=2.0"></script>
    <style>
        .oitiva-botoes { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .oitiva-botoes .btn { font-size: 15px; padding: 12px 22px; }
        .oitiva-botoes .btn:disabled { opacity: 0.45; cursor: not-allowed; }
        #oitiva_painel { position: relative; margin-top: 12px; }
        #oitiva_iframe { width: 100%; height: 460px; border: 0; border-radius: 8px; background: #0f172a; display: block; }
        .oitiva-barra-flutuante { display: none; }
        /* Durante a gravação a chamada flutua fixa na tela: continua visível (e gravada) ao rolar a página */
        #oitiva_painel.flutuante {
            position: fixed; right: 16px; bottom: 16px; width: 440px; max-width: calc(100vw - 32px);
            z-index: 10000; margin: 0; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); border-radius: 10px;
            background: #0f172a; padding: 6px;
        }
        #oitiva_painel.flutuante #oitiva_iframe { height: 270px; border-radius: 6px; }
        #oitiva_painel.flutuante.ampliado { width: 760px; }
        #oitiva_painel.flutuante.ampliado #oitiva_iframe { height: 460px; }
        #oitiva_painel.flutuante .oitiva-barra-flutuante {
            display: flex; justify-content: space-between; align-items: center; gap: 8px;
            color: #fff; font-size: 12px; padding: 2px 4px 6px;
        }
        .oitiva-barra-flutuante button { background: #334155; color: #fff; border: 0; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px; }
        .oitiva-barra-flutuante .parar { background: #dc2626; }
    </style>
</head>

<body>

    <!-- Inclui a barra lateral centralizada -->
    <?php require_once 'sidebar.php'; ?>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-wrapper">

        <!-- NAV SUPERIOR -->
        <?php require_once 'top-nav.php'; ?>

        <!-- BANNER DE TÍTULO -->
        <div class="page-banner">
            <i class="fa-solid fa-video"></i> Videoconferência / Gravação Audiovisual
        </div>

        <div class="content-area">

            <!-- OITIVA POR VIDEOCONFERÊNCIA COM GRAVAÇÃO INTEGRADA (MiroTalk P2P) -->
            <div class="card" id="card_oitiva_video">
                <div class="block-title">
                    <i class="fa-solid fa-video"></i> Realizar a Oitiva por Videoconferência
                    <span id="oitiva_rec" style="display:none; margin-left:auto; align-items:center; gap:6px; background:#dc2626; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px;">
                        <i class="fa-solid fa-circle fa-beat" style="font-size:8px;"></i> GRAVANDO <span id="oitiva_tempo">00:00:00</span>
                    </span>
                </div>

                <p style="color:#64748b; margin-bottom:12px;">
                    Abra a sala, envie o link ao depoente e use <b>Gravar</b> e <b>Encerrar</b>. O vídeo é salvo no seu computador e o relatório abaixo é preenchido sozinho.
                    Se a oitiva já foi gravada em outro programa, carregue o arquivo em <b>Dados da Gravação</b>.
                </p>

                <div class="oitiva-botoes">
                    <button type="button" id="btn_oitiva_sala" class="btn btn-blue" onclick="iniciarSalaOitiva()"><i class="fa-solid fa-door-open"></i> Abrir sala de oitiva</button>
                    <button type="button" id="btn_oitiva_gravar" class="btn btn-green" onclick="gravarOitiva()" disabled><i class="fa-solid fa-circle"></i> Gravar</button>
                    <button type="button" id="btn_oitiva_encerrar" class="btn" style="background:#dc2626;" onclick="encerrarOitiva()" disabled><i class="fa-solid fa-stop"></i> Encerrar</button>
                    <a href="#" id="lnk_oitiva_intercorrencia" onclick="registrarIntercorrenciaOitiva(); return false;" style="display:none; color:#b45309; font-size:13px;"><i class="fa-solid fa-flag"></i> Registrar intercorrência</a>
                </div>

                <div id="oitiva_status" style="margin-top:10px; font-size:13px;" aria-live="polite"></div>

                <div id="oitiva_sala" style="display:none;">
                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:12px; align-items:center;">
                        <label for="oitiva_link" style="margin:0;">Link para o depoente:</label>
                        <input id="oitiva_link" type="text" class="form-control" readonly style="flex:1; min-width:220px;">
                        <button type="button" class="btn btn-blue" onclick="copiarLinkSala()"><i class="fa-solid fa-copy"></i> Copiar</button>
                        <button type="button" class="btn btn-green" onclick="enviarLinkWhatsapp()"><i class="fa-brands fa-whatsapp"></i> Enviar</button>
                    </div>

                    <div id="oitiva_painel">
                        <div class="oitiva-barra-flutuante">
                            <span><i class="fa-solid fa-circle fa-beat" style="color:#ef4444; font-size:8px;"></i> Chamada em gravação</span>
                            <span style="display:flex; gap:6px;">
                                <button type="button" onclick="alternarTamanhoChamada()" title="Ampliar / reduzir"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></button>
                                <button type="button" class="parar" onclick="encerrarOitiva()"><i class="fa-solid fa-stop"></i> Encerrar</button>
                            </span>
                        </div>
                        <iframe id="oitiva_iframe" title="Sala de videoconferência"
                            allow="camera; microphone; speaker-selection; display-capture; fullscreen; autoplay; picture-in-picture; screen-wake-lock"></iframe>
                    </div>
                </div>

                <details style="margin-top:10px; font-size:12px; color:#64748b;">
                    <summary style="cursor:pointer;">Dicas</summary>
                    <ul style="margin:6px 0 0 18px; line-height:1.6;">
                        <li>Use o <b>Google Chrome</b> ou o <b>Microsoft Edge</b> no computador. O depoente pode entrar pelo celular, sem instalar nada.</li>
                        <li>Ao clicar em <b>Gravar</b>, confirme <b>Permitir</b> na janela do navegador (deixe marcado o áudio da guia).</li>
                        <li>Use fone de ouvido para evitar eco.</li>
                        <li>Não feche esta página durante a gravação. O vídeo não é enviado a nenhum servidor.</li>
                    </ul>
                </details>
            </div>

            <!-- CARD DE CHECKLIST -->
            <div class="card" style="text-align: center;">
                <h2 style="font-size: 16px; font-weight: bold; color: #2b80c5; margin-bottom: 5px;">Checklist de Tarefas
                </h2>
                <p style="margin-bottom: 10px; color: #555;">Use o botão abaixo para abrir a sequência de verificações
                    da oitiva:</p>
                <button id="openModal" class="btn btn-blue" style="margin: 0 auto;"><i
                        class="fa-solid fa-list-check"></i> Abrir Checklist</button>
            </div>

            <!-- MODAL CHECKLIST -->
            <div id="checklistModal" class="modal modal-checklist">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <h3 style="text-align: center; margin-bottom: 15px; color: #2b80c5;"><i
                            class="fa-solid fa-list-check"></i> Sequência de Ações</h3>

                    <div id="checklist">
                        <div class="checklist-item"><label><input type="checkbox"> Saudação / Hora e data /
                                Identificação do Encarregado</label></div>
                        <div class="checklist-item"><input type="checkbox"> Breve resumo dos fatos apurados</div>
                        <div class="checklist-item"><input type="checkbox"> Informar sobre a gravação (fins exclusivos)
                        </div>
                        <div class="checklist-item"><input type="checkbox"> Tomar qualificação do depoente:</div>
                        <div class="checklist-item"><input type="checkbox"> Nome</div>
                        <div class="checklist-item"><input type="checkbox"> RG / CPF / Data de Nascimento</div>
                        <div class="checklist-item"><input type="checkbox"> Filiação</div>
                        <div class="checklist-item"><input type="checkbox"> Estado Civil</div>
                        <div class="checklist-item"><input type="checkbox"> Telefone / Profissão / Escolaridade</div>
                        <div class="checklist-item"><input type="checkbox"> Endereço (Rua, Núm, Bairro, Comp, Município)
                        </div>
                        <div class="checklist-item"><input type="checkbox"> Mostrar o documento para câmera</div>
                        <div class="checklist-item" style="color: red;"><input type="checkbox"> Verificar grau de
                            parentesco, amizade ou inimizade com o Acusado/Indiciado</div>
                        <div class="checklist-item" style="color: red;"><input type="checkbox"> Colher/Dispensar do
                            compromisso legal de dizer a verdade, sob pena do crime de falso testemunho</div>
                        <div class="checklist-item"><input type="checkbox"> Ouvir relato da parte</div>
                        <div class="checklist-item"><input type="checkbox"> Momento de formulação de questionamentos do
                            Encarregado e do Defensor</div>
                        <div class="checklist-item" style="color: red;"><input type="checkbox"> Perguntar se há algo
                            mais a ser acrescentado</div>
                        <div class="checklist-item"><input type="checkbox"> Falar a HORA FINAL da oitiva e encerrar a
                            gravação</div>
                    </div>

                    <hr style="margin: 15px 0;">
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="newItem" class="form-control" placeholder="Adicionar nova tarefa..."
                            style="flex: 1;">
                        <button id="addItem" class="btn btn-blue" style="padding: 0 12px;">+</button>
                    </div>
                </div>
            </div>

            <form onsubmit="return false;">
                <!-- DADOS DO ENCARREGADO -->
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

                        <!-- Posto/Graduação Encarregado (Padronizado e Pré-selecionado) -->
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

                                    <!-- Posto/Graduação do Escrivão Padronizado -->
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

                <!-- DADOS DO DEPOENTE -->
                <div class="card" id="dados_depoente">
                    <div class="block-title">
                        <i class="fa-solid fa-user"></i> Dados do Depoente
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-4">
                            <label for="ent_qualidade">Qualidade:</label>
                            <select id="ent_qualidade" class="form-control">
                                <option value="Testemunha">Testemunha</option>
                                <option value="Autor">Autor</option>
                                <option value="Acusado">Acusado</option>
                                <option value="Indiciado">Indiciado</option>
                                <option value="Vítima">Vítima</option>
                                <option value="Sindicado">Sindicado</option>
                                <option value="Ofendido">Ofendido</option>
                                <option value="Informante">Informante</option>
                                <option value="Envolvido">Envolvido (IT)</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="ent_presenca"
                                title="Se a pessoa estiver sendo ouvida por videoconferência marque &quot;Não&quot;. Se estiver presente, marque &quot;Sim&quot;">Está
                                presente?</label>
                            <select id="ent_presenca" class="form-control">
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="ent_comp">Presta compromisso legal de dizer a verdade?</label>
                            <select id="ent_comp" class="form-control">
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label>Costumes: <span class="required">*</span></label>
                            <div class="radio-inline-group">
                                <label><input name="ent_cost" type="radio" value="sim" onclick="func_cost()">
                                    Sim</label>
                                <label><input name="ent_cost" type="radio" value="nao" onclick="func_cost()"> Não (nada
                                    disse)</label>
                            </div>
                            <div id="p_texto" style="margin-top: 5px;"></div>
                        </div>

                        <div class="form-group col-6">
                            <label for="entnome">Nome:</label>
                            <input id="entnome" type="text" class="form-control" placeholder="Nome do Depoente">
                        </div>

                        <div class="form-group col-2">
                            <label for="entcin"
                                title="Na certidão, os 3 primeiros dígitos serão ocultados, bem como os dois últimos (ex: XXX.123.123-XX)">CPF/CIN:</label>
                            <input id="entcin" type="text" class="form-control" maxlength="14"
                                placeholder="000.000.000-00" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <div class="form-group col-2">
                            <label for="entnasc">Data de Nascimento:</label>
                            <input id="entnasc" type="date" class="form-control" onchange="pegaidade()">
                        </div>

                        <div class="form-group col-2">
                            <label for="entidade">Idade:</label>
                            <input id="entidade" type="number" class="form-control">
                        </div>

                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Filiação</legend>
                                <div class="form-grid">
                                    <div class="form-group col-6">
                                        <label for="entmae">Mãe:</label>
                                        <input id="entmae" type="text" class="form-control"
                                            placeholder="Nome da genitora">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="entpai">Pai:</label>
                                        <input id="entpai" type="text" class="form-control"
                                            placeholder="Nome do genitor">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="form-group col-4">
                            <fieldset class="inner-fieldset">
                                <legend>Estado Civil</legend>
                                <div class="radio-inline-group" style="flex-wrap: wrap;">
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

                        <div class="form-group col-4">
                            <label for="telef">Telefone:</label>
                            <input id="telef" type="text" class="form-control" maxlength="16"
                                placeholder="(45) 99999-9999" onkeyup="aplicarMascaraTelefone()">

                            <label for="prof" style="margin-top: 5px;">Profissão:</label>
                            <input id="prof" type="text" class="form-control" placeholder="ex: Policial Militar">
                        </div>

                        <div class="form-group col-4">
                            <label for="escol">Escolaridade:</label>
                            <select id="escol" class="form-control">
                                <option value="Ensino fundamental incomp.">Ensino fundamental incompleto</option>
                                <option value="Ensino fundamental completo">Ensino fundamental completo</option>
                                <option value="Ensino médio incompleto">Ensino médio incompleto</option>
                                <option value="Ensino médio completo">Ensino médio completo</option>
                                <option value="Ensino superior incompleto">Ensino superior incompleto</option>
                                <option value="Ensino superior completo">Ensino superior completo</option>
                                <option value="Não informado" selected="">Não informado</option>
                            </select>
                        </div>

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
                                    <div class="form-group col-4">
                                        <label for="entbairro">Bairro:</label>
                                        <input id="entbairro" type="text" class="form-control" placeholder="Bairro">
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="entcomp">Complemento:</label>
                                        <input id="entcomp" type="text" class="form-control" placeholder="Complemento">
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="entmun">Município:</label>
                                        <input id="entmun" type="text" class="form-control" placeholder="Município">
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="entest">UF:</label>
                                        <input id="entest" type="text" class="form-control" placeholder="UF">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="form-group col-12">
                            <span id="limparDepo" class="link-escriba" onclick="limpaDados()">
                                <i class="fa-solid fa-eraser"></i> <u>Limpar dados do depoente</u>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- DADOS DA GRAVAÇÃO -->
                <div class="card" id="dados_gravacao">
                    <div class="block-title">
                        <i class="fa-solid fa-file-video"></i> Dados da Gravação
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-3">
                            <label for="datagrav">Data:</label>
                            <input id="datagrav" type="date" class="form-control">
                        </div>

                        <div class="form-group col-3">
                            <label for="id_cidade">Cidade da Oitiva:</label>
                            <input id="id_cidade" type="text" class="form-control" value="Cascavel">
                        </div>

                        <div class="form-group col-2">
                            <label for="est_oitiva">Estado (UF):</label>
                            <input id="est_oitiva" type="text" class="form-control" maxlength="2" value="PR">
                        </div>

                        <div class="form-group col-4">
                            <label for="loc_oitiva">Local da Oitiva:</label>
                            <input id="loc_oitiva" type="text" class="form-control"
                                value="Sexto Batalhão de Polícia Militar">
                        </div>

                        <div class="form-group col-3">
                            <label for="entinicio">Início:</label>
                            <input id="entinicio" type="time" class="form-control">
                        </div>

                        <div class="form-group col-3">
                            <label for="entfim">Término:</label>
                            <input id="entfim" type="time" class="form-control">
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

                        <div class="form-group col-12">
                            <label>Houve alguma alteração durante a gravação do depoimento? <span
                                    class="required">*</span></label>
                            <div class="radio-inline-group">
                                <label><input name="ent_alt" type="radio" value="sim" onclick="func_alteracao()"> Sim
                                    (especifique)</label>
                                <label><input name="ent_alt" type="radio" value="nao" onclick="func_alteracao()">
                                    Não</label>
                            </div>
                            <div id="texto_alt" style="margin-top: 5px;"></div>
                        </div>

                        <div class="form-group col-12">
                            <label for="fileInput">Carregue o arquivo da oitiva:</label>
                            <input type="file" id="fileInput" class="form-control" onchange="calculateHash()"
                                oninput="geraprevia()">
                            <span class="link-escriba" onclick="onClick()" style="margin-top: 5px;">
                                <u><i class="fa-solid fa-plus"></i> Inserir mais vídeos (max. 4)</u>
                            </span>
                            <span id="ins_dois"></span>
                            <span id="ins_tres"></span>
                            <span id="ins_quatro"></span>
                        </div>

                        <div class="form-group col-6">
                            <canvas id="preview-canvas" style="max-width: 100%; border-radius: 4px;"></canvas>
                            <div id="intrucao" style="margin-top: 5px;"></div>
                        </div>

                        <div class="form-group col-6">
                            <fieldset class="inner-fieldset">
                                <legend>Imagem capturada</legend>
                                <img id="captured-image" style="display:none; max-width: 100%; height: auto;">
                            </fieldset>
                        </div>

                        <div class="form-group col-12">
                            <fieldset class="inner-fieldset">
                                <legend>Códigos HASH</legend>
                                <div class="form-grid">
                                    <div class="form-group col-12">
                                        <label>SHA-1:</label>
                                        <input id="sha1" type="text" class="form-control" readonly>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>SHA-256:</label>
                                        <input id="sha256" type="text" class="form-control" readonly>
                                    </div>
                                    <div class="col-12" id="se_dois"></div>
                                    <div class="col-12" id="se_tres"></div>
                                    <div class="col-12" id="se_quatro"></div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="form-group col-6">
                            <label>Duração do vídeo:</label>
                            <div style="display: flex; gap: 5px; align-items: center;">
                                <input id="min" type="text" class="form-control"
                                    style="width: 60px; text-align: center;" placeholder="min"> :
                                <input id="seg" type="text" class="form-control"
                                    style="width: 60px; text-align: center;" placeholder="seg">
                            </div>
                        </div>

                        <div class="form-group col-6" style="justify-content: flex-end; align-items: flex-end;">
                            <span id="limpar" class="link-escriba">
                                <i class="fa-solid fa-trash-can"></i> <u>Limpar dados de vídeo</u>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- BOTÃO DE AÇÃO -->
                <div class="btn-container" style="justify-content: center;">
                    <button id="sbmt" type="button" class="btn btn-green" onclick="sbmt1();">
                        <i class="fa-solid fa-floppy-disk"></i> Gravar Formulário
                    </button>
                </div>
                <div id="btnconf" class="btn-container" style="justify-content: center; margin-top: 10px;"></div>
                <div id="mostradados" style="margin-top: 10px;"></div>
            </form>
        </div>

        <!-- Inclui o Rodapé Centralizado -->
        <?php require_once 'footer.php'; ?>

    </div>

    <script src="./auxilio.js?v=2.0"></script>

</body>

</html>
