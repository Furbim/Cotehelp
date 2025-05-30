<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Buscar valor total pago
$stmtTotal = $conn->prepare("SELECT SUM(valor) AS total_pago FROM payments WHERE status = 'approved'");
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalPago = $resultTotal->fetch_assoc()['total_pago'] ?? 0.00;

// Buscar todos os pedidos com dados do cliente
$query = "SELECT o.*, u.nome AS nome_cliente 
          FROM orders o 
          JOIN users u ON o.cliente_id = u.id
          ORDER BY o.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-6xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-3xl font-bold mb-6">Painel do Administrador</h1>

        <div class="mb-6 p-4 bg-green-100 rounded text-green-800 font-semibold">
            Total Recebido: R$ <?= number_format($totalPago, 2, ',', '.') ?>
        </div>

        <h2 class="text-xl font-semibold mb-4">Pedidos</h2>

        <?php if ($result->num_rows === 0): ?>
            <p class="text-gray-600">Nenhum pedido encontrado.</p>
        <?php else: ?>
            <table class="w-full border border-gray-300 rounded text-left">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Cliente</th>
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
                            <td class="p-3"><?= htmlspecialchars($pedido['nome_cliente']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($pedido['titulo']) ?></td>
                            <td class="p-3 capitalize"><?= str_replace('_', ' ', $pedido['status']) ?></td>
                            <td class="p-3">R$ <?= number_format($pedido['preco'], 2, ',', '.') ?></td>
                            <td class="p-3">
                                <?php
                                    // Verificar status do pagamento para este pedido
                                    $stmtPay = $conn->prepare("SELECT status FROM payments WHERE pedido_id = ? ORDER BY id DESC LIMIT 1");
                                    $stmtPay->bind_param("i", $pedido['id']);
                                    $stmtPay->execute();
                                    $resPay = $stmtPay->get_result();
                                    $pagStatus = ($resPay->num_rows > 0) ? $resPay->fetch_assoc()['status'] : 'não iniciado';

                                    switch ($pagStatus) {
                                        case 'approved':
                                            echo '<span class="text-green-600 font-semibold">Aprovado</span>';
                                            break;
                                        case 'pending':
                                            echo '<span class="text-yellow-600 font-semibold">Pendente</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span class="text-red-600 font-semibold">Rejeitado</span>';
                                            break;
                                        default:
                                            echo '<span class="text-gray-600">Não iniciado</span>';
                                            break;
                                    }
                                ?>
                            </td>
                            <td class="p-3 space-x-2">
                                <a href="detalhes_pedido.php?id=<?= $pedido['id'] ?>" 
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Detalhes</a>
                                <?php if ($pagStatus === 'approved'): ?>
                                    <a href="../cliente/chat.php?pedido_id=<?= $pedido['id'] ?>" 
                                       class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Chat</a>
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
