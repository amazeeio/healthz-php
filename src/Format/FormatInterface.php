<?php
/**
 * Created by PhpStorm.
 * User: bomoko
 * Date: 15/06/20
 * Time: 6:30 AM
 */

namespace AmazeeIO\Health\Format;

interface FormatInterface
{

    public function httpHeaderContentType();

    public function formattedResults();
}