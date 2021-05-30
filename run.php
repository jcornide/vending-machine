<?php

require __DIR__.'/vendor/autoload.php';

use App\Ui\Console\Run;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new Run());

$application->run();
