<?php
declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Core\Helpers;
use App\Core\App;

Helpers::loadEnv(__DIR__.'/../.env');

$app = new App();
$app->run();
