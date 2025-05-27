<?php
require_once "../config/conexao.php";
global $pdo;

if (isset($_POST['numero'])) {
    $numero = trim($_POST['numero']);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM processos WHERE numero = :numero");
    $stmt->bindParam(":numero", $numero);
    $stmt->execute();

    $existe = $stmt->fetchColumn();

    echo json_encode(['existe' => $existe > 0]);
}