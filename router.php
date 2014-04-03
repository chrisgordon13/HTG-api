<?php
// Config supplies a dependency container named $deps
//require 'config.php';

//$app = new \Slim\Slim($deps['slim_options']);
$app = new \Slim\Slim();

//$app->deps  = $deps;

// Route handlers
//foreach (glob("routes/*.php") as $filename) {
//    require $filename;
//}

$app->run();
