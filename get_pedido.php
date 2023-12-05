<?php

session_start();

// Verifique se o user_id está definido
if (!isset($_SESSION['user_id'])) {
    die("User ID não está definido na sessão.");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sqlPedidos = "SELECT id_pedido, valor_total, rua, pagamento
               FROM pedidos
               WHERE id_usuario = $user_id
               ORDER BY id_pedido DESC
               LIMIT 5";

$resultPedidos = $conn->query($sqlPedidos);

if ($resultPedidos === false) {
    die("Erro na consulta SQL: " . $conn->error);
}

$data = array();

if ($resultPedidos->num_rows > 0) {
    while ($pedido = $resultPedidos->fetch_assoc()) {
        $idPedido = $pedido['id_pedido'];

        $sqlProdutos = "SELECT produtos.preco,produtos.nome_produto, produtos.foto_produto, produtos_pedido.quantidade
                        FROM produtos_pedido
                        JOIN produtos ON produtos_pedido.id_produto = produtos.id_produto
                        WHERE produtos_pedido.id_pedido = $idPedido";

        $resultProdutos = $conn->query($sqlProdutos);

        $produtos = array();
        if ($resultProdutos->num_rows > 0) {
            while ($produto = $resultProdutos->fetch_assoc()) {
                $produtos[] = $produto;
            }
        }

        $data[] = array(
            'id_pedido' => $idPedido,
            'preco_total' => $pedido['valor_total'],
            'rua' => $pedido['rua'],
            'metodo_pagamento' => $pedido['pagamento'],
            'produtos' => $produtos
        );
    }
} else {
    echo "Nenhum pedido encontrado para o usuário.";
}

$conn->close();

echo json_encode($data);
?>