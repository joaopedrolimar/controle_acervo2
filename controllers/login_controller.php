<!--controle_acervo/controllers/login_controller.php-->
<?php
session_start();
require_once '../config/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Busca o usuário no banco de dados
    $sql = "SELECT id, nome, senha, perfil, aprovado FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verifica se a senha fornecida corresponde ao hash armazenado no banco
        if (password_verify($senha, $usuario['senha'])) {
            if ($usuario['aprovado'] == 1) {
                // Criação das variáveis de sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
                
                // Redireciona para o dashboard
                header("Location: ../views/dashboard.php");
                exit();
            } else {
                $_SESSION['erro_login'] = "Usuário ainda não aprovado!";
            }
        } else {
            $_SESSION['erro_login'] = "E-mail ou senha incorretos!";
        }
    } else {
        $_SESSION['erro_login'] = "Usuário não encontrado!";
    }

    // Redireciona de volta para a página de login com erro
    header("Location: ../views/login.php");
    exit();
} else {
    // Se não for uma requisição POST, redireciona para a página de login
    header("Location: ../views/login.php");
    exit();
}