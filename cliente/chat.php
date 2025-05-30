<?php
session_start();
require_once '../config/conexao.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}

$pedidoId = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : 0;
$usuarioId = $_SESSION['id'];
$tipoUsuario = $_SESSION['tipo'];

// Verifica se o pedido existe e pertence ao cliente ou é visível ao admin
if ($tipoUsuario === 'cliente') {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND cliente_id = ?");
    $stmt->bind_param("ii", $pedidoId, $usuarioId);
} else {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $pedidoId);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado.";
    exit;
}

$pedido = $result->fetch_assoc();

// Bloqueia chat se não estiver pago
if (!$pedido['pagamento_confirmado']) {
    echo "O chat será liberado após o pagamento ser confirmado.";
    exit;
}

// Enviar mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['mensagem'])) {
    $mensagem = trim($_POST['mensagem']);
    $stmt = $conn->prepare("INSERT INTO messages (pedido_id, remetente_id, mensagem) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $pedidoId, $usuarioId, $mensagem);
    $stmt->execute();
}

// Buscar mensagens
$stmt = $conn->prepare("
    SELECT m.*, u.nome, u.tipo 
    FROM messages m
    JOIN users u ON m.remetente_id = u.id 
    WHERE m.pedido_id = ? 
    ORDER BY m.enviado_em ASC
");
$stmt->bind_param("i", $pedidoId);
$stmt->execute();
$mensagens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Chat do Pedido #<?= $pedidoId ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
        <h2 class="text-xl font-bold mb-4">Chat do Pedido #<?= $pedidoId ?></h2>

        <div class="h-64 overflow-y-auto border p-4 rounded mb-4 bg-gray-50">
            <?php if (count($mensagens) === 0): ?>
                <p class="text-gray-500">Nenhuma mensagem ainda.</p>
            <?php else: ?>
                <?php foreach ($mensagens as $msg): ?>
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">
                            <strong>
                                <?= ($msg['remetente_id'] == $_SESSION['id']) 
                                    ? 'Você' 
                                    : htmlspecialchars($msg['nome']) ?>
                            </strong> - <?= date('d/m/Y H:i', strtotime($msg['enviado_em'])) ?>
                        </p>
                        <p class="bg-white border p-2 rounded"><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST">
            <textarea name="mensagem" required rows="3" placeholder="Digite sua mensagem..."
                      class="w-full border rounded p-2 mb-3"></textarea>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enviar Mensagem
            </button>
        </form>

        <a href="<?= $tipoUsuario === 'admin' ? '../admin/painel.php' : 'painel.php' ?>" class="text-blue-600 block mt-4">
            Voltar
        </a>
    </div>
</body>
</html>
