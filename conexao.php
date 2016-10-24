<?php

use GraphAware\Neo4j\Client\ClientBuilder;

$protocol = 'http';
$database = 'case';
$password = 'oesnCswC93D3fM6zs80K';
$host     = 'hobby-hhpfpgdaojekgbkeapbgdgol.dbs.graphenedb.com';
$port     = '24789';

$client = ClientBuilder::create()
    //Criar uma conexão com neo4j
    ->addConnection('default', "{$protocol}://{$database}:{$password}@{$host}:{$port}")
    ->setDefaultTimeout(30)
    ->build();