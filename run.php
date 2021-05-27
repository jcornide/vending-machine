<?php

require __DIR__.'/vendor/autoload.php';

use App\Ui\Console\Start;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new Start());

$application->run();
