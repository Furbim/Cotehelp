<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ../login.php');
    exit;
}

$clienteId = $_SESSION['id'];

// Buscar pedidos do cliente
$stmt = $conn->prepare("SELECT * FROM orders WHERE cliente_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $clienteId);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Painel do Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Seus Pedidos</h1>

        <a href="novo_pedido.php"
            class="inline-block mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Novo Pedido
        </a>

        <?php if ($result->num_rows === 0): ?>
            <p class="text-gray-600">Você ainda não fez nenhum pedido.</p>
        <?php else: ?>
            <table class="w-full border border-gray-300 rounded text-left">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Título</th>
                        <th class="p-3 border-b">Status</th>
                        <th class="p-3 border-b">Preço</th>
                        <th class="p-3 border-b">Pagamento</th>
                        <th class="p-3 border-b">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3"><?= $pedido['id'] ?></td>
                            <td class="p-3"><?= htmlspecialchars($pedido['titulo']) ?></td>
                            <td class="p-3 capitalize"><?= str_replace('_', ' ', $pedido['status']) ?></td>
                            <td class="p-3">R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></td>
                            <td class="p-3">
                                <?php
                                if (isset($pedido['pagamento_confirmado']) && $pedido['pagamento_confirmado']) {
                                    echo "Pagamento confirmado";
                                } else {
                                    echo "Sem preço definido ou pagamento não confirmado";
                                }

                                ?>
                            </td>
                            <td class="p-3 space-x-2">
                                <?php if ($pedido['pagamento_confirmado']): ?>
                                    <a href="chat.php?pedido_id=<?= $pedido['id'] ?>"
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Chat</a>
                                <?php elseif ($pedido['preco'] > 0): ?>
                                    <a href="pagamento.php?pedido_id=<?= $pedido['id'] ?>"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Pagar</a>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Aguardando definição</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="../logout.php" class="block mt-6 text-red-600 hover:underline">Sair</a>
    </div>
</body>

</html>