<?php
/**
 * Created by PhpStorm.
 * User: bomoko
 * Date: 15/06/20
 * Time: 5:58 AM
 */

namespace AmazeeIO\Health;

use AmazeeIO\Health\Check\CheckInterface;

interface CheckDriverInterface
{

    public function runChecks();

    public function pass();

    public function registerCheck(CheckInterface $check);
}