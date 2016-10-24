<?php

if(!file_exists('./vendor/autoload.php'))
    die('Por favor, instale as dependencias do composer. $_: composer install');

require_once './vendor/autoload.php';

try {

    $de   = isset($_GET['de'])   ? $_GET['de']    : false ;
    $para = isset($_GET['para']) ? $_GET['para']  : false ;
    $km   = isset($_GET['km'])   ? $_GET['km']    : false ;

    if(!$de || !$para || !$km)
        throw new Exception("Os parametros de requisições são inválidos! É necessário o parametro 'de', 'para' e 'km'.");


    $de   = !empty($de)   ? $de   : false ;
    $para = !empty($para) ? $para : false ;
    $km   = !empty($km) ? $km : false ;

    if(!$de || !$para || !$km || !is_numeric($de) || !is_numeric($para) || !is_numeric($km))
        throw new Exception("Os parametros de requisições estão vázios ou não são números.");

    $cypher = "MATCH (de:Bairro), 
                     (para:Bairro)
                     
               WHERE ID(de) = {de} AND ID(para) = {para}
               
               CREATE (de)-[ida:DISTANCIA]->(para), (de)<-[volta:DISTANCIA]-(para)
               SET ida += {distancia}, volta += {distancia}
               RETURN ida, volta";

    require_once './conexao.php';

    $result = $client->run($cypher, [
        'de'        => (int) $de,
        'para'      => (int) $para,
        'distancia' => ['km' => (float) $km],
    ]);

    if($result->size() == 0)
        throw new Exception('Nenhum resultado foi retornado!');


    $response = [
        'meta' => [
            'code' => 200
        ],
        'data' => [
            'status' => 'ok'
        ]
    ];

} catch (Exception $e) {
    $response = [
        'meta' => [
            'code'          => 400,
            'error_message' => $e->getMessage()
        ]
    ];
}
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode($response);