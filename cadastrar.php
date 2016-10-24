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

    $cypher = "CREATE (bairro:Bairro),
               (bairro)-[:DISTANCIA {km : 0}]->(bairro)
               SET bairro += {bairro}
               RETURN bairro";

    require_once './conexao.php';

    $result = $client->run($cypher, [
        'bairro' => [
            'nome' => $bairro
        ]
    ]);

    if($result->size() == 0)
        throw new Exception('Não foi possivel cadastrar esse bairro!');

    $dados = $result->firstRecord();

    $response = [
        'meta' => [
            'code' => 200
        ],
        'data' => [
            'id'   => $dados->get('bairro')->identity(),
            'nome' => $dados->get('bairro')->get('nome')
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