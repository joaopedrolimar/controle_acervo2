<!--controle_acervo/views/cadastro_processo.php-->
<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Processo</title>
    <!-- Bootstrap CSS -->
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

                    <li class="nav-item"><a class="nav-link" href="../controllers/logout.php">Sair</a></li>a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Cadastrar Novo Processo</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['mensagem'])): ?>
                        <div class="alert alert-info">
                            <?= $_SESSION['mensagem']; ?>
                        </div>
                        <?php unset($_SESSION['mensagem']); ?>
                        <?php endif; ?>

                        <form action="../controllers/processo_controller.php" method="POST">
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número do Processo</label>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                            </div>
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <input type="text" class="form-control" id="tipo" name="tipo" required>
                            </div>
                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                            </div>
                            <div class="mb-3">
                                <label for="crime" class="form-label">Crime</label>
                                <input type="text" class="form-control" id="crime" name="crime" required>
                            </div>
                            <div class="mb-3">
                                <label for="denunciado" class="form-label">Denunciado</label>
                                <input type="text" class="form-control" id="denunciado" name="denunciado" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="Cadastrado">Cadastrado</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100" name="cadastrar">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>