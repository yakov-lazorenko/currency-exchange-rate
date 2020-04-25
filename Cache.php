<?php

namespace App\Services\CurrencyExchangeRate;

use Illuminate\Filesystem\Filesystem;


/**
 *   Class Cache.
 *   The class is designed to cache currency exchange rates to a file.
 */
class Cache
{
    /**
     *   @var string The path to the cache file where currency exchange rates are stored.
     */
    public $path_to_cache_file;


    /**
     *   Create a new Cache class instance.
     *
     *   @param string $path_to_cache_file The path to the cache file where currency
     *                                     exchange rates are stored.
     */
    public function __construct($path_to_cache_file)
    {
        if (empty($path_to_cache_file)) {
            throw new \Exception('Path to cache file not specified.');
        }
        $this->path_to_cache_file = $path_to_cache_file;
    }


    /**
     *   Get currency exchange rates from cache file.
     *
     *   @return array Currency exchange rates.
     */
    public function get()
    {
        $filesystem = new Filesystem;
        if (!$filesystem->exists($this->path_to_cache_file)) {
            throw new \Exception('Cache file not exists.');
        }
        return \json_decode($filesystem->get($this->path_to_cache_file), true);
    }


    /**
     *   Save currency exchange rates to cache file.
     *
     *   @param array $rates Currency exchange rates.
     */
    public function save($rates)
    {
        (new Filesystem)->put(
            $this->path_to_cache_file,
            \json_encode($rates,  JSON_PRETTY_PRINT)
        );
    }


    public function clear()
    {
        (new Filesystem)->delete($this->path_to_cache_file);
    }
}
