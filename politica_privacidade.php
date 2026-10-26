<?php
require_once 'sessao.php';
// Página pública: pode ser lida antes do cadastro, sem login.
$logado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade e Termo de Consentimento - Ajuda do Encarregado</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f4f6f9;
            color: #1e293b;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.65;
        }
        .documento {
            max-width: 860px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 32px 40px;
        }
        .topo { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #2b80c5; padding-bottom: 14px; margin-bottom: 18px; }
        .topo img { height: 52px; }
        h1 { font-size: 20px; margin: 0; color: #0f172a; }
        .sub { color: #64748b; font-size: 12px; margin: 2px 0 0; }
        h2 { font-size: 15px; color: #2b80c5; margin: 26px 0 8px; border-left: 4px solid #2b80c5; padding-left: 8px; }
        h3 { font-size: 14px; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 13px; }
        th, td { border: 1px solid #e2e8f0; padding: 7px 9px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; }
        .aviso { background: #fff7ed; border-left: 4px solid #f59e0b; padding: 10px 14px; border-radius: 4px; margin: 14px 0; }
        .destaque { background: #eff6ff; border-left: 4px solid #2b80c5; padding: 10px 14px; border-radius: 4px; margin: 14px 0; }
        .acoes { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 28px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; background: #2b80c5; color: #fff; text-decoration: none; padding: 9px 16px; border-radius: 4px; font-weight: 600; font-size: 13px; }
        .btn.cinza { background: #64748b; }
        @media (max-width: 640px) {
            .documento { margin: 0; border-radius: 0; padding: 20px 16px; }
            table { font-size: 12px; }
        }
        @media print {
            body { background: #fff; }
            .documento { border: none; margin: 0; padding: 0; }
            .acoes { display: none; }
        }
    </style>
</head>

<body>
    <main class="documento">
        <div class="topo">
            <img src="img/logo.png" alt="">
            <div>
                <h1>Política de Privacidade e Termo de Consentimento</h1>
                <p class="sub">Sistema "Ajuda do Encarregado" &middot; Versão <?= e(TERMO_VERSAO_ATUAL) ?> &middot; Atualizada em <?= e(TERMO_DATA_ATUALIZACAO) ?></p>
            </div>
        </div>

        <div class="aviso">
            <strong>Ferramenta independente.</strong> O "Ajuda do Encarregado" é uma ferramenta de apoio desenvolvida de forma independente
            para auxiliar Encarregados de procedimentos na elaboração de documentos de oitiva. <strong>Não é um sistema oficial da Polícia
            Militar do Paraná</strong>, não substitui os sistemas institucionais e não dispensa a conferência dos documentos pelo Encarregado.
        </div>

        <p>
            Esta Política explica, em linguagem simples, quais dados pessoais o sistema trata, para quê, com quem são compartilhados,
            por quanto tempo são guardados e quais são os seus direitos, conforme a Lei Geral de Proteção de Dados Pessoais
            (<strong>LGPD – Lei nº 13.709/2018</strong>).
        </p>

        <h2>1. Quem é o responsável pelos dados</h2>
        <p>
            <strong>Controlador:</strong> Rafael A. Berwanger, desenvolvedor e administrador do sistema, que também atua como canal de
            atendimento ao titular (função de Encarregado de Dados prevista no art. 41 da LGPD).<br>
            <strong>Contato:</strong> canal <em>"Feedback / Suporte"</em> disponível no rodapé de todas as páginas do sistema.
        </p>
        <p>
            Quanto aos <strong>dados de terceiros</strong> (depoentes, testemunhas, vítimas, investigados e advogados) que o Encarregado
            digita para produzir os termos, o próprio Encarregado e a instituição a que pertence são os responsáveis pelo tratamento,
            no exercício de suas atribuições legais (item 4).
        </p>

        <h2>2. Quais dados são tratados</h2>

        <h3>2.1 Dados do usuário (guardados no servidor)</h3>
        <table>
            <tr><th>Dado</th><th>Finalidade</th></tr>
            <tr><td>Nome completo, posto/graduação, unidade (OPM), RG e CPF</td><td>Identificar o militar, conferir se tem direito de acesso e preencher automaticamente os dados do Encarregado nos termos.</td></tr>
            <tr><td>E-mail e telefone</td><td>Contato do administrador sobre a solicitação de acesso e suporte.</td></tr>
            <tr><td>Usuário e senha</td><td>Autenticação. A senha é guardada apenas na forma de <em>hash</em> criptográfico (bcrypt): nem o administrador consegue lê-la.</td></tr>
            <tr><td>Data de cadastro, último acesso, situação da conta e perfil</td><td>Segurança, controle de acesso e auditoria.</td></tr>
            <tr><td>Data e versão do aceite desta Política</td><td>Comprovar o consentimento (art. 8º, § 2º, da LGPD).</td></tr>
        </table>

        <h3>2.2 Dados das oitivas (NÃO são guardados no servidor)</h3>
        <p>
            Os dados digitados nos módulos de oitiva (qualificação do depoente, filiação, endereço, CPF, texto do depoimento, dados do
            advogado etc.) são processados <strong>somente no navegador</strong> do Encarregado para gerar o termo e o PDF.
            Eles <strong>não são enviados nem armazenados</strong> no banco de dados do sistema, com as exceções descritas no item 5.
        </p>
        <ul>
            <li><strong>Vídeos da oitiva:</strong> a gravação integrada é feita no próprio navegador e o arquivo é salvo no computador do Encarregado. O vídeo <strong>não</strong> é enviado ao servidor. Os códigos HASH (SHA-1/SHA-256) também são calculados localmente.</li>
            <li><strong>Rascunho do depoimento:</strong> para evitar perda de texto, o depoimento fica guardado temporariamente no armazenamento da própria aba do navegador (<em>sessionStorage</em>) e é apagado quando a aba é fechada.</li>
        </ul>

        <h3>2.3 Cookies</h3>
        <p>
            O sistema usa apenas um <strong>cookie de sessão estritamente necessário</strong>, que mantém o usuário conectado e expira ao
            fechar o navegador. Não há cookies de publicidade, rastreamento ou análise de audiência.
        </p>

        <h2>3. Para que os dados são usados</h2>
        <ul>
            <li>Permitir o cadastro, a aprovação e o acesso seguro ao sistema;</li>
            <li>Preencher automaticamente os dados do Encarregado nos documentos;</li>
            <li>Gerar termos de oitiva, certidões, relatórios de gravação e códigos HASH;</li>
            <li>Realizar a videochamada e a gravação da oitiva;</li>
            <li>Prestar suporte e garantir a segurança do sistema.</li>
        </ul>
        <p>Os dados <strong>não</strong> são vendidos, usados para publicidade nem para criar perfis de comportamento.</p>

        <h2>4. Bases legais</h2>
        <ul>
            <li><strong>Dados do usuário:</strong> consentimento do titular (art. 7º, I), manifestado no cadastro, e legítimo interesse na segurança do sistema (art. 7º, IX).</li>
            <li><strong>Dados das oitivas:</strong> tratados pelo Encarregado para o cumprimento de obrigação legal e o exercício regular de direitos em processo administrativo ou judicial (art. 7º, II e VI), e, quando forem dados sensíveis, nas hipóteses do art. 11, II, "a" e "d", sem prejuízo das regras de sigilo dos procedimentos.</li>
        </ul>

        <h2>5. Compartilhamento com terceiros</h2>
        <p>Alguns recursos dependem de serviços externos. Eles só recebem dados quando o recurso é usado:</p>
        <table>
            <tr><th>Serviço</th><th>Quando</th><th>O que recebe</th></tr>
            <tr><td><strong>Groq, Inc.</strong> (IA, Estados Unidos)</td><td>Somente ao clicar em "Refinar com IA"</td><td>O texto do depoimento, para correção ortográfica e de pontuação. Há <strong>transferência internacional</strong> de dados (art. 33 da LGPD). O sistema pede confirmação antes do envio. Evite usar o recurso para trechos com dados que não sejam necessários.</td></tr>
            <tr><td><strong>MiroTalk P2P</strong> (p2p.mirotalk.com ou servidor configurado)</td><td>Durante a videochamada</td><td>O nome de exibição e os dados de conexão dos participantes passam pelo servidor que organiza a sala. Áudio e vídeo trafegam diretamente entre os participantes (ponto a ponto), ou por um servidor de retransmissão quando a conexão direta não é possível. Por isso, os participantes podem conhecer o endereço IP uns dos outros. A gravação é feita no navegador do Encarregado, e não pelo MiroTalk.</td></tr>
            <tr><td><strong>IBGE</strong> (API de localidades)</td><td>Ao escolher estado e cidade</td><td>Apenas a sigla do estado. Nenhum dado pessoal.</td></tr>
            <tr><td><strong>WhatsApp</strong></td><td>Somente quando o usuário clica em um link do WhatsApp</td><td>O que o próprio usuário decidir enviar (ex.: link da sala).</td></tr>
            <tr><td>Bibliotecas públicas (cdnjs, jsDelivr) e provedor de hospedagem</td><td>Ao carregar as páginas</td><td>Dados técnicos da conexão, como endereço IP e navegador, necessários ao funcionamento da internet.</td></tr>
        </table>
        <p>Os dados também poderão ser compartilhados quando houver obrigação legal ou ordem de autoridade competente.</p>

        <h2>6. Por quanto tempo os dados são guardados</h2>
        <ul>
            <li><strong>Conta de usuário:</strong> enquanto a conta existir. Ao ser excluída pelo administrador (inclusive a pedido do titular), os dados de cadastro são apagados do banco de dados.</li>
            <li><strong>Solicitações recusadas:</strong> podem ser mantidas para evitar cadastros duplicados até que o titular ou o administrador peça a exclusão.</li>
            <li><strong>Dados das oitivas:</strong> não ficam no servidor. Os arquivos gerados (PDFs e vídeos) ficam sob guarda do Encarregado, que deve armazená-los conforme as normas do procedimento.</li>
        </ul>

        <h2>7. Segurança</h2>
        <p>
            São adotadas medidas como senhas protegidas por <em>hash</em> bcrypt, sessões com cookie protegido, proteção contra
            requisições forjadas (CSRF), controle de acesso por perfil com aprovação de cada cadastro pelo administrador, limite de
            tentativas de login e processamento local dos dados das oitivas. Nenhum sistema é totalmente imune a incidentes. Em caso
            de incidente relevante, os titulares afetados serão comunicados (art. 48 da LGPD).
        </p>

        <h2>8. Seus direitos como titular</h2>
        <p>Conforme o art. 18 da LGPD, você pode, a qualquer momento e sem custo:</p>
        <ul>
            <li>confirmar se tratamos seus dados e acessá-los;</li>
            <li>corrigir dados incompletos ou desatualizados (diretamente em <em>Meu Perfil</em>);</li>
            <li>pedir a anonimização, bloqueio ou eliminação de dados desnecessários;</li>
            <li>pedir a portabilidade e informações sobre compartilhamento;</li>
            <li><strong>revogar o consentimento</strong> e pedir a exclusão da sua conta, ciente de que sem ela não será possível usar o sistema;</li>
            <li>apresentar reclamação à Autoridade Nacional de Proteção de Dados (ANPD).</li>
        </ul>
        <p>Para exercer esses direitos, use o canal <em>"Feedback / Suporte"</em> do rodapé.</p>

        <h2>9. Responsabilidades do usuário</h2>
        <ul>
            <li>Manter usuário e senha em sigilo e não compartilhar o acesso;</li>
            <li>Usar o sistema somente para fins de serviço relacionados aos procedimentos em que atua;</li>
            <li>Inserir apenas os dados de terceiros necessários ao ato, respeitando o sigilo do procedimento;</li>
            <li>Obter dos participantes a ciência sobre a gravação da oitiva, conforme as normas aplicáveis;</li>
            <li>Guardar com segurança os documentos e vídeos gerados e conferir seu conteúdo antes de juntá-los aos autos.</li>
        </ul>

        <h2>10. Alterações desta Política</h2>
        <p>
            Esta Política pode ser atualizada. Quando houver mudança relevante, a versão será alterada e um novo aceite será solicitado
            no próximo acesso ao sistema.
        </p>

        <div class="destaque">
            <h3 style="margin-top:0;" id="termo">Termo de Consentimento para Tratamento de Dados Pessoais</h3>
            <p style="margin-bottom:0;">
                Ao marcar a opção de aceite no cadastro ou no acesso ao sistema, declaro que li e compreendi esta Política de Privacidade
                e <strong>consinto, de forma livre, informada e inequívoca</strong>, com o tratamento dos meus dados pessoais de cadastro
                (nome, posto/graduação, unidade, RG, CPF, e-mail, telefone e dados de acesso) para as finalidades descritas, nos termos
                do art. 7º, I, e do art. 8º da LGPD. Declaro também estar ciente de que: (a) posso revogar este consentimento a qualquer
                momento, pedindo a exclusão da minha conta; (b) o uso do recurso "Refinar com IA" envia o texto do depoimento a um serviço
                localizado nos Estados Unidos; e (c) sou responsável pelo uso adequado dos dados de terceiros que inserir no sistema.
            </p>
        </div>

        <div class="acoes">
            <?php if ($logado): ?>
                <a href="index.php" class="btn"><i class="fa-solid fa-house"></i> Voltar ao sistema</a>
            <?php else: ?>
                <a href="login.php" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Ir para o login</a>
                <a href="solicitar_acesso.php" class="btn cinza"><i class="fa-solid fa-user-plus"></i> Solicitar acesso</a>
            <?php endif; ?>
            <a href="#" class="btn cinza" onclick="window.print(); return false;"><i class="fa-solid fa-print"></i> Imprimir</a>
        </div>
    </main>
</body>

</html>
