<?php

namespace AmazeeIO\Health\Format;

use AmazeeIO\Health\CheckDriverInterface;

class JsonFormat implements FormatInterface
{
    protected $checkDriver;

    public function __construct(CheckDriverInterface $checkDriver)
    {
        $this->checkDriver = $checkDriver;
    }

    public function httpHeaderContentType()
    {
        return "application/json";
    }

    public function formattedResults()
    {
        return json_encode($this->checkDriver->runChecks());
    }
}