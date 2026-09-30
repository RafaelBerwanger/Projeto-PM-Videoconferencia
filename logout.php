<?php
session_start();
session_unset();     // Limpa todas as variáveis de sessão ($_SESSION)
session_destroy();   // Destrói a sessão no servidor

// Redireciona de volta para a tela de login
header('Location: login.php');
exit;
?>