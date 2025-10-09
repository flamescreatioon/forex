<?php

//Generate a random float number between min and max
function randomFloat($min = 0, $max=1, $decimals = 5){
    $factor = 10 ** $decimals;
    $minInt = (int) round($min * $factor);
    $maxInt = (int) round($max * $factor);
    return mt_rand($minInt, $maxInt)/$factor;
}

//Generate a random percentage change (for price movement)

function randomChange($min = -0.5, $max = 0.5){
    return randomFloat($min, $max, 4);
}

// Get current timestamp(formatted)

function now(){
    return date('Y-m-d H:i:s');
}