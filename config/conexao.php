<?php
// Dados da conexão com o banco de dados MySQL
$host = "localhost";    // endereço do servidor MySQL
$user = "root";  // usuário do banco
$pass = "";    // senha do banco
$db   = "cotefreelas"; // nome do banco de dados

// Criar conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Definir charset para evitar problemas com acentuação
$conn->set_charset("utf8mb4");
