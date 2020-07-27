<?php

namespace AmazeeIO\Health\Check;

use AmazeeIO\Health\EnvironmentCollection;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;


class CheckSolr extends BooleanCheck
{

    protected $applies = false;

    protected $solrHost;

    protected $solrPort;

    protected $solrCore = 'drupal';

    protected $solrPath = '/';

    public function __construct(EnvironmentCollection $env)
    {
        if($env->has(['SOLR_SERVICE_PORT', 'SOLR_SERVICE_HOST'])) { //First we deal with Kubernetes service environment vars
            $this->solrPort = $env->get('SOLR_SERVICE_PORT');
            $this->solrHost = $env->get('SOLR_SERVICE_HOST');
        } elseif ($env->has(['SOLR_PORT', 'SOLR_HOST']) && is_numeric($env->get('SOLR_PORT'))) {
            $this->solrPort = $env->get('SOLR_PORT');
            $this->solrHost = $env->get('SOLR_HOST');
        }

        $this->solrCore = $env->get('SOLR_CORE', 'drupal');
        $this->solrPath = $env->get('SOLR_PATH', '/');

        //Minimally, we need a host, port for this to apply
        $this->applies = array_reduce([
            $this->solrHost,
            $this->solrPort,
        ], 
        function($val, $element) { return $val && !empty($element);}, 
        true);
    }

    public function appliesInCurrentEnvironment()
    {
        return $this->applies;
    }

    public function result(): bool
    {
        $config = [
          'endpoint' => [
            'localhost' => $this->buildConfig(),
          ],
        ];

        try {
            $client = new Client(
              new Curl(),
              new EventDispatcher(),
              $config
            );

            $ping = $client->createPing();
            $result = $client->ping($ping);

            if (($result->getData())['status'] == 'OK') {
                return true;
            }

            return false;

        } catch (\Exception $exception) {
            return false;
        }
    }

    private function buildConfig() {
        return [
          'host' => $this->solrHost,
          'port' => $this->solrPort,
          'core' => $this->solrCore,
          'path' => $this->solrPath,
        ];
    }

    public function status()
    {
        if (!$this->result()) {
            return self::STATUS_FAIL;
        }

        return self::STATUS_PASS;
    }

    public function description()
    {
        return "This check tests to see if Solr is available";
    }

    public function shortName()
    {
        return "check_solr";
    }
}