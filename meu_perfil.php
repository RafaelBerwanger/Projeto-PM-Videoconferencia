<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$mensagem = '';
$tipo_msg = '';

// Função auxiliar para verificar e marcar o atributo 'selected' do HTML
function estaSelecionado($valorOpcao, $valorBanco) {
    return ($valorOpcao === $valorBanco) ? 'selected' : '';
}

// Processa a atualização dos dados
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
            // Atualiza dados cadastrais + senha
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
            // Atualiza apenas dados cadastrais
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

        // Atualiza os dados gravados na sessão
        $_SESSION['usuario_nome']            = $nome;
        $_SESSION['usuario_posto_graduacao'] = $posto_graduacao;
        $_SESSION['usuario_unidade']         = $unidade;

        $mensagem = 'Perfil atualizado com sucesso!';
        $tipo_msg = 'success';
    } else {
        $mensagem = 'Preencha todos os campos obrigatórios.';
        $tipo_msg = 'error';
    }
}

// Busca as informações atuais do usuário logado
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$user = $stmt->fetch();

$valPosto   = $user['posto_graduacao'] ?? '';
$valUnidade = $user['unidade'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil - Oitivas PMPR</title>
  <link rel="stylesheet" href="style_login.css?v=1.4">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

  <div class="login-container" style="max-width: 520px;">
    <div class="login-header">
      <div class="logo-icon"><i class="fa-solid fa-user-pen"></i></div>
      <h2>Editar Meu Perfil</h2>
      <p>Atualize suas informações pessoais e institucionais</p>
    </div>

    <?php if (!empty($mensagem)): ?>
      <div class="message-box <?= $tipo_msg ?>" style="display: block;">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="meu_perfil.php">
      
      <!-- Posto / Graduação (Select) -->
      <div class="input-group">
        <label for="posto_graduacao">Posto / Graduação</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-ranking-star input-icon"></i>
          <select id="posto_graduacao" name="posto_graduacao" required class="custom-select">
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
      </div>

      <!-- Nome Completo -->
      <div class="input-group">
        <label for="nome">Nome Completo</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-id-card input-icon"></i>
          <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required>
        </div>
      </div>

      <!-- RG e CPF -->
      <div style="display: flex; gap: 10px;">
        <div class="input-group" style="flex: 1;">
          <label for="rg">RG</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-address-card input-icon"></i>
            <input type="text" id="rg" name="rg" value="<?= htmlspecialchars($user['rg']) ?>" required>
          </div>
        </div>
        <div class="input-group" style="flex: 1;">
          <label for="cpf">CPF</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-fingerprint input-icon"></i>
            <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($user['cpf']) ?>" required oninput="mascaraCPF(this)">
          </div>
        </div>
      </div>

      <!-- Unidade / OPM (Select Completo) -->
      <div class="input-group">
        <label for="unidade">Unidade / OPM</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-building-shield input-icon"></i>
          <select id="unidade" name="unidade" class="custom-select" required>
            <option value="" disabled>Selecione a Unidade...</option>

            <optgroup label="Comandos Regionais (CRPM)">
              <option value="1º CRPM - Curitiba" <?= estaSelecionado('1º CRPM - Curitiba', $valUnidade) ?>>1º CRPM - Curitiba</option>
              <option value="2º CRPM - Londrina" <?= estaSelecionado('2º CRPM - Londrina', $valUnidade) ?>>2º CRPM - Londrina</option>
              <option value="3º CRPM - Maringá" <?= estaSelecionado('3º CRPM - Maringá', $valUnidade) ?>>3º CRPM - Maringá</option>
              <option value="4º CRPM - Ponta Grossa" <?= estaSelecionado('4º CRPM - Ponta Grossa', $valUnidade) ?>>4º CRPM - Ponta Grossa</option>
              <option value="5º CRPM - Cascavel" <?= estaSelecionado('5º CRPM - Cascavel', $valUnidade) ?>>5º CRPM - Cascavel</option>
              <option value="6º CRPM - São José dos Pinhais" <?= estaSelecionado('6º CRPM - São José dos Pinhais', $valUnidade) ?>>6º CRPM - São José dos Pinhais</option>
              <option value="7º CRPM - Pato Branco" <?= estaSelecionado('7º CRPM - Pato Branco', $valUnidade) ?>>7º CRPM - Pato Branco</option>
            </optgroup>

            <optgroup label="Comandos Especializados e Apoio">
              <option value="CME - Curitiba" <?= estaSelecionado('CME - Curitiba', $valUnidade) ?>>CME - Curitiba</option>
              <option value="CPE - Curitiba" <?= estaSelecionado('CPE - Curitiba', $valUnidade) ?>>CPE - Curitiba</option>
              <option value="COMAV - Curitiba" <?= estaSelecionado('COMAV - Curitiba', $valUnidade) ?>>COMAV - Curitiba</option>
              <option value="CIROCAM - Curitiba" <?= estaSelecionado('CIROCAM - Curitiba', $valUnidade) ?>>CIROCAM - Curitiba</option>
              <option value="COPOM - Curitiba" <?= estaSelecionado('COPOM - Curitiba', $valUnidade) ?>>COPOM - Curitiba</option>
            </optgroup>

            <optgroup label="Batalhões de Polícia Militar (BPM)">
              <option value="1º BPM - Ponta Grossa" <?= estaSelecionado('1º BPM - Ponta Grossa', $valUnidade) ?>>1º BPM - Ponta Grossa</option>
              <option value="2º BPM - Jacarezinho" <?= estaSelecionado('2º BPM - Jacarezinho', $valUnidade) ?>>2º BPM - Jacarezinho</option>
              <option value="3º BPM - Pato Branco" <?= estaSelecionado('3º BPM - Pato Branco', $valUnidade) ?>>3º BPM - Pato Branco</option>
              <option value="4º BPM - Maringá" <?= estaSelecionado('4º BPM - Maringá', $valUnidade) ?>>4º BPM - Maringá</option>
              <option value="5º BPM - Londrina" <?= estaSelecionado('5º BPM - Londrina', $valUnidade) ?>>5º BPM - Londrina</option>
              <option value="6º BPM - Cascavel" <?= estaSelecionado('6º BPM - Cascavel', $valUnidade) ?>>6º BPM - Cascavel</option>
              <option value="7º BPM - Cruzeiro do Oeste" <?= estaSelecionado('7º BPM - Cruzeiro do Oeste', $valUnidade) ?>>7º BPM - Cruzeiro do Oeste</option>
              <option value="8º BPM - Paranavaí" <?= estaSelecionado('8º BPM - Paranavaí', $valUnidade) ?>>8º BPM - Paranavaí</option>
              <option value="9º BPM - Paranaguá" <?= estaSelecionado('9º BPM - Paranaguá', $valUnidade) ?>>9º BPM - Paranaguá</option>
              <option value="10º BPM - Apucarana" <?= estaSelecionado('10º BPM - Apucarana', $valUnidade) ?>>10º BPM - Apucarana</option>
              <option value="11º BPM - Campo Mourão" <?= estaSelecionado('11º BPM - Campo Mourão', $valUnidade) ?>>11º BPM - Campo Mourão</option>
              <option value="12º BPM - Curitiba" <?= estaSelecionado('12º BPM - Curitiba', $valUnidade) ?>>12º BPM - Curitiba</option>
              <option value="13º BPM - Curitiba" <?= estaSelecionado('13º BPM - Curitiba', $valUnidade) ?>>13º BPM - Curitiba</option>
              <option value="14º BPM - Foz do Iguaçu" <?= estaSelecionado('14º BPM - Foz do Iguaçu', $valUnidade) ?>>14º BPM - Foz do Iguaçu</option>
              <option value="15º BPM - Rolândia" <?= estaSelecionado('15º BPM - Rolândia', $valUnidade) ?>>15º BPM - Rolândia</option>
              <option value="16º BPM - Guarapuava" <?= estaSelecionado('16º BPM - Guarapuava', $valUnidade) ?>>16º BPM - Guarapuava</option>
              <option value="17º BPM - São José dos Pinhais" <?= estaSelecionado('17º BPM - São José dos Pinhais', $valUnidade) ?>>17º BPM - São José dos Pinhais</option>
              <option value="18º BPM - Cornélio Procópio" <?= estaSelecionado('18º BPM - Cornélio Procópio', $valUnidade) ?>>18º BPM - Cornélio Procópio</option>
              <option value="19º BPM - Toledo" <?= estaSelecionado('19º BPM - Toledo', $valUnidade) ?>>19º BPM - Toledo</option>
              <option value="20º BPM - Curitiba" <?= estaSelecionado('20º BPM - Curitiba', $valUnidade) ?>>20º BPM - Curitiba</option>
              <option value="21º BPM - Francisco Beltrão" <?= estaSelecionado('21º BPM - Francisco Beltrão', $valUnidade) ?>>21º BPM - Francisco Beltrão</option>
              <option value="22º BPM - Colombo" <?= estaSelecionado('22º BPM - Colombo', $valUnidade) ?>>22º BPM - Colombo</option>
              <option value="23º BPM – Curitiba" <?= estaSelecionado('23º BPM – Curitiba', $valUnidade) ?>>23º BPM – Curitiba</option>
              <option value="25º BPM - Umuarama" <?= estaSelecionado('25º BPM - Umuarama', $valUnidade) ?>>25º BPM - Umuarama</option>
              <option value="26º BPM - Telêmaco Borba" <?= estaSelecionado('26º BPM - Telêmaco Borba', $valUnidade) ?>>26º BPM - Telêmaco Borba</option>
              <option value="27º BPM - União da Vitória" <?= estaSelecionado('27º BPM - União da Vitória', $valUnidade) ?>>27º BPM - União da Vitória</option>
              <option value="28° BPM - Lapa" <?= estaSelecionado('28° BPM - Lapa', $valUnidade) ?>>28° BPM - Lapa</option>
              <option value="29º BPM - Piraquara" <?= estaSelecionado('29º BPM - Piraquara', $valUnidade) ?>>29º BPM - Piraquara</option>
              <option value="30º BPM - Londrina" <?= estaSelecionado('30º BPM - Londrina', $valUnidade) ?>>30º BPM - Londrina</option>
              <option value="31º BPM - Assis Chateaubriand" <?= estaSelecionado('31º BPM - Assis Chateaubriand', $valUnidade) ?>>31º BPM - Assis Chateaubriand</option>
              <option value="32º BPM - Sarandi" <?= estaSelecionado('32º BPM - Sarandi', $valUnidade) ?>>32º BPM - Sarandi</option>
              <option value="33° BPM - Curitiba" <?= estaSelecionado('33° BPM - Curitiba', $valUnidade) ?>>33° BPM - Curitiba</option>
              <option value="34º BPM - Almirante Tamandaré" <?= estaSelecionado('34º BPM - Almirante Tamandaré', $valUnidade) ?>>34º BPM - Almirante Tamandaré</option>
            </optgroup>

            <optgroup label="Companhias Independentes (CIPM)">
              <option value="3ª CIPM - Loanda" <?= estaSelecionado('3ª CIPM - Loanda', $valUnidade) ?>>3ª CIPM - Loanda</option>
              <option value="5ª CIPM - Cianorte" <?= estaSelecionado('5ª CIPM - Cianorte', $valUnidade) ?>>5ª CIPM - Cianorte</option>
              <option value="6ª CIPM - Ivaiporã" <?= estaSelecionado('6ª CIPM - Ivaiporã', $valUnidade) ?>>6ª CIPM - Ivaiporã</option>
              <option value="7ª CIPM - Arapongas" <?= estaSelecionado('7ª CIPM - Arapongas', $valUnidade) ?>>7ª CIPM - Arapongas</option>
              <option value="8ª CIPM - Irati" <?= estaSelecionado('8ª CIPM - Irati', $valUnidade) ?>>8ª CIPM - Irati</option>
              <option value="9ª CIPM - Colorado" <?= estaSelecionado('9ª CIPM - Colorado', $valUnidade) ?>>9ª CIPM - Colorado</option>
              <option value="10ª CIPM - Laranjeiras do Sul" <?= estaSelecionado('10ª CIPM - Laranjeiras do Sul', $valUnidade) ?>>10ª CIPM - Laranjeiras do Sul</option>
              <option value="11ª CIPM - Cambé" <?= estaSelecionado('11ª CIPM - Cambé', $valUnidade) ?>>11ª CIPM - Cambé</option>
              <option value="12ª CIPM - Palmas" <?= estaSelecionado('12ª CIPM - Palmas', $valUnidade) ?>>12ª CIPM - Palmas</option>
            </optgroup>

            <optgroup label="Batalhões Especializados">
              <option value="BOPE - Piraquara" <?= estaSelecionado('BOPE - Piraquara', $valUnidade) ?>>BOPE - Piraquara</option>
              <option value="BPAmb - São José dos Pinhais" <?= estaSelecionado('BPAmb - São José dos Pinhais', $valUnidade) ?>>BPAmb - São José dos Pinhais</option>
              <option value="BPChoque - Curitiba" <?= estaSelecionado('BPChoque - Curitiba', $valUnidade) ?>>BPChoque - Curitiba</option>
              <option value="BPEC - Curitiba" <?= estaSelecionado('BPEC - Curitiba', $valUnidade) ?>>BPEC - Curitiba</option>
              <option value="BPFron - Marechal Cândido Rondon" <?= estaSelecionado('BPFron - Marechal Cândido Rondon', $valUnidade) ?>>BPFron - Marechal Cândido Rondon</option>
              <option value="BPRONE - Curitiba" <?= estaSelecionado('BPRONE - Curitiba', $valUnidade) ?>>BPRONE - Curitiba</option>
              <option value="BPRv - Curitiba" <?= estaSelecionado('BPRv - Curitiba', $valUnidade) ?>>BPRv - Curitiba</option>
              <option value="BPTran - Curitiba" <?= estaSelecionado('BPTran - Curitiba', $valUnidade) ?>>BPTran - Curitiba</option>
              <option value="RPMon - Curitiba" <?= estaSelecionado('RPMon - Curitiba', $valUnidade) ?>>RPMon - Curitiba</option>
            </optgroup>
          </select>
        </div>
      </div>

      <!-- Telefone / Whatsapp -->
      <div class="input-group">
        <label for="telefone">Telefone / Whatsapp</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-phone input-icon"></i>
          <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($user['telefone']) ?>" required oninput="mascaraTelefone(this)">
        </div>
      </div>

      <!-- Alterar Senha -->
      <div class="input-group">
        <label for="nova_senha">Alterar Senha <small style="color: #64748b;">(Deixe em branco para manter a atual)</small></label>
        <div class="input-wrapper">
          <i class="fa-solid fa-key input-icon"></i>
          <input type="password" id="nova_senha" name="nova_senha" placeholder="••••••••">
        </div>
      </div>

      <button type="submit" class="btn-login" style="margin-top: 15px;">
        <span>Salvar Alterações</span>
        <i class="fa-solid fa-floppy-disk"></i>
      </button>

    </form>

    <div class="login-footer">
      <p><a href="index.php"><i class="fa-solid fa-arrow-left"></i> Voltar ao Sistema</a></p>
    </div>
  </div>

  <script>
    function mascaraCPF(i) {
      let v = i.value.replace(/\D/g, "");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
      i.value = v;
    }

    function mascaraTelefone(i) {
      let v = i.value.replace(/\D/g, "");
      v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
      v = v.replace(/(\d{5})(\d)/, "$1-$2");
      i.value = v;
    }
  </script>

</body>
</html>