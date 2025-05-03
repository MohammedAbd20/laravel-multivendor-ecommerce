<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyConverter
{
    private $apiKey;
    protected $baseUrl = 'https://free.currconv.com/api/v7';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }


    public function convert(string $from,string $to,float $amount = 1): float{
        // $url = 'https://free.currencyconverterapi.com/api/v7/convert?q='. $from . '_' . $to;

        $q = "{$from}_{$to}";
        $ressponse = Http::baseUrl($this->baseUrl)
        ->get('/convert',[
            'q' => $q,
            'compact' => 'y',
            'apiKey' => $this->apiKey,
        ]);

        $result = $ressponse->json();
        dd($result);
        // if (isset($result['results'][$q]['val']) && is_numeric($result['results'][$q]['val'])) {
        //     return $result['results'][$q]['val'] * $amount;
        // }
        return $result[$q]['val'] * $amount;
    }




}



