<?php

require_once __DIR__.'/../models/AccountModel.php';
require_once  __DIR__.'TradeController.php';

class AccountController{
    private $accountModel;
    private $tradeController;

    public function __construct(){
        session_start();
        $this->accountModel = new AccountModel();
        $this->tradeController = new TradeController();
    }

    public function getLiveAccountData(){
        header('Content-Type: application/json');
        $priceData = $this->tradeModel->getLiveAccountData();
        $priceChange = $priceData['change'];

        $this->accountModel->updateAccount($priceChange);
        echo json_encode($this->accountModel->getAccountData());
    }
}