<?php

namespace AmazeeIO\Health\Check;


use AmazeeIO\Health\EnvironmentCollection;

class CheckMariadb extends BooleanCheck
{

    protected $appliesInCurrentEnvironment = false;

    protected $db_host = null;

    protected $db_username = null;

    protected $db_password = null;

    protected $db_database = null;


    public function __construct(EnvironmentCollection $env)
    {
        if ($env->has(['MARIADB_HOST', 'MARIADB_USERNAME', 'MARIADB_PASSWORD'])) {
            $this->appliesInCurrentEnvironment = true;
            $this->db_host = $env->get('MARIADB_HOST');
            $this->db_username = $env->get('MARIADB_USERNAME');
            $this->db_password = $env->get('MARIADB_PASSWORD');
            $this->db_database = $env->get('MARIADB_DATABASE');
        } else if ($env->has(['AMAZEEIO_DB_HOST', 'AMAZEEIO_DB_USERNAME', 'AMAZEEIO_DB_PASSWORD'])) {
            $this->appliesInCurrentEnvironment = true;
            $this->db_host = $env->get('AMAZEEIO_DB_HOST');
            $this->db_username = $env->get('AMAZEEIO_DB_USERNAME');
            $this->db_password = $env->get('AMAZEEIO_DB_PASSWORD');
            $this->db_database = $env->get('AMAZEEIO_SITENAME');
        }
    }

    public function appliesInCurrentEnvironment()
    {
        return $this->appliesInCurrentEnvironment;
    }

    public function result(): bool
    {
        try {
            $db = $this->getConnection();
            return $this->testRead($db);
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

    protected function testRead($conn)
    {
        $stmt = $conn->prepare('SHOW DATABASES');
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function description()
    {
        return "This test will attempt to connect to a database (if configured) and perform a simple read and write";
    }

    public function shortName()
    {
        return 'check_db';
    }


    protected function getConnection()
    {
        $dsn = "mysql:host={$this->db_host};dbname={$this->db_database}";
        try {
            $pdo = new \PDO($dsn, $this->db_username, $this->db_password);
        } catch (\Exception $exception) {
            throw $exception;
        }
        return $pdo;
    }

}