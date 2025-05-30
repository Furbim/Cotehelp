<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $clienteId = $_SESSION['id'];

    $arquivoNome = null;

    // Upload de arquivo (opcional)
    if (!empty($_FILES['arquivo']['name'])) {
        $diretorio = '../uploads/';
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $ext = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $arquivoNome = uniqid() . '.' . strtolower($ext);
        $caminhoCompleto = $diretorio . $arquivoNome;

        if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
            $erro = 'Erro ao enviar o arquivo.';
        }
    }

    if (!$erro) {
        $stmt = $conn->prepare("INSERT INTO orders (titulo, descricao, arquivo, cliente_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $titulo, $descricao, $arquivoNome, $clienteId);

        if ($stmt->execute()) {
            $sucesso = 'Pedido enviado com sucesso!';
        } else {
            $erro = 'Erro ao salvar o pedido.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Pedido</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Novo Pedido</h2>

        <?php if ($erro): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $sucesso ?></div>
            <a href="painel.php" class="inline-block mt-4 text-blue-600 underline">Voltar ao painel</a>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <label class="block mb-2 font-medium">Título do Pedido</label>
                <input type="text" name="titulo" required class="w-full p-2 mb-4 border rounded">

                <label class="block mb-2 font-medium">Descrição</label>
                <textarea name="descricao" required rows="5" class="w-full p-2 mb-4 border rounded"></textarea>

                <label class="block mb-2 font-medium">Arquivo (opcional)</label>
                <input type="file" name="arquivo" class="mb-4">

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Enviar Pedido
                </button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
