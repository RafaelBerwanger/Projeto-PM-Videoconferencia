<?php

/* // Configuração para rodar no XAMPP (Local)
$host = 'localhost';
$db   = 'encarregado';
$user = 'root';
$pass = ''; // No XAMPP a senha do root por padrão é vazia */


// Dados do InfinityFree
$host = 'sql212.infinityfree.com'; 
$db   = 'if0_37053278_encarregado';
$user = 'if0_37053278';
$pass = 'qTt2yFehBH';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>