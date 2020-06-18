<?php

include_once(__DIR__ . "/vendor/autoload.php");

use AmazeeIO\Health\CheckDriver;

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

$formatter = new \AmazeeIO\Health\Format\JsonFormat($driver);

$responseBody = $psr17Factory->createStream($formatter->formattedResults());
$response = $psr17Factory->createResponse($driver->pass() ? 200 : 500)->withBody($responseBody)
  ->withHeader('Cache-Control','must-revalidate, no-cache, private')
  ->withHeader('Vary','User-Agent')
  ->withHeader('Content-Type', $formatter->httpHeaderContentType());

(new \Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
