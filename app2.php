<?php

require __DIR__ . '/vendor/autoload.php';

use App\Bank;
use App\Transaction;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$log = new Logger('debug');
$log->pushHandler(new StreamHandler('dev.log', Logger::INFO));

// TODO: create object for commissions?

foreach (explode("\n", file_get_contents($argv[1])) as $row) {
    if ($row === "") {
        return;
    }

    $transaction = new Transaction(json_decode($row));
    
    $bank = new Bank($transaction->getBin());
    $countryCode = $bank->alpha2CountryCode();
    
    $comission = $transaction->getAmountRate() * $transaction->commissionRate($countryCode);

    echo ceiling($comission, 2);
    print "\n";
}

function ceiling($value, $precision = 0)
{
    return ceil($value * pow(10, $precision)) / pow(10, $precision);
}
