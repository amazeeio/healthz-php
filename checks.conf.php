<?php

/***
 * This file serves as a single location for configuring which checks get run
 * under which circumstances
 */

return [
  \AmazeeIO\Health\Check\CheckMariadb::class,
  \AmazeeIO\Health\Check\CheckNginx::class,
  \AmazeeIO\Health\Check\CheckPhp::class,
  \AmazeeIO\Health\Check\CheckRedis::class,
  \AmazeeIO\Health\Check\CheckSolr::class,
];