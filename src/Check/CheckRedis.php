<?php

namespace AmazeeIO\Health\Check;

use AmazeeIO\Health\EnvironmentCollection;
use Predis\Client;


class CheckRedis extends BooleanCheck
{

    protected $redisHost;

    protected $redisPort;

    protected $appliesInCurrentEnvironment = false;

    public function __construct(EnvironmentCollection $env)
    {
        // In general, the network service provided by the DBaaS/RaaS operators are used as a preference.
        // Hence the order we examine the env vars
        $this->redisHost = $env->get('REDIS_HOST',
            $env->get('REDIS_SERVICE_HOST'));
        $this->redisPort = $env->get('REDIS_SERVICE_PORT');
        
        $this->appliesInCurrentEnvironment = !empty($this->redisHost) && !empty($this->redisPort);
    }

    public function appliesInCurrentEnvironment()
    {
        return $this->appliesInCurrentEnvironment;
    }

    public function result(): bool
    {
        try {
            $client = new Client([
              'scheme' => 'tcp',
              'host' => $this->redisHost,
              'port' => $this->redisPort,
              'timeout' => '0.5',
            ]);

            $response = $client->executeRaw([
              'PING',
            ]);

            return $response == "PONG";
        } catch (\Exception $exception) {
            return false;
        }
    }


    public function status()
    {
        if(!$this->result()) {
            return self::STATUS_FAIL;
        }

        return self::STATUS_PASS;
    }

    public function description()
    {
        return "This check tests to see if Redis is available";
    }

    public function shortName()
    {
        return "check_redis";
    }
}