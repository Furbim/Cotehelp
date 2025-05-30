<?php
// Configurações do Mercado Pago
define('MP_ACCESS_TOKEN', 'APP_USR-3240661054533355-042217-906cfc0632f72df28e33dc857a35bd52-809819720');
define('MP_PUBLIC_KEY', 'APP_USR-70deb18a-e07e-4be2-98b6-f152d83e1f1d');

// Função para criar preferência de pagamento
function criarPreferenciaPagamento($pedido) {
    $url = 'https://api.mercadopago.com/checkout/preferences';
    
    $preference_data = [
        'items' => [
            [
                'title' => $pedido['titulo'],
                'quantity' => 1,
                'unit_price' => (float) $pedido['preco'],
                'currency_id' => 'BRL'
            ]
        ],
        'payment_methods' => [
            'excluded_payment_types' => [
                ['id' => 'credit_card'],
                ['id' => 'debit_card'],
                ['id' => 'wallet']
            ],
            'installments' => 1
        ],
        'back_urls' => [
            'success' => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/pagamento_sucesso.php?pedido_id={$pedido['id']}",
            'failure' => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/pagamento_falha.php?pedido_id={$pedido['id']}",
            'pending' => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/pagamento_pendente.php?pedido_id={$pedido['id']}"
        ],
        'external_reference' => $pedido['id'],
        'notification_url' => "https://" . $_SERVER['HTTP_HOST'] . "/cliente/webhook.php",
        'auto_return' => 'approved'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 201) {
        return json_decode($response, true);
    }
    
    return null;
}

// Função para consultar pagamento
function consultarPagamento($payment_id) {
    $url = "https://api.mercadopago.com/v1/payments/$payment_id";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return null;
} 