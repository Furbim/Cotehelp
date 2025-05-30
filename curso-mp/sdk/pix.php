<?php

if (!isset($_GET['vl'])) {
    die('vl nao existe');
} else {
    if ($_GET['vl'] == "" || !is_numeric($_GET['vl'])) {
        die('vl não pode ser vazio, e tem que ser numerico');
    } else {
        if ($_GET['vl'] < 1 && $_GET['vl'] > 100) {
            die('valor deve ser entre 1 e 100');
        }
    }
}

$config = require_once '../config.php';
require_once 'lib/vendor/autoload.php';
require_once '../class/Conn.class.php';
require_once '../class/Payment.class.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

// captura  o valor
$amount = (float) trim($_GET['vl']);

// instancia a classe pagamento
$payment = new Payment(1);

// criação do pagamento
$payCreate = $payment->addPayment($amount);

if ($payCreate) {

    $accesstoken = $config['accesstoken'];

    MercadoPagoConfig::setAccessToken($accesstoken);
    $client = new PaymentClient();

    $createRequest = [
        "transaction_amount" => $amount,
        "description" => "description",
        "external_reference" => $payCreate,
        "notification_url" => $config['url_notification_sdk'],
        "payment_method_id" => "pix",
            "payer" => [
                "email" => "cliente-email@gmail.com",
            ]
    ];

    $payment = $client->create($createRequest);

    if (isset($payment->id)) {
        if ($payment->id != NULL) {

            $copia_cola = $payment->point_of_interaction->transaction_data->qr_code;
            $img_qrcode = $payment->point_of_interaction->transaction_data->qr_code_base64;
            $link_externo = $payment->point_of_interaction->transaction_data->ticket_url;
            $notification_url = $payment->notification_url;
            $transaction_amount = $payment->transaction_amount;
            $external_reference = $payment->external_reference;

            echo "<h3>{$transaction_amount} #{$external_reference}</h3> <br />";
            echo "{$notification_url} <br />";
            echo "<img src='data:image/png;base64, {$img_qrcode}' width='200' /> <br />";
            echo "<textarea>{$copia_cola}</textarea> <br />";
            echo "<a href='{$link_externo}' target='_blank' >Link externo</a>";

        }
    }


}