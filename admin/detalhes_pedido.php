<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pedido_id = intval($_GET['id'] ?? 0);
if ($pedido_id <= 0) {
    die('Pedido inválido.');
}

// Buscar pedido
$stmt = $conn->prepare("SELECT o.*, u.nome AS nome_cliente, u.email AS email_cliente FROM orders o JOIN users u ON o.cliente_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
if (!$pedido) {
    die('Pedido não encontrado.');
}

// Buscar último status pagamento
$stmtPay = $conn->prepare("SELECT * FROM payments WHERE pedido_id = ? ORDER BY id DESC LIMIT 1");
$stmtPay->bind_param("i", $pedido_id);
$stmtPay->execute();
$payment = $stmtPay->get_result()->fetch_assoc() ?? null;

$erro = '';
$sucesso = '';

// Atualizar preço e status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_preco = floatval($_POST['preco'] ?? 0);
    $novo_status = $_POST['status'] ?? $pedido['status'];

    if ($novo_preco < 0) {
        $erro = "Preço inválido.";
    } else {
        $stmtUp = $conn->prepare("UPDATE orders SET preco = ?, status = ? WHERE id = ?");
        $stmtUp->bind_param("dsi", $novo_preco, $novo_status, $pedido_id);
        if ($stmtUp->execute()) {
            $sucesso = "Pedido atualizado com sucesso.";
            // Atualizar dados locais
            $pedido['preco'] = $novo_preco;
            $pedido['status'] = $novo_status;
        } else {
            $erro = "Erro ao atualizar pedido.";
        }
    }
}

// Opções de status possíveis
$opcoes_status = [
    'aguardando_orcamento' => 'Aguardando Orçamento',
    'aguardando_pagamento' => 'Aguardando Pagamento',
    'em_andamento' => 'Em Andamento',
    'finalizado' => 'Finalizado'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Detalhes do Pedido #<?= $pedido_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Detalhes do Pedido #<?= $pedido_id ?></h1>

        <?php if ($erro): ?>
            <div class="bg-red-200 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php elseif ($sucesso): ?>
            <div class="bg-green-200 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <div class="mb-6">
            <h2 class="font-semibold text-lg">Cliente:</h2>
            <p><?= htmlspecialchars($pedido['nome_cliente']) ?> (<?= htmlspecialchars($pedido['email_cliente']) ?>)</p>
        </div>

        <div class="mb-6">
            <h2 class="font-semibold text-lg">Título:</h2>
            <p><?= htmlspecialchars($pedido['titulo']) ?></p>
        </div>

        <div class="mb-6">
            <h2 class="font-semibold text-lg">Descrição:</h2>
            <p><?= nl2br(htmlspecialchars($pedido['descricao'])) ?></p>
        </div>

        <div class="mb-6">
            <h2 class="font-semibold text-lg">Arquivo enviado:</h2>
            <?php if ($pedido['arquivo'] && file_exists("../uploads/{$pedido['arquivo']}")): ?>
                <a href="../uploads/<?= htmlspecialchars($pedido['arquivo']) ?>" target="_blank" class="text-blue-600 hover:underline">
                    <?= htmlspecialchars($pedido['arquivo']) ?>
                </a>
            <?php else: ?>
                <p>Nenhum arquivo enviado.</p>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-4 mb-6">
            <div>
                <label class="block font-semibold mb-1" for="preco">Preço (R$):</label>
                <input type="number" step="0.01" name="preco" id="preco" value="<?= number_format($pedido['preco'], 2, '.', '') ?>" min="0"
                    class="border border-gray-300 rounded p-2 w-full" required />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="status">Status do Pedido:</label>
                <select name="status" id="status" class="border border-gray-300 rounded p-2 w-full" required>
                    <?php foreach ($opcoes_status as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($pedido['status'] === $key) ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar Alterações</button>
        </form>

        <div class="mb-6">
            <h2 class="font-semibold text-lg">Status do Pagamento:</h2>
            <?php if ($payment): ?>
                <p>Status: 
                    <?php
                    switch ($payment['status']) {
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
                            echo '<span class="text-gray-600">Desconhecido</span>';
                            break;
                    }
                    ?>
                </p>
                <p>Valor: R$ <?= number_format($payment['valor'], 2, ',', '.') ?></p>
                <p>Data: <?= date('d/m/Y H:i', strtotime($payment['data'])) ?></p>
            <?php else: ?>
                <p>Nenhum pagamento registrado.</p>
            <?php endif; ?>
        </div>

        <?php if ($payment && $payment['status'] === 'approved'): ?>
            <a href="../cliente/chat.php?pedido_id=<?= $pedido_id ?>" 
               class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Abrir Chat com Cliente
            </a>
        <?php endif; ?>

        <div class="mt-6">
            <a href="painel.php" class="text-blue-600 hover:underline">← Voltar ao Painel</a>
        </div>
    </div>
</body>
</html>
