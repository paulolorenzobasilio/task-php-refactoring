<?php

namespace App;

class Bank
{
    private $bank;

    public function __construct($bin)
    {
        $this->bank = $this->bankIdentification($bin);
    }

    public function getBank(){
        return $this->bank;
    }

    public function alpha2CountryCode(){
        return $this->bank->country->alpha2;
    }

    private function bankIdentification($bin)
    {
        $bankId = file_get_contents('https://lookup.binlist.net/' . $bin);
        if (!$bankId)
            die('error!');
        return json_decode($bankId);
    }
}
