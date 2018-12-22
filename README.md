# REST Countries API

This is an example app to fetch country data by name or ISO code.

The JavaScript (ES6) and CSS are vanilla because they're small in size and don't need framework bloat to support them. [Guzzle](https://github.com/guzzle/guzzle) is used to make remote HTTP requests to the [REST Countries API](https://restcountries.eu/). The PHP framework is a microframework I made called [Bitty](https://github.com/bittyphp/bitty).

The PHP code follows [PSRs](https://www.php-fig.org/psr/), is fully tested via [PHPUnit](https://github.com/sebastianbergmann/phpunit), and uses [PHPStan](https://github.com/phpstan/phpstan) cranked to the max to prevent bugs.

## To View

```sh
# clone it
git clone git@github.com:bizurkur/restcountries.git
cd restcountries/

# install the composer packages
composer install

# start the server
cd public_html/
php -S localhost:8000
```

Then browse to localhost:8000 using the browser of your choice.
