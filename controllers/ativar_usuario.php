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
        // Verifica o status atual do usuário
        $sql = "SELECT aprovado FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $novo_status = $usuario['aprovado'] ? 0 : 1;

            // Atualiza o status do usuário
            $sql = "UPDATE usuarios SET aprovado = :novo_status WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':novo_status', $novo_status, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['mensagem'] = $novo_status ? "Usuário ativado!" : "Usuário desativado!";
        } else {
            $_SESSION['mensagem'] = "Usuário não encontrado.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/gerenciar_usuarios.php");
    exit();
}
?>