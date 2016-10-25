<?php

use GraphAware\Neo4j\Client\ClientBuilder;

$protocol = 'http';
$database = 'neo4j';
$password = '12qwaszx';
$host     = 'localhost';
$port     = '7474';

$client = ClientBuilder::create()
    //Criar uma conexão com neo4j
    ->addConnection('default', "{$protocol}://{$database}:{$password}@{$host}:{$port}")
    ->setDefaultTimeout(30)
    ->build();