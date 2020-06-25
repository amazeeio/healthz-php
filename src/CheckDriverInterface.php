<?php

namespace AmazeeIO\Health;

use AmazeeIO\Health\Check\CheckInterface;

interface CheckDriverInterface
{

    public function runChecks();

    public function pass();

    public function registerCheck(CheckInterface $check);

    public function customRunCheck($customRunner);
}