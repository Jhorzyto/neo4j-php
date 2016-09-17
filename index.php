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

    foreach ($data as &$centro){
        $createCypher($client, [
            'id'        => $centro->localidade->idLocalidade,
            'codigo'    => $centro->localidade->idCentro,
            'nome'      => $centro->localidade->nome,
            'sigla'     => $centro->localidade->sigla,
            'marcador'  => $centro->localidade->marcador,
            'latitude'  => $centro->localidade->latitude,
            'longitude' => $centro->localidade->longitude,
        ], "uema", "CentrosUema");

        echo "Criando centro {$centro->localidade->nome} <br>";

        foreach ($centro->localidade->vertices as &$vertice){
            $createCypher($client, [
                'idCentro'  => $centro->localidade->idLocalidade,
                'latitude'  => $vertice->latitude,
                'longitude' => $vertice->longitude,
            ], "vertice", "LocalVertice");
        }

        $client->run("MATCH 
                      (centro:CentrosUema), (vertices:LocalVertice)  
                      WHERE 
                      centro.id = '{$centro->localidade->idLocalidade}'
                      AND 
                      vertices.idCentro = '{$centro->localidade->idLocalidade}'
                      CREATE 
                      (centro)-[:VERTICES]->(vertices)");

    }


//    var_dump($createCypher($client, [
//        'codigo'    => $data[1]->localidade->idCentro,
//        'nome'      => $data[1]->localidade->nome,
//        'sigla'     => $data[1]->localidade->sigla,
//        'marcador'  => $data[1]->localidade->marcador,
//        'latitude'  => $data[1]->localidade->latitude,
//        'longitude' => $data[1]->localidade->longitude,
//    ], "uema", "CentrosUema")->getRecord());

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}";
}