<?php
session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="text-center">Login</h3>

                        <?php if (isset($_SESSION['erro_login'])): ?>
                        <div class="alert alert-danger text-center">
                            <?= $_SESSION['erro_login']; ?>
                        </div>
                        <?php unset($_SESSION['erro_login']); ?>
                        <?php endif; ?>

                        <form action="../controllers/login_controller.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" id="senha" name="senha" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>

                        <!-- Botão para abrir o modal -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalAtualizacoes">
                                Últimas Atualizações
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Atualizações -->
    <div class="modal fade" id="modalAtualizacoes" tabindex="-1" aria-labelledby="modalAtualizacoesLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAtualizacoesLabel">Últimas Atualizações</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Versão 1.0:</strong> Primeira versão com as funções necessárias para o início do
                            sistema.
                        </li>
                        <!-- Adicione novas versões aqui -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>