<?php
session_start();
require_once "../config/conexao.php";
global $pdo; // Garante que a conexão PDO está acessível

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem'] = "Acesso negado!";
    header("Location: ../views/dashboard.php");
    exit();
}

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Deleta o usuário do banco
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário deletado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao deletar o usuário.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/gerenciar_usuarios.php");
    exit();
} else {
    $_SESSION['mensagem'] = "ID inválido.";
    header("Location: ../views/gerenciar_usuarios.php");
    exit();
}
?>