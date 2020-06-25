<?php

namespace AmazeeIO\Health\Format;

use AmazeeIO\Health\CheckDriverInterface;

class PrometheusFormat implements FormatInterface
{

    /** @var \AmazeeIO\Health\Format\CheckDriverInterface  */
    protected $checkDriver;

    public function __construct(CheckDriverInterface $checkDriver)
    {
        $this->checkDriver = $checkDriver;
    }

    public function httpHeaderContentType()
    {
        return "text/plain";
    }

    public function formattedResults()
    {
        $formattedResults = $this->checkDriver->customRunCheck(function ($applicableChecks) {
            $r = [];
            foreach ($applicableChecks as $name => $check) {
                //TODO: we'll need to make this sensitive to class
                // eventually, but for now we can just return the
                // status as a bool in the requisite
                $r[$name . "_info"] = sprintf("%s_info %s", $name, $check->result() ? 1 : 0);
            }
            return $r;
        });

        return implode("\n", $formattedResults);
    }

}