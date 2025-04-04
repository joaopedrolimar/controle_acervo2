<?php
session_start();
require_once "../config/conexao.php";
require_once "logs_controller.php";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Buscar o registro para log
    $stmtBusca = $pdo->prepare("SELECT * FROM anpp WHERE id = :id");
    $stmtBusca->bindParam(':id', $id);
    $stmtBusca->execute();
    $registroAntigo = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if ($registroAntigo) {
        // Deletar
        $stmt = $pdo->prepare("DELETE FROM anpp WHERE id = :id");
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            // Log
            registrar_log(
                $_SESSION['usuario_id'],
                "Excluiu um ANPP",
                "anpp",
                $id,
                json_encode($registroAntigo),
                null
            );

            $_SESSION['mensagem'] = "ANPP excluído com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir o ANPP!";
        }
    } else {
        $_SESSION['mensagem'] = "Registro não encontrado!";
    }

    header("Location: ../views/listar_anpp.php");
    exit();
}