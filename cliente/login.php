<?php
session_start();
require_once '../config/conexao.php'; // Conexão com o banco de dados

$msgErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Buscar usuário no banco
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND tipo = 'cliente' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verifica a senha
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];
            header('Location: painel.php');
            exit;
        } else {
            $msgErro = 'Senha incorreta.';
        }
    } else {
        $msgErro = 'Usuário não encontrado.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login do Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <form method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login do Cliente</h2>

        <?php if ($msgErro): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= $msgErro ?>
            </div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" required class="w-full p-2 mb-4 border rounded" />
        <input type="password" name="senha" placeholder="Senha" required class="w-full p-2 mb-4 border rounded" />

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
            Entrar
        </button>

        <p class="mt-4 text-center text-sm text-gray-600">
            Ainda não tem conta? <a href="cadastro.php" class="text-blue-500 hover:underline">Cadastre-se</a>
        </p>
    </form>

</body>
</html>
