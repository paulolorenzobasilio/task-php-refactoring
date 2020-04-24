<?php

namespace App;

class Transaction
{
    private $bin;
    private $amount;
    private $currency;
    private $amountRate;

    private $euCountries = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
        'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    public function __construct($transaction)
    {
        list(
            'bin' => $this->bin,
            'amount' => $this->amount,
            'currency' => $this->currency
        ) = get_object_vars($transaction);

        $this->amountRate = $this->isEuCurrency()
            ? $this->amount
            : $this->computeExchangeRate();
    }

    public function commissionRate($countryCode)
    {
        $isEuCountry = $this->isEuCountry($countryCode);

        return $isEuCountry ? 0.01 : 0.02;
    }

    public function getBin()
    {
        return $this->bin;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getAmountRate()
    {
        return $this->amountRate;
    }

    private function isEuCurrency()
    {
        return $this->currency === 'EUR';
    }

    private function computeExchangeRate()
    {
        $rate = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'))->rates->{$this->currency};
        if ($rate === 0) {
            return $this->amount;
        }

        return ($this->amount / $rate);
    }

    private function isEuCountry($code)
    {
        $this->euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
            'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        return in_array($code, $this->euCountries);
    }
}
