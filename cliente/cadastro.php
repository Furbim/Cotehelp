<?php
session_start();
require_once '../config/conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma = $_POST['confirma'];

    if ($senha !== $confirma) {
        $erro = 'As senhas não coincidem.';
    } else {
        // Verifica se email já está em uso
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')");
            $stmt->bind_param("sss", $nome, $email, $senhaHash);

            if ($stmt->execute()) {
                $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
            } else {
                $erro = 'Erro ao cadastrar. Tente novamente.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <form method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Criar Conta</h2>

        <?php if ($erro): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= $sucesso ?>
            </div>
        <?php endif; ?>

        <input type="text" name="nome" placeholder="Nome completo" required class="w-full p-2 mb-4 border rounded" />
        <input type="email" name="email" placeholder="Email" required class="w-full p-2 mb-4 border rounded" />
        <input type="password" name="senha" placeholder="Senha" required class="w-full p-2 mb-4 border rounded" />
        <input type="password" name="confirma" placeholder="Confirme a senha" required class="w-full p-2 mb-4 border rounded" />

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
            Cadastrar
        </button>

        <p class="mt-4 text-center text-sm text-gray-600">
            Já tem conta? <a href="login.php" class="text-blue-500 hover:underline">Faça login</a>
        </p>
    </form>

</body>
</html>
