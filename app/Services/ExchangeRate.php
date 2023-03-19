<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeRate
{
    public function getExchangeRate()
    {
        // // Define exchange rates URL
        $exchangeRatesUrl = config('constants.EXCHANGE_RATE_URL');

        // // Get exchange rates
        $exchangeRatesResponse = Http::get($exchangeRatesUrl);
        $exchangeRates = json_decode($exchangeRatesResponse->body(), true);
        return $exchangeRates;
    }
}
