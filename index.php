<?php

include_once(__DIR__ . "/vendor/autoload.php");

use AmazeeIO\Health\CheckDriver;

// Note, we don't use 500s because of potential negative caching
// for example, Akamai
const DEFAULT_FAIL_HTTP_RESPONSE = 500;

//Wrap any environment vars we want to pass to our checks
$environment = new \AmazeeIO\Health\EnvironmentCollection($_SERVER);

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
  $psr17Factory, // ServerRequestFactory
  $psr17Factory, // UriFactory
  $psr17Factory, // UploadedFileFactory
  $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();

$driver = new CheckDriver();
foreach (include(__DIR__ . '/checks.conf.php') as $check) {
    $driver->registerCheck(new $check($environment));
}
$queryParams = $serverRequest->getQueryParams();

if(key_exists("format", $queryParams) && $queryParams["format"] == "prometheus") {
    $formatter = new \AmazeeIO\Health\Format\PrometheusFormat($driver);
} else {
    $formatter = new \AmazeeIO\Health\Format\JsonFormat($driver);
}

$responseBody = $psr17Factory->createStream($formatter->formattedResults());
//$responseBody = $psr17Factory->createStream(print_r($queryParams, true));
$response = $psr17Factory->createResponse($driver->pass() ? 200 : $environment->get('HEALTHZ_PHP_HTTP_FAIL_CODE', DEFAULT_FAIL_HTTP_RESPONSE))->withBody($responseBody)
  ->withHeader('Cache-Control','must-revalidate, no-cache, private')
  ->withHeader('Vary','User-Agent')
  ->withHeader('Content-Type', $formatter->httpHeaderContentType());

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
