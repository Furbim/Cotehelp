<?php
session_start();
if (isset($_SESSION['tipo'])) {
    // Redireciona conforme o tipo de usuário
    if ($_SESSION['tipo'] == 'cliente') {
        header('Location: cliente/painel.php');
        exit;
    } elseif ($_SESSION['tipo'] == 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Sistema de Pedidos</h1>
        <p class="text-gray-600 mb-6">Escolha uma das opções abaixo para acessar o sistema.</p>

        <div class="flex flex-col gap-4">
            <a href="cliente/login.php" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                Área do Cliente
            </a>
            <a href="admin/login.php" class="bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-900 transition">
                Área do Administrador
            </a>
        </div>
    </div>

</body>
</html>
