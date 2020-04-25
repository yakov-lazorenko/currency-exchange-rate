# currency-exchange-rate

Получение актуальных обменных курсов валют.

Класс CurrencyExchangeRate позволяет получить актуальные обменные курсы 168 валют с помощью web-сервиса https://currencylayer.com.

Для использования этого web-сервиса необходимо создать бесплатный аккаунт на сайте https://currencylayer.com и получить там ключ доступа (ACCESS_KEY).

Получить курсы всех 168 валют можно с помощью метода getExchangeRates(), данные записываются (кэшируются) в файл. Полный путь к кэш-файлу нужно указать в конструкторе класса CurrencyExchangeRate. Кэш-файл нужен для минимизации обращений к HTTP API, поскольку количество запросов для бесплатных аккаунтов ограничено.

Чтобы получить обменный курс заданной валюты относительно другой, нужно вызвать метод getExchangeRate, передав в аргументы коды этих валют.

Инструмент предназначен для использовния с PHP-фреймворком Laravel, но может быть легко переделан для других фреймворков.

Пример использования для получения курса евро относительно доллара и конвертации 100 евро в доллары:

    // access key for free account form HTTP API https://currencylayer.com
    $accessKey = 'your-access-key';

    // full path to cache file to save currency exchange rates
    $pathToCacheFile = 'full-path-to-cache-file';

    try {
        // create CurrencyExchangeRate instance
        $currencyTool = new CurrencyExchangeRate($accessKey, $pathToCacheFile);

        // get currency exchange rates from HTTP API https://currencylayer.com
        // and save it in a cache file
        $rates = $currencyTool->getExchangeRates();
        //  returns array [
        //      "USDAED" => 3.673102
        //      "USDAFN" => 75.838566
        //      "USDAMD" => 479.809805
        //      ...

        // get exchange rate EUR/USD
        $rate = $currencyTool->getExchangeRate('EUR', 'USD');// 1.08172

        // convert 100 EUR to USD
        $price = $currencyTool->convert(100, 'EUR', 'USD');// 108.172
    } catch (\Exception $e) {
        echo "\n" . 'Error: ' . $e->getMessage() . "\n";
    }
