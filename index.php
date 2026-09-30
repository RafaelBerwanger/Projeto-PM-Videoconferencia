<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videoconferência / Gravação Audiovisual - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <?php
    session_start();
    // Se o usuário não estiver logado, redireciona de volta para a tela de login
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
    ?>

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Ficheiros CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_auxilio.css">

    <!-- Bibliotecas JS Externas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.2/dist/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.4.1/jspdf.debug.js"
        integrity="sha384-THVO/sM0mFD9h7dfSndI6TS0PgAGavwKvB5hAxRRvc0o9cPLohB0wb/PTA7LdUHs"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>

    <script src="./functionsJS.js"></script>
</head>

<body>

    <!-- BARRA LATERAL -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="img/logo.png" alt="Logo PMPR" class="sidebar-logo">
            <span>Ajuda do Encarregado</span>
        </div>

        <div class="sidebar-title">OITIVAS</div>

        <a href="index.php" class="menu-item active">
            <i class="fa-solid fa-video"></i>
            <span>Oitivas Audiovisuais</span>
        </a>
        <a href="presencial.php" class="menu-item">
            <i class="fa-solid fa-file-pen"></i>
            <span>Oitivas Escritas</span>
        </a>

        <!-- ITEM VISÍVEL APENAS SE O USUÁRIO FOR ADMIN -->
        <?php if (($_SESSION['usuario_perfil'] ?? '') === 'admin'): ?>
            <div class="sidebar-title" style="margin-top: 15px; color: #f59e0b;">ADMINISTRAÇÃO</div>
            <a href="admin_usuarios.php" class="menu-item">
                <i class="fa-solid fa-user-gear" style="color: #f59e0b;"></i>
                <span>Gestão de Acessos</span>
            </a>
        <?php endif; ?>

        <!-- Opção de Perfil para qualquer usuário -->
        <a href="meu_perfil.php" class="menu-item">
            <i class="fa-solid fa-user-pen"></i>
            <span>Meu Perfil</span>
        </a>

        <!-- No menu lateral (Sidebar) -->
        <a href="logout.php" class="menu-item logout-item">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Sair</span>
        </a>


    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-wrapper">

        <!-- NAV SUPERIOR -->
        <div class="top-nav">
            <div class="top-nav-left">
                <i class="fa-solid fa-bars"></i>
                <a href="#">Administração</a>
                <a href="#">Boletins</a>
                <a href="#">Sistemas</a>
            </div>

            <!-- DATA E HORA EM TEMPO REAL (HORÁRIO DE BRASÍLIA) -->
            <div class="top-nav-center" style="color: #64748b; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                <i class="fa-regular fa-clock"></i>
                <span id="relogio-brasilia">Carregando horário...</span>
            </div>

            <div class="top-nav-right">
                <span class="badge-email"><i class="fa-regular fa-envelope"></i> Emails</span>
                <i class="fa-regular fa-bell" style="color:#666;"></i>
                <div class="user-profile">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <!-- Exibe o nome armazenado na sessão do PHP -->
                    <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário Logado') ?></span>
                </div>
            </div>
        </div>

        <!-- BANNER DE TÍTULO -->
        <div class="page-banner">
            <i class="fa-solid fa-video"></i> Videoconferência / Gravação Audiovisual
        </div>

        <div class="content-area">

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

            <form>
                <!-- DADOS DO ENCARREGADO -->
                <div class="card" id="dados_encarregado">
                    <div class="block-title">
                        <i class="fa-solid fa-user-gear"></i> Dados do Encarregado
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-6">
                            <label for="entnome_enc">Nome:</label>
                            <input id="entnome_enc" type="text" class="form-control" placeholder="Nome do Encarregado">
                        </div>

                        <div class="form-group col-3">
                            <label for="entcni_enc"
                                title="Na certidão, os 3 primeiros dígitos serão ocultados, bem como os dois últimos (ex: XXX.123.123-XX)">CPF/CIN:</label>
                            <input id="entcni_enc" type="text" class="form-control" maxlength="14"
                                placeholder="000.000.000-00" onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <div class="form-group col-3">
                            <label for="ent_posto">Posto/Graduação:</label>
                            <select id="ent_posto" class="form-control">
                                <option value="Coronel">Coronel</option>
                                <option value="Tenente-Coronel">Tenente-Coronel</option>
                                <option value="Major">Major</option>
                                <option value="Capitão">Capitão</option>
                                <option value="1º Tenente">1º Tenente</option>
                                <option value="2º Tenente">2º Tenente</option>
                                <option value="Asp.">Asp. PM</option>
                                <option value="Subtenente">Subtenente</option>
                                <option value="1º Sargento">1º Sargento</option>
                                <option value="2º Sargento">2º Sargento</option>
                                <option value="3º Sargento" selected>3º Sargento</option>
                                <option value="Cabo">Cabo</option>
                                <option value="Soldado">Soldado</option>
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

                        <div class="form-group col-2">
                            <label for="opm">OPM:</label>
                            <select name="opm" id="opm" class="form-control">
                                <option value="1º BATALHÃO DE POLÍCIA MILITAR">1º BPM</option>
                                <option value="2º BATALHÃO DE POLÍCIA MILITAR">2º BPM</option>
                                <option value="3º BATALHÃO DE POLÍCIA MILITAR">3º BPM</option>
                                <option value="4º BATALHÃO DE POLÍCIA MILITAR">4º BPM</option>
                                <option value="5º BATALHÃO DE POLÍCIA MILITAR">5º BPM</option>
                                <option value="6º BATALHÃO DE POLÍCIA MILITAR" selected>6º BPM</option>
                                <option value="7º BATALHÃO DE POLÍCIA MILITAR">7º BPM</option>
                                <option value="8º BATALHÃO DE POLÍCIA MILITAR">8º BPM</option>
                                <option value="9º BATALHÃO DE POLÍCIA MILITAR">9º BPM</option>
                                <option value="10º BATALHÃO DE POLÍCIA MILITAR">10º BPM</option>
                                <option value="11º BATALHÃO DE POLÍCIA MILITAR">11º BPM</option>
                                <option value="12º BATALHÃO DE POLÍCIA MILITAR">12º BPM</option>
                                <option value="13º BATALHÃO DE POLÍCIA MILITAR">13º BPM</option>
                                <option value="14º BATALHÃO DE POLÍCIA MILITAR">14º BPM</option>
                                <option value="15º BATALHÃO DE POLÍCIA MILITAR">15º BPM</option>
                                <option value="16º BATALHÃO DE POLÍCIA MILITAR">16º BPM</option>
                                <option value="17º BATALHÃO DE POLÍCIA MILITAR">17º BPM</option>
                                <option value="18º BATALHÃO DE POLÍCIA MILITAR">18º BPM</option>
                                <option value="19º BATALHÃO DE POLÍCIA MILITAR">19º BPM</option>
                                <option value="20º BATALHÃO DE POLÍCIA MILITAR">20º BPM</option>
                                <option value="21º BATALHÃO DE POLÍCIA MILITAR">21º BPM</option>
                                <option value="22º BATALHÃO DE POLÍCIA MILITAR">22º BPM</option>
                                <option value="23º BATALHÃO DE POLÍCIA MILITAR">23º BPM</option>
                                <option value="24º BATALHÃO DE POLÍCIA MILITAR">24º BPM</option>
                                <option value="25º BATALHÃO DE POLÍCIA MILITAR">25º BPM</option>
                                <option value="26º BATALHÃO DE POLÍCIA MILITAR">26º BPM</option>
                                <option value="27º BATALHÃO DE POLÍCIA MILITAR">27º BPM</option>
                                <option value="28º BATALHÃO DE POLÍCIA MILITAR">28º BPM</option>
                                <option value="29º BATALHÃO DE POLÍCIA MILITAR">29º BPM</option>
                                <option value="30º BATALHÃO DE POLÍCIA MILITAR">30º BPM</option>
                                <option value="31º BATALHÃO DE POLÍCIA MILITAR">31º BPM</option>
                                <option value="BATALHÃO DE POLÍCIA DE FRONTEIRA">BPFRON</option>
                                <option value="BATALHÃO DE RONDAS OSTENSIVAS DE NATUREZA ESPECIAL">BPRONE</option>
                                <option value="BATALHÃO DE POLÍCIA DE CHOQUE">BPCHOQUE</option>
                                <option value="BATALHÃO DE POLÍCIA RODOVIÁRIA">BPRv</option>
                                <option value="BATALHÃO DE PATRULHA ESCOLAR COMUNITÁRIA">BPEC</option>
                                <option value="BATALHÃO DE POLÍCIA MILITAR DE OPERAÇÕES AÉREAS">BPMOA</option>
                                <option value="REGIMENTO DE POLÍCIA MONTADA - &quot;CORONEL DULCÍDIO&quot;">RPMon
                                </option>
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
                                            <option value="Coronel">Coronel</option>
                                            <option value="Tenente-Coronel">Tenente-Coronel</option>
                                            <option value="Major">Major</option>
                                            <option value="Capitão">Capitão</option>
                                            <option value="1º Tenente">1º Tenente</option>
                                            <option value="2º Tenente">2º Tenente</option>
                                            <option value="Asp.">Asp. PM</option>
                                            <option value="Subtenente">Subtenente</option>
                                            <option value="1º Sargento">1º Sargento</option>
                                            <option value="2º Sargento">2º Sargento</option>
                                            <option value="3º Sargento" selected>3º Sargento</option>
                                            <option value="Cabo">Cabo</option>
                                            <option value="Soldado">Soldado</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="quadro_esc">Quadro:</label>
                                        <select id="quadro_esc" class="form-control">
                                            <option value="QP PM">QP PM</option>
                                            <option value="QOEM PM">QOEM PM</option>
                                            <option value="PM">Sem quadro</option>
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
                    <button id="sbmt" type="button" class="btn btn-green"
                        onclick="sbmt1(); func_alteracao(); formatar(); confirmar(); inc_cost(); inc_alt();">
                        <i class="fa-solid fa-floppy-disk"></i> Gravar Formulário
                    </button>
                </div>
                <div id="btnconf" style="text-align: center; margin-top: 10px;"></div>
                <div id="mostradados" style="margin-top: 10px;"></div>
                <div id="testpdf"></div>
            </form>
        </div>

        <footer>
            <div id="rodape">
                Desenvolvido por Cad 1º PM. Rafael A. Berwanger — EsFO/APMG
            </div>
        </footer>

    </div>

    <script src="./auxilio.js"></script>

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