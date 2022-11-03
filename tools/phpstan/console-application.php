<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

(new Dotenv())->bootEnv($root . '/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);

return new Application($kernel);
