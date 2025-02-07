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

// Verifica se o ID do usuário foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Busca os dados do usuário no banco
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $_SESSION['mensagem'] = "Usuário não encontrado.";
            header("Location: ../views/gerenciar_usuarios.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
        header("Location: ../views/gerenciar_usuarios.php");
        exit();
    }
} else {
    $_SESSION['mensagem'] = "ID inválido.";
    header("Location: ../views/gerenciar_usuarios.php");
    exit();
}

// Se o formulário for enviado para salvar as alterações
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_usuario'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $perfil = $_POST['perfil'];

    try {
        // Atualiza o usuário no banco
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário atualizado com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar o usuário.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco: " . $e->getMessage();
    }

    header("Location: ../views/gerenciar_usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white text-center">
                        <h4>Editar Usuário</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required
                                    value="<?= htmlspecialchars($usuario['nome']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?= htmlspecialchars($usuario['email']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="perfil" class="form-label">Perfil</label>
                                <select class="form-control" id="perfil" name="perfil" required>
                                    <option value="administrador"
                                        <?= $usuario['perfil'] == "administrador" ? "selected" : "" ?>>Administrador
                                    </option>
                                    <option value="cadastrador"
                                        <?= $usuario['perfil'] == "cadastrador" ? "selected" : "" ?>>Cadastrador
                                    </option>
                                    <option value="consultor"
                                        <?= $usuario['perfil'] == "consultor" ? "selected" : "" ?>>Consultor</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100" name="atualizar_usuario">Atualizar
                                Usuário</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>