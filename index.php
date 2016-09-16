<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Client;

try {

    $createCypher = function (Client $client, array $data, $nameObject, $nameClass) {
        try {
            return $client->run("CREATE ({$nameObject}:{$nameClass}) SET {$nameObject} += {{$nameObject}}", [$nameObject => $data]);
        } catch (Exception $e) {
            throw new Exception("Não foi possível cadastrar o objeto {$nameObject} no nó {$nameClass}. Error: {$e->getMessage()}");
        }
    };

    $client = ClientBuilder::create()
        ->addConnection('default', 'http://neo4j:12qwaszx@localhost:7474')// Example for HTTP connection configuration (port is optional)
        ->build();

    $response = \Httpful\Request::get("http://localhost/neo4j/dados.php")
        ->send();

    if ($response->hasErrors())
        throw new Exception("Não foi possível conectar o GPU");

    $body = $response->body;

    if (!isset($body->data) || !is_array($body->data))
        throw new Exception("Nenhum dado foi retornado!");

    $data = $body->data;

    var_dump($createCypher($client, [
        'codigo'    => $data[1]->localidade->idCentro,
        'nome'      => $data[1]->localidade->nome,
        'sigla'     => $data[1]->localidade->sigla,
        'marcador'  => $data[1]->localidade->marcador,
        'latitude'  => $data[1]->localidade->latitude,
        'longitude' => $data[1]->localidade->longitude,
    ], "uema", "CentrosUema")->getRecord());

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}";
}