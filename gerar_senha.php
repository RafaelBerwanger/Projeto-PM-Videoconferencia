<?php
// Define a senha que você deseja usar para o login
$senhaDesejada = '0000';

// Gera a hash oficial do PHP usando BCRYPT
$hash = password_hash($senhaDesejada, PASSWORD_BCRYPT);

echo "Sua senha é: <b>" . $senhaDesejada . "</b><br>";
echo "Rode este comando SQL no seu phpMyAdmin:<br><br>";
echo "<pre>UPDATE usuarios SET senha = '" . $hash . "' WHERE usuario = 'admin';</pre>";
?>