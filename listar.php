<?php

if(!file_exists('./vendor/autoload.php'))
    die('Por favor, instale as dependencias do composer. $_: composer install');

require_once './vendor/autoload.php';

try {

    $cypher = "MATCH (bairros:Bairro)
               RETURN bairros";

    require_once './conexao.php';

    $result = $client->run($cypher);

    if($result->size() == 0)
        throw new Exception('Nenhum resultado foi encontrado para essa rota!');

    $dados   = $result->records();
    $bairros = [];

    foreach ($dados as $bairro){
        $bairros[] = [
            'id'   => $bairro->get('bairros')->identity(),
            'nome' => $bairro->get('bairros')->get('nome')
        ];
    }

    $response = [
        'meta' => [
            'code' => 200
        ],
        'data' => $bairros
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