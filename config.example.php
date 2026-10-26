<?php
// Copie este arquivo para config.php e preencha com os dados reais.
// O config.php está no .gitignore e NUNCA deve ser versionado.

// Evita acesso direto ao arquivo de configuração
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('Acesso direto não permitido.');
}

// Banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'encarregado');
define('DB_USER', 'root');
define('DB_PASS', '');

// Videoconferência (MiroTalk P2P, software livre). Por padrão usa o servidor
// público; com servidor MiroTalk próprio, troque pelo domínio dele.
define('MIROTALK_DOMINIO', 'p2p.mirotalk.com');

// Chave da API Groq (usada no "Refinar com IA")
define('GROQ_API_KEY', 'coloque-sua-chave-aqui');
