<?php

namespace AmazeeIO\Health;


use AmazeeIO\Health\Check\CheckInterface;

class CheckDriver implements CheckDriverInterface
{

    protected $environment;

    protected $registeredChecks = [];

    protected $applicableChecks = [];

    protected $lastRunResults = null;

    protected $lastRunResultStatuses = null;

    protected $hasRun = false;

    public function __construct()
    {

    }

    public function runChecks()
    {
        if (count($this->applicableChecks) == 0) {
            throw new NoApplicableCheckException("There were no applicable checks that could be run in this environment");
        }

        $checkStatuses = [];
        foreach ($this->applicableChecks as $name => $check) {
            $checkStatuses[$check->shortName()] = $check->status();
        }

        $this->lastRunResultStatuses = $checkStatuses;
        $this->hasRun = true;
        return $checkStatuses;
    }


    public function pass()
    {
        if (!$this->hasRun) {
            $this->runChecks();
        }

        if ($this->status() == CheckInterface::STATUS_FAIL) {
            return false;
        }

        return true;

    }

    public function status()
    {
        if (!$this->hasRun) {
            $this->runChecks();
        }

        $warning = false;

        foreach ($this->lastRunResultStatuses as $status) {
            switch ($status) {
                case(CheckInterface::STATUS_FAIL):
                    return CheckInterface::STATUS_FAIL;
                    break;
                case(CheckInterface::STATUS_WARN):
                    $warning = true;
                    break;
            }
        }
        return $warning ? CheckInterface::STATUS_WARN : CheckInterface::STATUS_PASS;
    }

    public function registerCheck(CheckInterface $check)
    {
        $this->storeRegisteredCheck($check);

        if ($check->appliesInCurrentEnvironment()) {
            $this->queueCheckToRun($check);
        }
    }

    protected function storeRegisteredCheck(CheckInterface $check)
    {
        $this->registeredChecks[$check->shortName()] = $check;
    }

    protected function queueCheckToRun(CheckInterface $check)
    {
        $this->applicableChecks[$check->shortName()] = $check;
    }

}