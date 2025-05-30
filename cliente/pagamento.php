<?php
// cliente/pagamento.php

session_start();
require_once '../config/conexao.php';
require_once '../config/mercadopago.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
    die("Pedido inválido.");
}

// Buscar pedido e verificar se pertence ao usuário
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $pedido_id, $_SESSION['id']);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    die("Pedido não encontrado.");
}

if ($pedido['preco'] <= 0) {
    die("O preço ainda não foi definido pelo administrador.");
}

// Criar preferência de pagamento
$preference = criarPreferenciaPagamento($pedido);

if (!$preference) {
    die("Erro ao criar preferência de pagamento.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pagamento PIX - Pedido #<?= $pedido_id ?></title>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md text-center">
        <h1 class="text-2xl font-bold mb-4">Pagamento PIX - Pedido #<?= $pedido_id ?></h1>
        <p class="mb-6">Título: <strong><?= htmlspecialchars($pedido['titulo']) ?></strong></p>
        <p class="mb-6">Valor: <strong>R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></strong></p>
        
        <div id="button-checkout"></div>
    </div>

    <script>
    const mp = new MercadoPago('<?= MP_PUBLIC_KEY ?>', {
        locale: 'pt-BR'
    });

    mp.checkout({
        preference: {
            id: '<?= $preference['id'] ?>'
        },
        render: {
            container: '#button-checkout',
            label: 'Pagar com PIX',
            type: 'wallet',
            theme: {
                elementsColor: '#007bff',
                headerColor: '#007bff'
            }
        }
    });
    </script>
</body>
</html>
