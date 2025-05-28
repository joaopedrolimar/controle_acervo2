<?php
require_once "../config/conexao.php";
global $pdo;

if (isset($_GET['numero'])) {
    $numero = trim($_GET['numero']);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM anpp WHERE numero_inquerito = :numero");
    $stmt->bindParam(':numero', $numero);
    $stmt->execute();
    $existe = $stmt->fetchColumn();

    echo json_encode(['existe' => $existe > 0]);
}