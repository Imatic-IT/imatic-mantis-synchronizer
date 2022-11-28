<?php

$contollers = glob(__DIR__ . '/controller/*.php');
$models = glob(__DIR__ . '/model/*.php');

$files = array_merge($contollers, $models);

foreach ($files as $file) {
    require($file);
}