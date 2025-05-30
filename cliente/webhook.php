<?php

require_once '../config/conexao.php';
require_once '../config/mercadopago.php';

// Função de log centralizada
function logWebhook(string $message): void {
    file_put_contents(__DIR__ . '/webhook.log', date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

logWebhook('Webhook iniciado');

// Captura payload bruto (JSON)
$rawBody = file_get_contents('php://input');
logWebhook("Raw payload: $rawBody");

// Decodifica o payload
$payload = json_decode($rawBody, true);

if (!$payload) {
    logWebhook("Erro ao decodificar JSON");
    http_response_code(400);
    exit;
}

// Verifica se é uma notificação de pagamento
if ($payload['type'] === 'payment' && isset($payload['data']['id'])) {
    $paymentId = $payload['data']['id'];
    logWebhook("Payment ID recebido: $paymentId");
    
    // Consulta detalhes do pagamento
    $payment = consultarPagamento($paymentId);
    
    if ($payment) {
        $externalRef = $payment['external_reference'];
        logWebhook("External Reference: $externalRef");

        // Busca pedido no banco
        $stmt = $conn->prepare('SELECT id, status FROM orders WHERE id = ?');
        $stmt->bind_param('i', $externalRef);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            logWebhook("Pedido encontrado: " . json_encode($order));
            
            // Mapeamento de status Mercado Pago -> nosso sistema
            $mapStatus = [
                'approved'   => 'pago',
                'pending'    => 'pendente',
                'in_process' => 'pendente',
                'rejected'   => 'cancelado',
                'refunded'   => 'cancelado'
            ];
            $status = $mapStatus[$payment['status']] ?? 'pendente';
            logWebhook("Status do pagamento: {$payment['status']} -> $status");

            // Atualiza status do pedido
            $stmt = $conn->prepare('UPDATE orders SET status = ?, pagamento_confirmado = ? WHERE id = ?');
            $pagamentoConfirmado = ($payment['status'] === 'approved') ? 1 : 0;
            $stmt->bind_param('sii', $status, $pagamentoConfirmado, $externalRef);
            $updateResult = $stmt->execute();
            $stmt->close();
            
            logWebhook("Atualização do pedido: " . ($updateResult ? "Sucesso" : "Falha"));

            // Registra o pagamento
            $stmt = $conn->prepare(
                'INSERT INTO payments (pedido_id, payment_id, valor, payment_method, status, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            $stmt->bind_param(
                'iidss',
                $externalRef,
                $paymentId,
                $payment['transaction_amount'],
                $payment['payment_method_id'],
                $status
            );
            $insertResult = $stmt->execute();
            $stmt->close();
            
            logWebhook("Inserção do pagamento: " . ($insertResult ? "Sucesso" : "Falha"));
        } else {
            logWebhook("Pedido não encontrado: $externalRef");
        }
    } else {
        logWebhook("Erro ao consultar pagamento no MP");
    }
} else {
    logWebhook("Notificação ignorada. Tipo: " . ($payload['type'] ?? 'desconhecido'));
}

// Responde para o Mercado Pago
http_response_code(200);
echo json_encode(['status' => 'success']);
logWebhook('Webhook finalizado');
