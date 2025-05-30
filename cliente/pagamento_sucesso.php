<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
    die("Pedido inválido.");
}

// Buscar pedido
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $pedido_id, $_SESSION['id']);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    die("Pedido não encontrado.");
}

// Buscar último pagamento
$stmt = $conn->prepare("SELECT * FROM payments WHERE pedido_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pagamento = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Pagamento Aprovado!</h1>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-2">Pedido #<?= $pedido_id ?></p>
            <p class="text-gray-600 mb-2">Valor: R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></p>
            <?php if ($pagamento): ?>
                <p class="text-gray-600">Método: <?= ucfirst($pagamento['payment_method']) ?></p>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <a href="painel.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                Voltar ao Painel
            </a>
            <a href="chat.php?pedido_id=<?= $pedido_id ?>" class="block w-full bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition duration-200">
                Ir para o Chat
            </a>
        </div>
    </div>
</body>
</html> 