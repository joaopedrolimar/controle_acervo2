<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Busca o arquivo antes de deletar
    $stmt = $pdo->prepare("SELECT caminho, tipo FROM atos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($documento) {
        if ($documento['tipo'] === 'arquivo' && file_exists($documento['caminho'])) {
            unlink($documento['caminho']); // Apaga o arquivo do servidor
        }

        // Apaga do banco de dados
        $stmt = $pdo->prepare("DELETE FROM atos WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}

// Redireciona de volta para a página de atos
header("Location: ../views/atos.php");
exit();