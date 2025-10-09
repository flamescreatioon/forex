<?php
// Format number as price

function formatPrice($price){
    return number_format($price, 5, '.', ',');
}

//Format balance or profit as current

function formatCurrency($amount){
    return '$' . number_format($amount, 2);
}

//Format timestamp nicely

function formatTime($timestamp){
    return date('H:i:s', strtotime($timestamp));
}