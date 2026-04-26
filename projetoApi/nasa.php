<?php
// URL da API da NASA buscando pela palavra "galaxy"
$url = 'https://images-api.nasa.gov/search?q=galaxy';

// Opções para evitar problemas de SSL no XAMPP/WAMP
$opcoes = [
    'http' => [
        'method' => 'GET',
        'timeout' => 15,
        'header' => "User-Agent: PHP\r\n"
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];

$contexto = stream_context_create($opcoes);
$conteudo = @file_get_contents($url, false, $contexto);

if ($conteudo === false) {
    die("Erro ao acessar a API da NASA. Verifique sua conexão com a internet.");
}

$imagens = json_decode($conteudo, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erro no JSON: " . json_last_error_msg());
}

$todosItens = $imagens['collection']['items'] ?? [];
$itensPorPagina = 10;

function buscarPorId($itens, $id) {
    $resultado = [];
    foreach ($itens as $item) {
        if (isset($item['data'][0]['nasa_id']) &&
            stripos($item['data'][0]['nasa_id'], $id) !== false) {
            $resultado[] = $item;
        }
    }
    return $resultado;
}

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $termoBusca = trim($_GET['search']);
    $todosItens = buscarPorId($todosItens, $termoBusca);
}

$totalItens = count($todosItens);
$totalPaginas = $totalItens > 0 ? ceil($totalItens / $itensPorPagina) : 1;

$paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$paginaAtual = max(1, min($paginaAtual, $totalPaginas));

$inicio = ($paginaAtual - 1) * $itensPorPagina;
$itensPagina = array_slice($todosItens, $inicio, $itensPorPagina);

include 'index_.html';
?>