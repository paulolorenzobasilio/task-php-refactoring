<?php

require __DIR__ . '/vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$log = new Logger('debug');
$log->pushHandler(new StreamHandler('dev.log', Logger::INFO));

foreach (explode("\n", file_get_contents($argv[1])) as $row) {
    if($row === ""){
        return;
    }    

    $transaction = json_decode($row);
    list('bin' => $bin, 'amount' => $amount, 'currency' => $currency) = get_object_vars($transaction);
    
    $amountRate = isEuCurrency($currency) 
        ? $amount 
        : computeExchangeRate($amount, $currency);

    $bankId = getBankIdentification($bin);
    $countryCode = $bankId->country->alpha2;

    $comission = $amountRate * commissionRate($countryCode);

    echo ceiling($comission, 2);
    print "\n";
}

function isEuCurrency($currency){
    return $currency === 'EUR';
}

function commissionRate($countryCode){
    $isEuCountry = isEuCountry($countryCode);

    return $isEuCountry ? 0.01 : 0.02;
}

function isEuCountry($code) {
    $euCountries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 
    'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];

    return in_array($code, $euCountries);
}

function computeExchangeRate($amount, $currency){
    $rate = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'))->rates->$currency;
    if ($rate === 0) {
        return $amount;
    }

    return $amount/$rate;
}

function getBankIdentification($bin){
    $bankId = file_get_contents('https://lookup.binlist.net/' . $bin);
    if (!$bankId)
        die('error!');
    return json_decode($bankId);
}

function ceiling($value, $precision = 0) {
    return ceil($value * pow(10, $precision)) / pow(10, $precision);
}


