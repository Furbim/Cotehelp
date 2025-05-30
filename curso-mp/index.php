<?php 

    require_once 'class/Conn.class.php';
    require_once 'class/User.class.php';

    $user       = new User(1);
    $dados_user = $user->get();


?>
<!DOCTYPE html>
<html lang="pt-bt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Minha conta</title>
</head>
<body>
    <div class="div_balance">
        <h2>Username: <?= $dados_user->username; ?></h2>
        <h2>Saldo: R$ <?= $dados_user->balance; ?></h2>

        <br>

         <input type="number" placeholder="0.00" name="valor" id="valor" >

        <br>

         <p style="margin-top: 30px;border: 1px solid #01d2e5;padding: 10px;border-radius: 8px;">
            API:
            <a href="api/pix.php">Adicionar saldo por pix</a> 
            <a href="api/preference.php">Adicionar saldo por preference</a>
            <a href="api/card.php">Adicionar saldo por Cartão</a>
         </p>

         <p style="margin-top: 30px;border: 1px solid #01d2e5;padding: 10px;border-radius: 8px;">
            SDK:
            <a href="sdk/pix.php">Adicionar saldo por pix </a> 
            <a href="sdk/preference.php">Adicionar saldo por preference</a>
            <a href="sdk/card.php">Adicionar saldo por Cartão</a>
         </p>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>