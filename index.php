<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Client;

try {
    //Função para criar nodo
    $createCypher = function (Client $client, array $data, $nameObject, $nameClass) {
        try {
            return $client->run("CREATE ({$nameObject}:{$nameClass}) SET {$nameObject} += {{$nameObject}}", [$nameObject => $data]);
        } catch (Exception $e) {
            throw new Exception("Não foi possível cadastrar o objeto {$nameObject} no nó {$nameClass}. Error: {$e->getMessage()}");
        }
    };

    $client = ClientBuilder::create()
        //Criar uma conexão com neo4j
        ->addConnection('default', 'http://neo4j:12qwaszx@localhost:7474')// Example for HTTP connection configuration (port is optional)
        ->build();

    //Requisição para obter os dados json
    $response = \Httpful\Request::get("http://localhost/neo4j/dados.php")
        ->send();

    //verificar se foi possível puxar os dados
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

        echo "Criando centro {$centro->localidade->nome} \n";

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

        foreach ($centro->areas as &$area){
            $createCypher($client, [
                'idCentro'   => $centro->localidade->idLocalidade,
                'idArea'     => $area->idArea,
                'idTipoArea' => $area->idTipoArea,
                'endereco'   => $area->endereco,
                'valor'      => $area->valor,
                'proprio'    => $area->proprio,
                'doacao'     => $area->doacao,
                'documento'  => $area->documento,
                'publicacao' => $area->publicacao,
                'observacao' => $area->observacao,
            ], "area", "Areas");

            foreach ($area->arquivos as &$arquivo){
                $createCypher($client, [
                    'idArea'        => $area->idArea,
                    'idArquivo'     => $arquivo->idArquivo,
                    'idTipoArquivo' => $arquivo->idTipoArquivo,
                    'descricao'     => $arquivo->descricao,
                    'path'          => $arquivo->path,
                ], "arquivos", "Arquivos");
            }

            $client->run("MATCH 
                      (area:Areas), (arquivos:Arquivos)  
                      WHERE 
                      area.idArea = '{$area->idArea}'
                      AND 
                      arquivos.idArea = '{$area->idArea}'
                      CREATE 
                      (area)-[:ARQUIVOS]->(arquivos)");
        }

        $client->run("MATCH 
                      (centro:CentrosUema), (area:Areas)  
                      WHERE 
                      centro.id = '{$centro->localidade->idLocalidade}'
                      AND 
                      area.idCentro = '{$centro->localidade->idLocalidade}'
                      CREATE 
                      (centro)-[:AREAS]->(area)");

        foreach ($centro->cursos as &$curso) {
            $createCypher($client, [
                'idCentro'    => $centro->localidade->idLocalidade,
                'idCurso'     => $curso->idCurso,
                'idTipoCurso' => $curso->idTipoCurso,
                'tipoCurso'   => $curso->tipoCurso,
                'descricao'   => $curso->descricao,
            ], "curso", "Cursos");
        }

        $client->run("MATCH 
                      (centro:CentrosUema), (curso:Cursos)  
                      WHERE 
                      centro.id = '{$centro->localidade->idLocalidade}'
                      AND 
                      curso.idCentro = '{$centro->localidade->idLocalidade}'
                      CREATE 
                      (centro)-[:CURSOS]->(curso)");

        foreach ($centro->gestores as &$gestor) {
            $createCypher($client, [
                'idCentro'     => $centro->localidade->idLocalidade,
                'idGestor'     => $gestor->idGestor,
                'idTipoGestor' => $gestor->idTipoGestor,
                'tipoGestor'   => $gestor->tipoGestor,
                'nome'         => $gestor->nome,
                'telefones'    => implode(', ', $gestor->telefones),
                'email'        => $gestor->email,
            ], "gestor", "Gestores");
        }

        $client->run("MATCH 
                      (centro:CentrosUema), (gestor:Gestores)  
                      WHERE 
                      centro.id = '{$centro->localidade->idLocalidade}'
                      AND 
                      gestor.idCentro = '{$centro->localidade->idLocalidade}'
                      CREATE 
                      (centro)-[:GESTORES]->(gestor)");

    }

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}";
}