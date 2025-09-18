<?php

// Configurações do banco
$host    = "localhost";   // normalmente não precisa alterar
$usuario = "root";        // substituir se seu usuário não for root
$senha   = "";            // substituir se você tiver senha no MySQL
$banco   = "projeto.tsuchida";       // substituir pelo nome do seu banco criado no phpMyAdmin

// Conexão MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro ao conectar: " . mysqli_connect_error());
}

// SENSITIVE CASE suportar acentos e Ç
mysqli_set_charset($conexao, "utf8");

// Substituam os valores abaixo pelas credenciais da sua própria conta do Cloudinary
$cloud_name = "dcyosnjtr";  // exemplo: "meucloud123"
$api_key    = "749879538611754";     // exemplo: "123456789012345"
$api_secret = "fNJcHimcZn8yIn3lRCh0_K9bt4o";  // exemplo: "abcdeFGHijkLMNopqrstu"
?>