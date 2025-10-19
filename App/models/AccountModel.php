<?php

class AccountModel{
    private $balance = 10000.00;
    private $profit = 0.00;

    public function __construct(){
        if(isset($_SESSION['balance'])){
            $this->balance = $_SESSION['balance'];
            $this->profit = $_SESSION['profit'];
        } else {
            $_SESSION['balance'] = $this->balance;
            $_SESSION['profit'] = $this->profit;
        }
    }

    public function updateAccount($priceChange){
        $randomFactor = (mt_rand(0,100)<80)?abs($priceChange):$priceChange;

        $profitChange = $randomFactor * 10;
        $this->profit += $profitChange;
        $this->balance += $profitChange;

        $_SESSION['profit'] = $this->profit;
        $_SESSION['balance'] = $this->balance;
    }

    public function getAccountData(){
        return[
            'balance'=> round($this->balance,2),
            'profit'=> round($this->profit, 2),
            'equity'=> round($this->balance + ($this->profit * 0.1), 2)
        ];
    }
}