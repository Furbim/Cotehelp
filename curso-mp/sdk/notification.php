<?php  

    $config = require_once '../config.php';
    require_once 'lib/vendor/autoload.php';

    require_once '../class/Conn.class.php';
    require_once '../class/Payment.class.php';
    require_once '../class/User.class.php';

    use MercadoPago\MercadoPagoConfig;
    use MercadoPago\Client\Payment\PaymentClient;

    $accesstoken = $config['accesstoken'];
    MercadoPagoConfig::setAccessToken($accesstoken);

    $body   = json_decode(file_get_contents('php://input'));

    if(isset($body->data->id)){

        $id      = $body->data->id;
        $client  = new PaymentClient();

        $payment = $client->get($id);

        if(isset($payment->id)){

            $payment_class             = new Payment();
            $payment_class->payment_id = $payment->external_reference;
            $payment_data              = $payment_class->get();

            if($payment_data){

                if($payment->status == "approved"){
                    // add balance user
                    $user = new User($payment_data->user_id);
                    $addBalance = $user->addBalance((float)$payment_data->valor);

                }

                $payment_class->setStatusPayment($payment->status);

            }


        }


    }