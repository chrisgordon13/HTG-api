<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/lib/autoload.php';

$deps = new Pimple();

$deps['slim_options']   = ['debug' => true];

$deps['salt']           = '$2y$11$' . substr(md5(uniqid(rand(),true)), 0, 22);

$deps['orm']            = function() {

                            class o extends ORM { public function __construct() { parent::__construct(''); }};

                            $orm = new o;

                            //$orm::configure('mysql:host=10.128.12.164;dbname=HealthyTravelGal');
                            //$orm::configure('username', 'htg');
                            //$orm::configure('password', 'rt304X^H#$093JlKb@]]');

                            $orm::configure('mysql:host=10.128.12.164;dbname=TripNanny');
                            $orm::configure('username', 'tripnanny');
                            $orm::configure('password', 'kDb6c$9a');

                            return $orm;
                        };

$deps['auth']           = function() { return new AuthHelper; };

$deps['key']            = function() { return new KeyHelper; };

$deps['date']           = function() { return new DateHelper; };
