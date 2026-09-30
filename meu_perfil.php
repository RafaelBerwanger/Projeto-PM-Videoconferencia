<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <?php
    session_start();
    require_once 'db.php';

    // Bloqueia acesso de usuários não logados
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    $id_usuario = $_SESSION['usuario_id'];
    $mensagem = '';
    $tipo_msg = '';

    // Função auxiliar para marcar o elemento 'selected' no select
    function estaSelecionado($valorOpcao, $valorBanco)
    {
        return ($valorOpcao === $valorBanco) ? 'selected' : '';
    }

    // Processa a atualização do formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome            = trim($_POST['nome'] ?? '');
        $rg              = trim($_POST['rg'] ?? '');
        $cpf             = trim($_POST['cpf'] ?? '');
        $posto_graduacao = trim($_POST['posto_graduacao'] ?? '');
        $unidade         = trim($_POST['unidade'] ?? '');
        $telefone        = trim($_POST['telefone'] ?? '');
        $nova_senha      = trim($_POST['nova_senha'] ?? '');

        if (!empty($nome) && !empty($rg) && !empty($cpf) && !empty($posto_graduacao) && !empty($unidade) && !empty($telefone)) {

            if (!empty($nova_senha)) {
                // Atualiza com nova senha
                $hashSenha = password_hash($nova_senha, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE usuarios SET nome = :nome, rg = :rg, cpf = :cpf, posto_graduacao = :posto, unidade = :unidade, telefone = :telefone, senha = :senha WHERE id = :id');
                $stmt->execute([
                    'nome'     => $nome,
                    'rg'       => $rg,
                    'cpf'      => $cpf,
                    'posto'    => $posto_graduacao,
                    'unidade'  => $unidade,
                    'telefone' => $telefone,
                    'senha'    => $hashSenha,
                    'id'       => $id_usuario
                ]);
            } else {
                // Atualiza sem alterar senha
                $stmt = $pdo->prepare('UPDATE usuarios SET nome = :nome, rg = :rg, cpf = :cpf, posto_graduacao = :posto, unidade = :unidade, telefone = :telefone WHERE id = :id');
                $stmt->execute([
                    'nome'     => $nome,
                    'rg'       => $rg,
                    'cpf'      => $cpf,
                    'posto'    => $posto_graduacao,
                    'unidade'  => $unidade,
                    'telefone' => $telefone,
                    'id'       => $id_usuario
                ]);
            }

            // Atualiza sessão
            $_SESSION['usuario_nome']            = $nome;
            $_SESSION['usuario_posto_graduacao'] = $posto_graduacao;
            $_SESSION['usuario_unidade']         = $unidade;

            $mensagem = 'Dados do perfil atualizados com sucesso!';
            $tipo_msg = 'sucesso';
        } else {
            $mensagem = 'Preencha todos os campos obrigatórios.';
            $tipo_msg = 'erro';
        }
    }

    // Busca dados atualizados no banco
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id_usuario]);
    $user = $stmt->fetch();

    $valPosto       = $user['posto_graduacao'] ?? '';
    $valUnidade     = $user['unidade'] ?? '';
    $nome_usuario   = $_SESSION['usuario_nome'] ?? 'Policial Militar';
    $perfil_usuario = $_SESSION['usuario_perfil'] ?? 'usuario';
    ?>

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Ficheiros CSS da Intranet PMPR -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_auxilio.css">

    <style>
        .msg-alerta {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .msg-alerta.sucesso {
            background-color: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
        }

        .msg-alerta.erro {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
    </style>
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
            <i class="fa-solid fa-user-gear"></i> Editar Meu Perfil Cadastral
        </div>

        <div class="content-area">

            <?php if (!empty($mensagem)): ?>
                <div class="msg-alerta <?= $tipo_msg ?>">
                    <i class="fa-solid <?= $tipo_msg === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="meu_perfil.php">

                <!-- CARD DE DADOS CADASTRAIS -->
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-id-card"></i> Informações Pessoais e Institucionais
                    </div>

                    <div class="form-grid">

                        <!-- Posto / Graduação (Select com valores do cadastro) -->
                        <div class="form-group col-6">
                            <label for="posto_graduacao">Posto / Graduação: <span class="required">*</span></label>
                            <select id="posto_graduacao" name="posto_graduacao" class="form-control" required>
                                <option value="" disabled>Selecione...</option>

                                <optgroup label="Oficiais Superiores">
                                    <option value="Cel." <?= estaSelecionado('Cel.', $valPosto) ?>>Coronel (Cel.)</option>
                                    <option value="Ten.-Cel." <?= estaSelecionado('Ten.-Cel.', $valPosto) ?>>Tenente-Coronel (Ten.-Cel.)</option>
                                    <option value="Maj." <?= estaSelecionado('Maj.', $valPosto) ?>>Major (Maj.)</option>
                                </optgroup>

                                <optgroup label="Oficiais Intermediários e Subalternos">
                                    <option value="Cap." <?= estaSelecionado('Cap.', $valPosto) ?>>Capitão (Cap.)</option>
                                    <option value="1º Ten." <?= estaSelecionado('1º Ten.', $valPosto) ?>>1º Tenente (1º Ten.)</option>
                                    <option value="2º Ten." <?= estaSelecionado('2º Ten.', $valPosto) ?>>2º Tenente (2º Ten.)</option>
                                    <option value="Asp. a Of." <?= estaSelecionado('Asp. a Of.', $valPosto) ?>>Aspirante-a-Oficial (Asp. a Of.)</option>
                                </optgroup>

                                <optgroup label="Praças Especiais">
                                    <option value="Cadete 3º Ano" <?= estaSelecionado('Cadete 3º Ano', $valPosto) ?>>Cadete 3º Ano</option>
                                    <option value="Cadete 2º Ano" <?= estaSelecionado('Cadete 2º Ano', $valPosto) ?>>Cadete 2º Ano</option>
                                    <option value="Cadete 1º Ano" <?= estaSelecionado('Cadete 1º Ano', $valPosto) ?>>Cadete 1º Ano</option>
                                </optgroup>

                                <optgroup label="Praças Graduados">
                                    <option value="Subten." <?= estaSelecionado('Subten.', $valPosto) ?>>Subtenente (Subten.)</option>
                                    <option value="1º Sgt." <?= estaSelecionado('1º Sgt.', $valPosto) ?>>1º Sargento (1º Sgt.)</option>
                                    <option value="2º Sgt." <?= estaSelecionado('2º Sgt.', $valPosto) ?>>2º Sargento (2º Sgt.)</option>
                                    <option value="3º Sgt." <?= estaSelecionado('3º Sgt.', $valPosto) ?>>3º Sargento (3º Sgt.)</option>
                                    <option value="Cabo" <?= estaSelecionado('Cabo', $valPosto) ?>>Cabo (Cb.)</option>
                                </optgroup>

                                <optgroup label="Praças">
                                    <option value="Soldado 1ª Classe" <?= estaSelecionado('Soldado 1ª Classe', $valPosto) ?>>Soldado 1ª Classe (Sd. 1ª Cl.)</option>
                                    <option value="Soldado 2ª Classe" <?= estaSelecionado('Soldado 2ª Classe', $valPosto) ?>>Soldado 2ª Classe (Sd. 2ª Cl. - Aluno)</option>
                                </optgroup>

                            </select>
                        </div>

                        <!-- Nome Completo -->
                        <div class="form-group col-6">
                            <label for="nome">Nome Completo: <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required>
                        </div>

                        <!-- RG -->
                        <div class="form-group col-4">
                            <label for="rg">RG: <span class="required">*</span></label>
                            <input type="text" id="rg" name="rg" class="form-control" value="<?= htmlspecialchars($user['rg'] ?? '') ?>" required>
                        </div>

                        <!-- CPF -->
                        <div class="form-group col-4">
                            <label for="cpf">CPF: <span class="required">*</span></label>
                            <input type="text" id="cpf" name="cpf" class="form-control" value="<?= htmlspecialchars($user['cpf'] ?? '') ?>" maxlength="14" required onkeyup="aplicarMascaraCPF(this)">
                        </div>

                        <!-- Telefone -->
                        <div class="form-group col-4">
                            <label for="telefone">Telefone / WhatsApp: <span class="required">*</span></label>
                            <input type="text" id="telefone" name="telefone" class="form-control" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" required onkeyup="aplicarMascaraTelefone(this)">
                        </div>

                        <!-- Unidade / OPM -->
                        <div class="form-group col-12">
                            <label for="unidade">Unidade / OPM de Origem: <span class="required">*</span></label>
                            <select id="unidade" name="unidade" class="form-control" required>
                                <option value="" disabled>Selecione a Unidade...</option>
                                <optgroup label="Comandos Regionais (CRPM)">
                                    <option value="1º COMANDO REGIONAL" <?= estaSelecionado('1º COMANDO REGIONAL', $valUnidade) ?>>1º CRPM - Curitiba</option>
                                    <option value="2º COMANDO REGIONAL" <?= estaSelecionado('2º COMANDO REGIONAL', $valUnidade) ?>>2º CRPM - Londrina</option>
                                    <option value="3º COMANDO REGIONAL" <?= estaSelecionado('3º COMANDO REGIONAL', $valUnidade) ?>>3º CRPM - Maringá</option>
                                    <option value="4º COMANDO REGIONAL" <?= estaSelecionado('4º COMANDO REGIONAL', $valUnidade) ?>>4º CRPM - Ponta Grossa</option>
                                    <option value="5º COMANDO REGIONAL" <?= estaSelecionado('5º COMANDO REGIONAL', $valUnidade) ?>>5º CRPM - Cascavel</option>
                                    <option value="6º COMANDO REGIONAL" <?= estaSelecionado('6º COMANDO REGIONAL', $valUnidade) ?>>6º CRPM - São José dos Pinhais</option>
                                    <option value="7º COMANDO REGIONAL" <?= estaSelecionado('7º COMANDO REGIONAL', $valUnidade) ?>>7º CRPM - Pato Branco</option>
                                </optgroup>
                                <optgroup label="Batalhões de Polícia Militar (BPM)">
                                    <option value="1º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('1º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>1º BPM - Ponta Grossa</option>
                                    <option value="5º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('5º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>5º BPM - Londrina</option>
                                    <option value="6º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('6º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>6º BPM - Cascavel</option>
                                    <option value="12º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('12º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>12º BPM - Curitiba</option>
                                    <option value="13º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('13º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>13º BPM - Curitiba</option>
                                    <option value="14º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('14º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>14º BPM - Foz do Iguaçu</option>
                                    <option value="19º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('19º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>19º BPM - Toledo</option>
                                    <option value="31º BATALHÃO DE POLÍCIA MILITAR" <?= estaSelecionado('31º BATALHÃO DE POLÍCIA MILITAR', $valUnidade) ?>>31º BPM - Assis Chateaubriand</option>
                                </optgroup>
                                <optgroup label="Batalhões Especializados">
                                    <option value="BATALHÃO DE POLÍCIA DE FRONTEIRA" <?= estaSelecionado('BATALHÃO DE POLÍCIA DE FRONTEIRA', $valUnidade) ?>>BPFRON - Marechal Cândido Rondon</option>
                                    <option value="BATALHÃO DE RONDAS OSTENSIVAS DE NATUREZA ESPECIAL" <?= estaSelecionado('BATALHÃO DE RONDAS OSTENSIVAS DE NATUREZA ESPECIAL', $valUnidade) ?>>BPRONE - Curitiba</option>
                                </optgroup>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- CARD DE SEGURANÇA / SENHA -->
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-lock"></i> Segurança e Alteração de Senha
                    </div>

                    <div class="form-grid">
                        <div class="form-group col-12">
                            <label for="nova_senha">Nova Senha: <span style="font-weight: normal; color: #64748b;">(Deixe em branco caso não queira alterar)</span></label>
                            <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <!-- BOTÃO DE AÇÃO -->
                <div class="btn-container" style="justify-content: center;">
                    <button type="submit" class="btn btn-green">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
                    </button>
                    <a href="index.php" class="btn btn-blue" style="background-color: #64748b; border-color: #64748b; text-decoration: none;">
                        <i class="fa-solid fa-arrow-left"></i> Voltar
                    </a>
                </div>

            </form>

        </div>

        <!-- RODAPÉ -->
        <!-- Inclui o Rodapé Centralizado -->
        <?php require_once 'footer.php'; ?>

    </div>

    <script>
        function aplicarMascaraCPF(i) {
            let v = i.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            i.value = v;
        }

        function aplicarMascaraTelefone(i) {
            let v = i.value.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d{5})(\d)/, "$1-$2");
            i.value = v;
        }

        function atualizarRelogioBrasilia() {
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

        atualizarRelogioBrasilia();
        setInterval(atualizarRelogioBrasilia, 1000);
    </script>

</body>

</html>