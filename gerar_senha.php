<?php
// Utilitário para gerar o hash de senha do administrador.
// Uso (somente no terminal):  php gerar_senha.php "SenhaForte123"
// Bloqueado no navegador para não ficar exposto no servidor web.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$senhaDesejada = $argv[1] ?? '';
if (strlen($senhaDesejada) < 6) {
    fwrite(STDERR, "Informe uma senha com no mínimo 6 caracteres: php gerar_senha.php \"SuaSenha\"\n");
    exit(1);
}

$hash = password_hash($senhaDesejada, PASSWORD_DEFAULT);

echo "Rode este comando SQL no seu phpMyAdmin:\n\n";
echo "UPDATE usuarios SET senha = '" . $hash . "' WHERE usuario = 'admin';\n";
