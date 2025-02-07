<?php
session_start();
require_once "../config/conexao.php";
global $pdo; // Garante que a conexão PDO está acessível

// Verifica se o formulário foi enviado para adicionar usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_usuario'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $perfil = $_POST['perfil'];

    try {
        // Insere no banco de dados
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil, aprovado) VALUES (:nome, :email, :senha, :perfil, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':perfil', $perfil);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário adicionado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar usuário.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/gerenciar_usuarios.php");
    exit();
}
?>