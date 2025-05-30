<?php
session_start();
require_once '../config/conexao.php';

if (isset($_SESSION['id']) && $_SESSION['tipo'] === 'admin') {
    header('Location: painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Buscar admin pelo email
    $stmt = $conn->prepare("SELECT id, senha FROM users WHERE email = ? AND tipo = 'admin' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['tipo'] = 'admin';
            header('Location: painel.php');
            exit;
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Administrador não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Login Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-6 rounded shadow-md w-full max-w-sm">
        <h1 class="text-2xl mb-4 font-bold text-center">Login Admin</h1>

        <?php if (!empty($erro)): ?>
            <div class="bg-red-200 text-red-800 p-2 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <label class="block mb-2 font-semibold" for="email">Email</label>
        <input type="email" name="email" id="email" required
               class="w-full border border-gray-300 rounded p-2 mb-4" />

        <label class="block mb-2 font-semibold" for="senha">Senha</label>
        <input type="password" name="senha" id="senha" required
               class="w-full border border-gray-300 rounded p-2 mb-6" />

        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
            Entrar
        </button>
    </form>
</body>
</html>
