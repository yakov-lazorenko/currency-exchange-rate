Получение актуальных обменных курсов валют.

Разработан класс CurrencyExchangeRate, который позволяет получить актуальные обменные курсы валют с помощью web-сервиса currencylayer.com.

Класс предназначен для использования с PHP-фреймворком Laravel.

Необходимо создать бесплатный аккаунт на сайте https://currencylayer.com и получить там ключ доступа (ACCESS_KEY).

Пример использования класса для получения курса евро относительно доллара и конвертации 100 евро в доллары:

$tool = new CurrencyExchangeRate($accessKey);

$rate = $tool->getExchangeRate('EUR', 'USD');

$price = $tool->convert(100, 'EUR', 'USD');
