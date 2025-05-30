<?php
session_start();
require_once '../config/conexao.php';

// Verifica login
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit;
}

// Verifica pedido
if (!isset($_GET['pedido_id'])) {
    header('Location: painel.php');
    exit;
}

$pedidoId = (int) $_GET['pedido_id'];
$clienteId = $_SESSION['id'];

// Buscar o pedido do cliente
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $pedidoId, $clienteId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado.";
    exit;
}

$pedido = $result->fetch_assoc();

// Verifica se já foi pago
if ($pedido['pagamento_confirmado']) {
    echo "Este pedido já foi pago.";
    exit;
}

// Gera link de pagamento
require_once '../config/mercadopago.php';

use MercadoPago\Item;
use MercadoPago\Preference;

$preference = new Preference();

$item = new Item();
$item->title = $pedido['titulo'];
$item->quantity = 1;
$item->unit_price = floatval($pedido['preco']);

$preference->items = [$item];
$preference->external_reference = $pedido['id'];
$preference->back_urls = [
    "success" => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/painel.php",
    "failure" => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/painel.php",
    "pending" => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/painel.php"
];
$preference->auto_return = "approved";
$preference->notification_url = "https://" . $_SERVER['HTTP_HOST'] . "/webhook.php";

$preference->save();

// Registrar pagamento pendente
$stmt = $conn->prepare("INSERT INTO payments (pedido_id, valor, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("id", $pedidoId, $pedido['preco']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pagamento do Pedido</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-6 rounded shadow max-w-md w-full">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Pagamento do Pedido</h2>

        <p><strong>Título:</strong> <?= htmlspecialchars($pedido['titulo']) ?></p>
        <p><strong>Descrição:</strong> <?= htmlspecialchars($pedido['descricao']) ?></p>
        <p class="mt-2"><strong>Valor:</strong> R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></p>

        <a href="<?= $preference->init_point ?>" target="_blank"
           class="block mt-6 bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded">
           Pagar com Mercado Pago
        </a>

        <a href="painel.php" class="block mt-4 text-blue-600 text-center">Voltar ao painel</a>
    </div>

</body>
</html>
