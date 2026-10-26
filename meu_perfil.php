<?php
require_once 'sessao.php';
require_once 'listas.php';

$user       = exigirLogin();
$id_usuario = (int) $user['id'];
$mensagem   = '';
$tipo_msg   = '';

// Processa a atualização do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [];
    foreach (['nome', 'rg', 'cpf', 'email', 'posto_graduacao', 'unidade', 'telefone'] as $c) {
        $dados[$c] = trim($_POST[$c] ?? '');
    }
    $nova_senha = $_POST['nova_senha'] ?? '';

    $erro = csrf_valido() ? validarDadosCadastrais($dados) : 'Sessão expirada. Envie o formulário novamente.';
    if (!$erro && $nova_senha !== '' && strlen($nova_senha) < 6) {
        $erro = 'A nova senha deve ter no mínimo 6 caracteres.';
    }

    if (!$erro) {
        // CPF e e-mail não podem pertencer a outro usuário
        $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE (cpf = :cpf OR email = :email) AND id <> :id LIMIT 1');
        $stmtCheck->execute(['cpf' => $dados['cpf'], 'email' => $dados['email'], 'id' => $id_usuario]);
        if ($stmtCheck->fetch()) {
            $erro = 'CPF ou e-mail já cadastrados para outro usuário.';
        }
    }

    if (!$erro) {
        $sql = 'UPDATE usuarios SET nome = :nome, rg = :rg, cpf = :cpf, email = :email, posto_graduacao = :posto, unidade = :unidade, telefone = :telefone';
        $params = [
            'nome'     => $dados['nome'],
            'rg'       => $dados['rg'],
            'cpf'      => $dados['cpf'],
            'email'    => $dados['email'],
            'posto'    => $dados['posto_graduacao'],
            'unidade'  => $dados['unidade'],
            'telefone' => $dados['telefone'],
            'id'       => $id_usuario,
        ];
        if ($nova_senha !== '') {
            $sql .= ', senha = :senha';
            $params['senha'] = password_hash($nova_senha, PASSWORD_DEFAULT);
        }

        try {
            $pdo->prepare($sql . ' WHERE id = :id')->execute($params);

            // Atualiza a sessão ativa
            $_SESSION['usuario_nome']            = $dados['nome'];
            $_SESSION['usuario_posto_graduacao'] = $dados['posto_graduacao'];
            $_SESSION['usuario_unidade']         = $dados['unidade'];

            $mensagem = 'Dados do perfil atualizados com sucesso!';
            $tipo_msg = 'sucesso';
        } catch (PDOException $e) {
            error_log('Erro ao atualizar perfil: ' . $e->getMessage());
            $erro = 'Erro ao salvar os dados. Tente novamente.';
        }
    }

    if ($erro) {
        $mensagem = $erro;
        $tipo_msg = 'erro';
    }

    // Recarrega os dados do banco (ou mantém o que foi digitado em caso de erro)
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id_usuario]);
    $user = $erro ? array_merge($stmt->fetch(), $dados) : $stmt->fetch();
}

$valPosto   = $user['posto_graduacao'] ?? '';
$valUnidade = $user['unidade'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Intranet PMPR</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Ficheiros CSS da Intranet PMPR -->
    <link rel="stylesheet" href="style.css?v=1.0.1">
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

    <!-- BARRA LATERAL DA INTRANET PMPR -->
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
                <div class="msg-alerta <?= e($tipo_msg) ?>">
                    <i class="fa-solid <?= $tipo_msg === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <?= e($mensagem) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="meu_perfil.php">
                <?= csrf_campo() ?>

                <!-- CARD DE DADOS CADASTRAIS -->
                <div class="card">
                    <div class="block-title">
                        <i class="fa-solid fa-id-card"></i> Informações Pessoais e Institucionais
                    </div>

                    <div class="form-grid">

                        <!-- Posto / Graduação -->
                        <div class="form-group col-6">
                            <label for="posto_graduacao">Posto / Graduação: <span class="required">*</span></label>
                            <select id="posto_graduacao" name="posto_graduacao" class="form-control" required>
                                <?= renderOpcoes(LISTA_POSTOS, $valPosto) ?>
                            </select>
                        </div>

                        <!-- Nome Completo -->
                        <div class="form-group col-6">
                            <label for="nome">Nome Completo: <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" value="<?= e($user['nome'] ?? '') ?>" required>
                        </div>

                        <!-- RG -->
                        <div class="form-group col-3">
                            <label for="rg">RG: <span class="required">*</span></label>
                            <input type="text" id="rg" name="rg" class="form-control" value="<?= e($user['rg'] ?? '') ?>" required>
                        </div>

                        <!-- CPF -->
                        <div class="form-group col-3">
                            <label for="cpf">CPF: <span class="required">*</span></label>
                            <input type="text" id="cpf" name="cpf" class="form-control" value="<?= e($user['cpf'] ?? '') ?>" maxlength="14" required oninput="aplicarMascaraCPF(this)">
                        </div>

                        <!-- E-MAIL (RECUPERADO DO BANCO DE DADOS) -->
                        <div class="form-group col-3">
                            <label for="email">E-mail: <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@pm.pr.gov.br" value="<?= e($user['email'] ?? '') ?>" required>
                        </div>

                        <!-- Telefone -->
                        <div class="form-group col-3">
                            <label for="telefone">Telefone / WhatsApp: <span class="required">*</span></label>
                            <input type="text" id="telefone" name="telefone" class="form-control" value="<?= e($user['telefone'] ?? '') ?>" required oninput="aplicarMascaraTelefone(this)" maxlength="15">
                        </div>

                        <!-- Unidade / OPM -->
                        <div class="form-group col-12">
                            <label for="unidade">Unidade / OPM de Origem: <span class="required">*</span></label>
                            <select id="unidade" name="unidade" class="form-control" required>
                                <?= renderOpcoes(LISTA_UNIDADES, $valUnidade, 'Selecione a Unidade...') ?>
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
                            <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="mín. 6 caracteres" minlength="6" autocomplete="new-password">
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
        <?php require_once 'footer.php'; ?>

    </div>

    <script>
        function aplicarMascaraCPF(i) {
            let v = i.value.replace(/\D/g, "").slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            i.value = v;
        }

        function aplicarMascaraTelefone(i) {
            let v = i.value.replace(/\D/g, "").slice(0, 11);
            v = v.replace(/^(\d{2})(\d)/, "($1) $2");
            v = v.replace(/(\d)(\d{4})$/, "$1-$2");
            i.value = v;
        }
    </script>

</body>

</html>
