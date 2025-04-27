<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['alterar_senha'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $nova_senha = trim($_POST['nova_senha']);
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':id', $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Senha atualizada com sucesso!";
    } else {
        $_SESSION['mensagem'] = "Erro ao atualizar a senha.";
    }
}

header("Location: ../views/gerenciar_usuarios.php");
exit();