<?php


$config = require_once '../config.php';
require_once 'lib/vendor/autoload.php';
require_once '../class/Conn.class.php';
require_once '../class/Payment.class.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;


// access token arquivo config.php
$accesstoken = $config['accesstoken'];

MercadoPagoConfig::setAccessToken($accesstoken);

$body = json_decode(file_get_contents("php://input"));

// se nao for requisição do formulario do cartao
if (!isset($body->token)) {

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

    // captura  o valor
    $amount = (float) trim($_GET['vl']);

    // instancia a classe pagamento
    $payment = new Payment(1);

    // criação do pagamento
    $payCreate = $payment->addPayment($amount);

    if ($payCreate) {

        $client = new PreferenceClient();

        $createRequest = [
            "external_reference" => $payCreate,
            "notification_url" => $config['url_notification_sdk'],
            "items"=> array(
              array(
                "id" => "4567",
                "title" => "Dummy Title",
                "description" => "Dummy description",
                "picture_url" => "http://www.myapp.com/myimage.jpg",
                "category_id" => "eletronico",
                "quantity" => 1,
                "currency_id" => "BRL",
                "unit_price" => $amount
              )
            ),
            "default_payment_method_id" => "master",
            "excluded_payment_types" => array(
              array(
                "id" => "ticket"
              )
            )
        ];

        $preference = $client->create($createRequest);

        if (isset($preference->id)) {
            if ($preference->id != NULL) {

                if (isset($card)) {
                    $preference_id = $preference->id;
                } else {

                    $link_externo = $preference->init_point;
                    $external_reference = $preference->external_reference;

                    echo "<h3>{$amount} #{$external_reference}</h3> <br />";
                    echo "<a href='{$link_externo}' target='_blank' >Link externo</a>";

                }
            }
        }

    }

}