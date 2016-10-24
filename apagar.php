<?php

if(!file_exists('./vendor/autoload.php'))
    die('Por favor, instale as dependencias do composer. $_: composer install');

require_once './vendor/autoload.php';

try {

    $bairro = isset($_GET['bairro']) ? $_GET['bairro'] : false ;

    if(!$bairro)
        throw new Exception("O parametro de requisição é inválido! É necessário o parametro 'bairro'.");


    $bairro = !empty($bairro) ? $bairro : false ;

    if(!$bairro)
        throw new Exception("Os parametros de requisições estão vázios.");

    $cypher = "MATCH (bairro:Bairro)
               WHERE ID(bairro) = {id} 
               DETACH DELETE bairro";

    require_once './conexao.php';

    $client->run($cypher, [
        'id' => (int) $bairro
    ]);

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