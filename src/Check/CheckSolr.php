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

    protected $solrCore = 'drupal';

    protected $solrUser;

    protected $solrPassword;

    public function __construct(EnvironmentCollection $env)
    {
        if ($env->has(['SOLR_PORT'])) {
            $this->applies = true;
            $this->solrHost = $env->get('SOLR_PORT');
            $this->solrHost = $env->get('SOLR_HOST', 'solr');
            $this->solrCore = $env->get('SOLR_CORE', 'drupal');
            $this->solrUser = $env->get('SOLR_USER', 'drupal');
            $this->solrPassword = $env->get('SOLR_PASSWORD', 'drupal');
        }
    }

    public function appliesInCurrentEnvironment()
    {
        return $this->applies;
    }

    public function result(): bool
    {
        $config = [
          'endpoint' => [
            'localhost' => [
              'host' => $this->solrHost,
              'port' => $this->solrPort,
              'path' => '/',
              'core' => $this->solrCore,
            ],
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