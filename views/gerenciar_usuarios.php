<?php
session_start();
require_once "../config/conexao.php";
global $pdo; // Garante que a conexão PDO está acessível

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem'] = "Acesso negado!";
    header("Location: dashboard.php");
    exit();
}

// Busca usuários do banco
$sql = "SELECT * FROM usuarios";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="../public/img/logoPGJ.png" alt="Logo" width="180" height="80" class="me-2">
        </a>

        <!-- Botão para mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Itens do menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="listar_processos.php">Listar Processos</a></li>
                <li class="nav-item"><a class="nav-link" href="cadastro_processo.php">Cadastrar Processos</a></li>

                <?php if ($_SESSION['usuario_perfil'] === 'administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                    <li class="nav-item"><a class="nav-link" href="log_atividades.php">Log de Atividades</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link" href="../controllers/logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>


    <div class="container mt-5">
        <h2 class="text-center">Gerenciar Usuários</h2>

        <!-- Formulário para adicionar usuário -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">Adicionar Novo Usuário</div>
            <div class="card-body">
                <form action="../controllers/usuario_controller.php" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-control" id="perfil" name="perfil" required>
                            <option value="administrador">Administrador</option>
                            <option value="cadastrador">Cadastrador</option>
                            <option value="consultor">Consultor</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100" name="adicionar_usuario">Adicionar
                        Usuário</button>
                </form>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= ucfirst($usuario['perfil']) ?></td>
                        <td><?= $usuario['aprovado'] ? 'Ativado' : 'Desativado' ?></td>
                        <td>
                            <a href="../controllers/editar_usuario.php?id=<?= $usuario['id'] ?>"
                                class="btn btn-warning btn-sm">Editar</a>
                            <a href="../controllers/deletar_usuario.php?id=<?= $usuario['id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                            <a href="../controllers/ativar_usuario.php?id=<?= $usuario['id'] ?>"
                                class="btn btn-secondary btn-sm">
                                <?= $usuario['aprovado'] ? 'Desativar' : 'Ativar' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>