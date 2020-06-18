<?php

namespace AmazeeIO\Health\Check;

use AmazeeIO\Health\EnvironmentCollection;
use Predis\Client;


class CheckRedis extends BooleanCheck
{

    protected $redis_host;

    protected $redis_port;

    protected $appliesInCurrentEnvironment = false;

    public function __construct(EnvironmentCollection $env)
    {
        if($env->has('REDIS_HOST', 'REDIS_SERVICE_PORT')) {
            $this->redis_host = $env->get('REDIS_HOST');
            $this->redis_port = $env->get('REDIS_SERVICE_PORT');
            $this->appliesInCurrentEnvironment = true;
        }
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
              'host' => $this->redis_host,
              'port' => $this->redis_port,
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