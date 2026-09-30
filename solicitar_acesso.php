<?php
session_start();
require_once 'db.php';

$mensagem = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome            = trim($_POST['nome'] ?? '');
  $rg              = trim($_POST['rg'] ?? '');
  $cpf             = trim($_POST['cpf'] ?? '');
  $posto_graduacao = trim($_POST['posto_graduacao'] ?? '');
  $unidade         = trim($_POST['unidade'] ?? '');
  $telefone        = trim($_POST['telefone'] ?? '');
  $usuario         = trim($_POST['usuario'] ?? '');
  $senha           = trim($_POST['senha'] ?? '');

  if (!empty($nome) && !empty($rg) && !empty($cpf) && !empty($posto_graduacao) && !empty($unidade) && !empty($telefone) && !empty($usuario) && !empty($senha)) {

    // Verifica se o usuário ou CPF já existe
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario OR cpf = :cpf LIMIT 1');
    $stmt->execute(['usuario' => $usuario, 'cpf' => $cpf]);

    if ($stmt->fetch()) {
      $mensagem = 'Nome de usuário ou CPF já cadastrado no sistema.';
      $tipo_msg = 'error';
    } else {
      $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare('INSERT INTO usuarios (nome, rg, cpf, posto_graduacao, unidade, telefone, usuario, senha, perfil, status) VALUES (:nome, :rg, :cpf, :posto, :unidade, :telefone, :usuario, :senha, "operador", "pendente")');
      $stmt->execute([
        'nome'     => $nome,
        'rg'       => $rg,
        'cpf'      => $cpf,
        'posto'    => $posto_graduacao,
        'unidade'  => $unidade,
        'telefone' => $telefone,
        'usuario'  => $usuario,
        'senha'    => $senhaHash
      ]);

      $mensagem = 'Solicitação enviada com sucesso! Aguarde a aprovação do Administrador.';
      $tipo_msg = 'success';
    }
  } else {
    $mensagem = 'Preencha todos os campos obrigatórios.';
    $tipo_msg = 'error';
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitar Acesso - Oitivas PMPR</title>
  <link rel="stylesheet" href="style_login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <div class="login-container" style="max-width: 480px;">
    <div class="login-header">
      <div class="logo-icon"><i class="fa-solid fa-user-plus"></i></div>
      <h2>Solicitar Acesso</h2>
      <p>Preencha os dados cadastrais para requerer acesso</p>
    </div>

    <?php if (!empty($mensagem)): ?>
      <div class="message-box <?= $tipo_msg ?>" style="display: block;"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" action="solicitar_acesso.php">

      <!-- Posto / Graduação (Select) -->
      <div class="input-group">
        <label for="posto_graduacao">Posto / Graduação</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-ranking-star input-icon"></i>
          <select id="posto_graduacao" name="posto_graduacao" class="custom-select" required>
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
      </div>

      <!-- Nome Completo -->
      <div class="input-group">
        <label for="nome">Nome Completo</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-id-card input-icon"></i>
          <input type="text" id="nome" name="nome" placeholder="Nome completo sem abreviações" required>
        </div>
      </div>

      <!-- RG e CPF (2 colunas) -->
      <div style="display: flex; gap: 10px;">
        <div class="input-group" style="flex: 1;">
          <label for="rg">RG</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-address-card input-icon"></i>
            <input type="text" id="rg" name="rg" placeholder="Ex: 12.345.678-9" required>
          </div>
        </div>
        <div class="input-group" style="flex: 1;">
          <label for="cpf">CPF</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-fingerprint input-icon"></i>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" oninput="mascaraCPF(this)" required>
          </div>
        </div>
      </div>

      <!-- Unidade / OPM (Select Completo PMPR) -->
      <div class="input-group">
        <label for="unidade">Unidade / OPM</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-building-shield input-icon"></i>
          <select id="unidade" name="unidade" class="custom-select" required>
            <option value="" disabled selected>Selecione a Unidade...</option>

            <optgroup label="Comandos Regionais (CRPM)">
              <option value="1º CRPM - Curitiba">1º CRPM - Curitiba</option>
              <option value="2º CRPM - Londrina">2º CRPM - Londrina</option>
              <option value="3º CRPM - Maringá">3º CRPM - Maringá</option>
              <option value="4º CRPM - Ponta Grossa">4º CRPM - Ponta Grossa</option>
              <option value="5º CRPM - Cascavel">5º CRPM - Cascavel</option>
              <option value="6º CRPM - São José dos Pinhais">6º CRPM - São José dos Pinhais</option>
              <option value="7º CRPM - Pato Branco">7º CRPM - Pato Branco</option>
            </optgroup>

            <optgroup label="Comandos Especializados e Apoio">
              <option value="CME - Curitiba">CME - Curitiba</option>
              <option value="CPE - Curitiba">CPE - Curitiba</option>
              <option value="COMAV - Curitiba">COMAV - Curitiba</option>
              <option value="CIROCAM - Curitiba">CIROCAM - Curitiba</option>
              <option value="COPOM - Curitiba">COPOM - Curitiba</option>
            </optgroup>

            <optgroup label="Batalhões de Polícia Militar (BPM)">
              <option value="1º BPM - Ponta Grossa">1º BPM - Ponta Grossa</option>
              <option value="2º BPM - Jacarezinho">2º BPM - Jacarezinho</option>
              <option value="3º BPM - Pato Branco">3º BPM - Pato Branco</option>
              <option value="4º BPM - Maringá">4º BPM - Maringá</option>
              <option value="5º BPM - Londrina">5º BPM - Londrina</option>
              <option value="6º BPM - Cascavel">6º BPM - Cascavel</option>
              <option value="7º BPM - Cruzeiro do Oeste">7º BPM - Cruzeiro do Oeste</option>
              <option value="8º BPM - Paranavaí">8º BPM - Paranavaí</option>
              <option value="9º BPM - Paranaguá">9º BPM - Paranaguá</option>
              <option value="10º BPM - Apucarana">10º BPM - Apucarana</option>
              <option value="11º BPM - Campo Mourão">11º BPM - Campo Mourão</option>
              <option value="12º BPM - Curitiba">12º BPM - Curitiba</option>
              <option value="13º BPM - Curitiba">13º BPM - Curitiba</option>
              <option value="14º BPM - Foz do Iguaçu">14º BPM - Foz do Iguaçu</option>
              <option value="15º BPM - Rolândia">15º BPM - Rolândia</option>
              <option value="16º BPM - Guarapuava">16º BPM - Guarapuava</option>
              <option value="17º BPM - São José dos Pinhais">17º BPM - São José dos Pinhais</option>
              <option value="18º BPM - Cornélio Procópio">18º BPM - Cornélio Procópio</option>
              <option value="19º BPM - Toledo">19º BPM - Toledo</option>
              <option value="20º BPM - Curitiba">20º BPM - Curitiba</option>
              <option value="21º BPM - Francisco Beltrão">21º BPM - Francisco Beltrão</option>
              <option value="22º BPM - Colombo">22º BPM - Colombo</option>
              <option value="23º BPM - Curitiba">23º BPM - Curitiba</option>
              <option value="25º BPM - Umuarama">25º BPM - Umuarama</option>
              <option value="26º BPM - Telêmaco Borba">26º BPM - Telêmaco Borba</option>
              <option value="27º BPM - União da Vitória">27º BPM - União da Vitória</option>
              <option value="28º BPM - Lapa">28º BPM - Lapa</option>
              <option value="29º BPM - Piraquara">29º BPM - Piraquara</option>
              <option value="30º BPM - Londrina">30º BPM - Londrina</option>
              <option value="31º BPM - Assis Chateaubriand">31º BPM - Assis Chateaubriand</option>
              <option value="32º BPM - Sarandi">32º BPM - Sarandi</option>
              <option value="33º BPM - Curitiba">33º BPM - Curitiba</option>
              <option value="34º BPM - Almirante Tamandaré">34º BPM - Almirante Tamandaré</option>
            </optgroup>

            <optgroup label="Companhias Independentes (CIPM)">
              <option value="3ª CIPM - Loanda">3ª CIPM - Loanda</option>
              <option value="5ª CIPM - Cianorte">5ª CIPM - Cianorte</option>
              <option value="6ª CIPM - Ivaiporã">6ª CIPM - Ivaiporã</option>
              <option value="7ª CIPM - Arapongas">7ª CIPM - Arapongas</option>
              <option value="8ª CIPM - Irati">8ª CIPM - Irati</option>
              <option value="9ª CIPM - Colorado">9ª CIPM - Colorado</option>
              <option value="10ª CIPM - Laranjeiras do Sul">10ª CIPM - Laranjeiras do Sul</option>
              <option value="11ª CIPM - Cambé">11ª CIPM - Cambé</option>
              <option value="12ª CIPM - Palmas">12ª CIPM - Palmas</option>
            </optgroup>

            <optgroup label="Batalhões Especializados">
              <option value="BOPE - Piraquara">BOPE - Piraquara</option>
              <option value="BPAmb - São José dos Pinhais">BPAmb - São José dos Pinhais</option>
              <option value="BPChoque - Curitiba">BPChoque - Curitiba</option>
              <option value="BPEC - Curitiba">BPEC - Curitiba</option>
              <option value="BPFron - Marechal Cândido Rondon">BPFron - Marechal Cândido Rondon</option>
              <option value="BPRONE - Curitiba">BPRONE - Curitiba</option>
              <option value="BPRv - Curitiba">BPRv - Curitiba</option>
              <option value="BPTran - Curitiba">BPTran - Curitiba</option>
              <option value="RPMon - Curitiba">RPMon - Curitiba</option>
            </optgroup>
          </select>
        </div>
      </div>

      <!-- Telefone -->
      <div class="input-group">
        <label for="telefone">Telefone / Whatsapp</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-phone input-icon"></i>
          <input type="text" id="telefone" name="telefone" placeholder="(41) 99999-9999" maxlength="15" oninput="mascaraTelefone(this)" required>
        </div>
      </div>

      <!-- Usuário e Senha -->
      <div style="display: flex; gap: 10px;">
        <div class="input-group" style="flex: 1;">
          <label for="usuario">Usuário</label>
          <div class="input-wrapper">
            <i class="fa-regular fa-user input-icon"></i>
            <input type="text" id="usuario" name="usuario" placeholder="joao.silva" required>
          </div>
        </div>
        <div class="input-group" style="flex: 1;">
          <label for="senha">Senha</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="senha" name="senha" placeholder="••••••••" required>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-login" style="margin-top: 10px;">
        <span>Enviar Solicitação</span>
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </form>

    <div class="login-footer">
      <p>Já possui uma conta? <a href="login.php">Voltar para o Login</a></p>
    </div>
  </div>

  <script>
    // Máscara para CPF: 000.000.000-00
    function mascaraCPF(i) {
      let v = i.value.replace(/\D/g, "");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
      i.value = v;
    }

    // Máscara para Telefone: (00) 00000-0000
    function mascaraTelefone(i) {
      let v = i.value.replace(/\D/g, "");
      v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
      v = v.replace(/(\d{5})(\d)/, "$1-$2");
      i.value = v;
    }
  </script>
</body>

</html>