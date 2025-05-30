<?php

 class Payment{
    private $pdo;

    private $user_id = NULL;

    public $payment_id = NULL;

 public function __construct($user_id = NULL){

      $this->user_id   = $user_id;

      $this->pdo = DB::getInstance();

  }

  public function get(){
    $query = $this->pdo->prepare("SELECT * FROM `payment` WHERE id= :id");
    $query->bindValue(':id', $this->payment_id);

    if($query->execute()){

       $row = $query->fetchAll(PDO::FETCH_OBJ);

        if(count($row)>0){
            return $row[0];
        }else{
            return false;
        }

    }else{
        return false;
    }
  }

  public function addPayment($valor){

    $query = $this->pdo->prepare("INSERT INTO payment (valor, user_id) VALUES (:valor, :user_id)");
    $query->bindValue(':valor', $valor);
    $query->bindValue(':user_id', $this->user_id);

    if($query->execute()){
        return $this->pdo->lastInsertId();     
    }else{
        return false;
    }
    
  }


  public function setStatusPayment($status){
    $query = $this->pdo->prepare("UPDATE `payment` SET status= :status WHERE id= :id ");
    $query->bindValue(':status', $status);
    $query->bindValue(':id', $this->payment_id);

    if($query->execute()){
       return true;
    }else{
        return false;
    }
  }

 }

 ?>
