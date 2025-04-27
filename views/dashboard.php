<!--/controle_acervo/views/dashboard.php-->
<?php
session_start();
require_once "../config/conexao.php";
global $pdo;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtém o nome do usuário logado
$nome_usuario = $_SESSION['usuario_nome'];
// Obtém o perfil do usuário logado
$perfil = $_SESSION['usuario_perfil'] ?? '';

$pagina_atual = basename($_SERVER['PHP_SELF']);

// Contagem de processos por status
$sql_cadastrados = "SELECT COUNT(*) FROM processos WHERE status = 'Cadastrado'";
$sql_finalizados = "SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'";
$cadastrados = $pdo->query($sql_cadastrados)->fetchColumn();
$finalizados = $pdo->query($sql_finalizados)->fetchColumn();


?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="../public/img/favicon-32x32.png" type="../img/favicon-32x32.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    /* Ajuste da logo na navbar */
    .logo-navbar {
        max-width: 300px;
        /* Define um tamanho máximo */
        height: auto;
        /* Mantém a proporção correta */
    }

    /* Ajuste para telas menores */
    @media (max-width: 576px) {
        .logo-navbar {
            max-width: 250px;
            /* Reduz a logo para melhor encaixe */
            display: block;
            /* Evita que fique desalinhada */
            margin: auto;
            /* Centraliza no mobile */
        }
    }

    canvas {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 10px;
    }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #900020;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../public/img/logoWhite.png" alt="Logo" class="logo-navbar">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'dashboard.php') ? 'active' : '' ?>"
                            href="dashboard.php">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>



                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'listar_processos.php') ? 'active' : '' ?>"
                            href="listar_processos.php">
                            <i class="fas fa-list"></i> Listar Processos
                        </a>
                    </li>

                    <?php if ($perfil === 'cadastrador' || $perfil === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'cadastro_processo.php') ? 'active' : '' ?>"
                            href="cadastro_processo.php">
                            <i class="fas fa-plus"></i> Cadastrar Processos
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Novos itens de ANPP -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'listar_anpp.php') ? 'active' : '' ?>"
                            href="listar_anpp.php">
                            <i class="fas fa-scale-balanced"></i> Listagem de ANPPs
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'anpp.php') ? 'active' : '' ?>" href="anpp.php">
                            <i class="fas fa-file-circle-plus"></i> Cadastrar ANPP
                        </a>
                    </li>
                    <!-- Fim dos itens de ANPP -->

                    <?php if ($perfil === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'gerenciar_usuarios.php') ? 'active' : '' ?>"
                            href="gerenciar_usuarios.php">
                            <i class="fas fa-users-cog"></i> Gerenciar Usuários
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'atos.php') ? 'active' : '' ?>" href="atos.php">
                            <i class="fas fa-file-alt"></i> Atos
                        </a>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'log_atividades.php') ? 'active' : '' ?>"
                            href="log_atividades.php">
                            <i class="fas fa-history"></i> Log de Atividades
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'cadastro_basico.php') ? 'active' : '' ?>"
                            href="cadastro_basico.php">
                            <i class="fas fa-address-book"></i> Cadastro Básico
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../controllers/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>



    <div class="container mt-4">
        <h2 class="text-center"><i class="fas fa-chart-line"></i> Dashboard</h2>

        <div class="container d-flex flex-column align-items-center mt-5">
            <h2 class="text-center mb-4"><i class="fas fa-chart-pie"></i> Situação dos Processos</h2>
            <div style="max-width: 400px; width: 100%;">
                <canvas id="graficoStatus"></canvas>
            </div>
        </div>



    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctxStatus = document.getElementById('graficoStatus').getContext('2d');
    const graficoStatus = new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: ['Cadastrado', 'Finalizado'],
            datasets: [{
                data: [<?= $cadastrados ?>, <?= $finalizados ?>],
                backgroundColor: ['#ffc107', '#198754'], // amarelo e verde
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>


</body>

</html>