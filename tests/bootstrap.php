<?php

if (file_exists(__DIR__.'/../vendor/autoload.php')) { // standalone
    $loader = require __DIR__.'/../vendor/autoload.php';
} else { // se está programando dentro de la carpeta vendor
    $loader = require __DIR__.'/../../../autoload.php';
}

$loader->add('SIU\Afip\Test', __DIR__);