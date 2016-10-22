<?php

if(!file_exists('./vendor/autoload.php'))
    die('Por favor, instale as dependencias do composer. $_: composer install');

require_once './vendor/autoload.php';

//Usar pelo http://localhost/neo4j/?de=Renascença&para=Cohab

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Formatter\Type\Path;

try {

    $de   = isset($_GET['de'])   ? $_GET['de']    : false ;
    $para = isset($_GET['para']) ? $_GET['para']  : false ;

    if(!$de || !$para)
        throw new Exception("Os parametros de requisições são inválidos! É necessário o parametro 'de' e 'para'");


    $de   = !empty($de)   ? $de   : false ;
    $para = !empty($para) ? $para : false ;

    if(!$de || !$para)
        throw new Exception("Os parametros de requisições estão vázios.");

    $cypher = "MATCH (de:Bairro {nome: {de}}), 
                     (para:Bairro {nome: {para}}), 
                     caminhos = (de)-[:DISTANCIA*]->(para)
               RETURN caminhos AS melhorCaminho, 
                      reduce(km = 0, caminho in relationships(caminhos) | km + caminho.km) AS totalKm 
               ORDER BY totalKm ASC
               LIMIT 1";

    $protocol = 'http';
    $database = 'neo4j';
    $password = '12qwaszx';
    $host     = 'localhost';
    $port     = '7474';

    $client = ClientBuilder::create()
        //Criar uma conexão com neo4j
        ->addConnection('default', "{$protocol}://{$database}:{$password}@{$host}:{$port}")
        ->build();

    $result = $client->run($cypher, [
        'de'   => $de,
        'para' => $para,
    ]);

    if($result->size() == 0)
        throw new Exception('Nenhum resultado foi encontrado para essa rota!');

    $dados = $result->firstRecord();

    if(!$dados->hasValue('melhorCaminho') || !$dados->hasValue('totalKm'))
        throw new Exception('O melhor caminho e a quantidade de quilometros não foi retornado!');

    $melhorCaminho = $dados->get('melhorCaminho');
    $totalKm       = $dados->get('totalKm');

    if(!$melhorCaminho instanceof Path)
        throw new Exception('O melhor caminho não é uma instancia de Path!');

    elseif(!$melhorCaminho->length())
        throw new Exception('Nenhum relacionamento foi encontrado no melhor caminho!');

    $nodes         = $melhorCaminho->nodes();
    $relationships = $melhorCaminho->relationships();

    $bairros       = [];
    $caminhos      = [];

    foreach ($nodes as $node)
        $bairros[(int) $node->identity()] = [
            'id'     => (int) $node->identity(),
            'bairro' => $node->get('nome'),
        ];


    foreach ($relationships as $relationship)
        $caminhos[(int) $relationship->identity()] = [
            'id'           => (int) $relationship->identity(),
            'distancia_km' => $relationship->get('km'),
            'de'           => $bairros[$relationship->startNodeIdentity()],
            'para'         => $bairros[$relationship->endNodeIdentity()],
            'tipo'         => $relationship->type(),
        ];

    $estrutura     = [
        'de'             => $de,
        'para'           => $para,
        'total_km'       => $totalKm,
        'caminho'        => array_values($bairros),
        'relacionamento' => array_values($caminhos),
    ];

    $response = [
        'meta' => [
            'code' => 200
        ],
        'data' => $estrutura
    ];

} catch (Exception $e) {
    $response = [
        'meta' => [
            'code'          => 400,
            'error_message' => $e->getMessage()
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($response);