<?php

namespace AmazeeIO\Health\Check;

/**
 * Checks extending this are expected to pass back a true/false from their
 * result() function
 *
 * Class BooleanCheck
 *
 * @package AmazeeIO\Health\Check
 */
abstract class BooleanCheck implements CheckInterface
{
    abstract public function result(): bool;
}