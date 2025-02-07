<?php
require_once "../config/conexao.php";
global $pdo;

function registrar_log($usuario_id, $acao, $tabela_afetada, $registro_id, $valores_anteriores = null, $valores_novos = null) {
    global $pdo;
    $sql = "INSERT INTO logs (usuario_id, acao, tabela_afetada, registro_id, valores_anteriores, valores_novos, data_hora) 
            VALUES (:usuario_id, :acao, :tabela_afetada, :registro_id, :valores_anteriores, :valores_novos, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindParam(':acao', $acao, PDO::PARAM_STR);
    $stmt->bindParam(':tabela_afetada', $tabela_afetada, PDO::PARAM_STR);
    $stmt->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
    $stmt->bindParam(':valores_anteriores', $valores_anteriores, PDO::PARAM_STR);
    $stmt->bindParam(':valores_novos', $valores_novos, PDO::PARAM_STR);
    $stmt->execute();
}
?>
