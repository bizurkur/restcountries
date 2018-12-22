<?php

require(dirname(__DIR__).'/vendor/autoload.php');

use Bitty\Application;
use Bitty\Http\JsonResponse;
use Bitty\View\Twig;
use Bizurkur\CountryLookup;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;

$app = new Application();

$app->getContainer()->set('view', function () {
    return new Twig(dirname(__DIR__).'/templates/');
});
$app->getContainer()->set('country.lookup', function () {
    return new CountryLookup(
        new Client(['base_uri' => 'https://restcountries.eu/rest/v2/']),
        [
            'name',
            'alpha2Code',
            'alpha3Code',
            'flag',
            'region',
            'subregion',
            'population',
            'languages',
        ]
    );
});

$app->get('/', function (ServerRequestInterface $request) {
    return $this->get('view')->renderResponse('index.html.twig');
});

$app->get('/search', function (ServerRequestInterface $request) {
    $q = $request->query->get('q');

    $result = $this->get('country.lookup')->lookup($q);

    return new JsonResponse($result);
});

$app->run();
