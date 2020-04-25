<?php

namespace App\Services\CurrencyExchangeRate;
use GuzzleHttp;
use Storage;
use App\Services\CurrencyExchangeRate\Cache;


/**
 *   Class CurrencyExchangeRate.
 *   Tool for obtaining currency exchange rates.
 */
class CurrencyExchangeRate
{

    /**
     *   HTTP API URL
     */
    const API_URL = 'http://api.currencylayer.com/live';

    /**
     *   @var string Access Key for HTTP API https://currencylayer.com
     */
    public $api_access_key;

    /**
     *   @var string The path to the cache file where currency exchange 
     *               rates are stored.
     */
    public $path_to_cache_file;

    /**
     *   @var Cache The class for caching currency exchange rates to a file.
     */
    protected $cache;

    /**
     *   @var integer Number of digits after the decimal point for 
     *                currency exchange rates.
     */
    protected $precision;


    /**
     *   Create a new CurrencyExchangeRate class instance.
     *
     *   @param string $api_access_key Access Key for HTTP API https://currencylayer.com
     *   @param string $path_to_cache_file The path to the cache file where 
                                           currency exchange rates are stored.
     */
    public function __construct($api_access_key, $path_to_cache_file)
    {
        if (empty($api_access_key)){
            throw new \Exception('API access key not specified.');
        }
        if (empty($path_to_cache_file)){
            throw new \Exception('Path to cache file not specified.');
        }
        $this->api_access_key = $api_access_key ?? '';
        $this->path_to_cache_file = $path_to_cache_file ?? '';
        $this->cache = new Cache($this->path_to_cache_file);
        $this->precision = 6;
    }


    /**
     *   Get currency exchange rates for 168 currencies from HTTP API 
     *   https://currencylayer.com and save it in a cache file.
     *
     *   @return array Currency exchange rates
     */
    public function getExchangeRates()
    {
        $rates = $this->getExchangeRatesFromHttpApi();
        $this->cache->save($rates);
        return $rates;
    }


    /**
     *   Get currency exchange rate.
     *
     *   For example, exchange rate 'EUR'/'USD' can be obtained
     *   by calling this method with such arguments: getExchangeRate('EUR', 'USD').
     *
     *   @param string $currencyFrom Currency code, the value of which must 
     *                               be obtained in another currency ($currencyTo).
     *   @param string $currencyTo Currency code in which you want to get the value 
     *                             of the currency with the code $currencyFrom.
     *   @param integer|null $precision Number of digits after the decimal 
     *                                  point for exchange rate.
     *   @return float Currency exchange rate.
     */
    public function getExchangeRate($currencyFrom, $currencyTo, $precision = null)
    {
        if (!($rates = $this->getExchangeRatesFromCache())) {
            throw new \Exception('Data retrieving error.');
        }

        $precision = $precision ?? $this->precision;

        if ($currencyFrom == 'USD') {
            if (!isset($rates['USD' . $currencyTo])) {
                throw new \Exception('Data format error.');
            }
            return round(
                (float)($rates['USD' . $currencyTo]),
                $precision
            ) ?? false;
        }

        if ($currencyTo == 'USD') {
            if (!isset($rates['USD' . $currencyFrom])) {
                throw new \Exception('Data format error.');
            }
            return isset($rates['USD' . $currencyFrom]) ? round(
                (float)(1/$rates['USD' . $currencyFrom]),
                $precision
            ) : false;
        }

        if (!isset($rates['USD' . $currencyFrom])
            || !isset($rates['USD' . $currencyTo])
        ) {
            throw new \Exception('Data format error.');
        }

        return round(
            (float)($rates['USD' . $currencyTo] / $rates['USD' . $currencyFrom]),
            $precision
        );
    }


    /**
     *   Convert money.
     *
     *   For example, to convert 100 EUR to USD, you must call this method with
     *   such arguments: convert(100, 'EUR', 'USD').
     *
     *   @param float|integer $amount  Money amount.
     *   @param string $currencyFrom Currency code, the value of which must 
     *                               be obtained in another currency ($currencyTo).
     *   @param string $currencyTo Currency code in which you want to get the value 
     *                             of the currency with the code $currencyFrom.
     *   @param integer|null $precision Number of digits after the decimal 
     *                                  point for convertion result.
     *   @return float
     */
    public function convert($amount, $currencyFrom, $currencyTo, $precision = null)
    {
        if (!($rate = $this->getExchangeRate($currencyFrom, $currencyTo))) {
            return null;
        }
        return round((float)$amount * $rate , $precision ?? $this->precision);
    }


    /**
     *   Set number of digits after the decimal point for currency exchange rates.
     *
     *   @param integer $precision Number of digits after the decimal point for 
     *                             currency exchange rates.
     */
    public function setPrecision($precision)
    {
        if (!is_int($precision)) {
            throw new \Exception('Precision must be an integer.');
        }
        $this->precision = $precision;
    }


    /**
     *   Get number of digits after the decimal point for currency exchange rates.
     *
     *   @return integer
     */
    public function getPrecision()
    {
        return $this->precision;
    }


    public function getExchangeRatesFromHttpApi()
    {
        $url = self::API_URL . '?access_key=' . $this->api_access_key;
        $client = new GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $url, ['timeout' => 10]);
        } catch (\Throwable $e) {
            throw new \Exception('API connection error: Guzzle exception.');
        }
        if (empty($response) ||
            $response->getStatusCode() != 200 ||
            empty($response->getBody())
        ) {
            throw new \Exception('API response error.');
        }
        $data = \json_decode($response->getBody(), true);
        if (!$data || empty($data['success']) || empty($data['quotes'])) {
            throw new \Exception('Response data format error.');
        }
        return $data['quotes'];
    }


    public function getExchangeRatesFromCache()
    {
        return $this->cache->get();
    }

}

